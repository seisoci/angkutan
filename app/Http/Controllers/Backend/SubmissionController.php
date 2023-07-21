<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\OperationalExpense;
use App\Services\JobOrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class SubmissionController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:submission-list|submission-create|submission-edit|submission-delete', ['only' => ['index']]);
    $this->middleware('permission:submission-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:submission-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:submission-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Pengajuan Uang Jalan";
    $config['page_description'] = "Daftar List Pengajuan Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Pengajuan Uang Jalan"],
    ];
    $selectCoa = ConfigCoa::with('coa')->where('name_page', 'joborders')->sole();

    $saldoGroup = collect($selectCoa->coa)->map(function ($coa) {
      return [
        'name' => $coa->name ?? NULL,
        'balance' => DB::table('journals')
            ->select(DB::raw('
          IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
          '))
            ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
            ->where('journals.coa_id', $coa->id)
            ->groupBy('journals.coa_id')
            ->first()->saldo ?? 0,
      ];
    });

    $restRoadMoney = JobOrder::with('driver:id,name', 'transport:id,num_pol')
      ->whereRaw('id IN (SELECT max(id) FROM `job_orders` WHERE `status_cargo` = "selesai" GROUP BY `transport_id`, `driver_id`)')
      ->orderBy('created_at', 'desc')
      ->get();

    if ($request->ajax()) {
      $type = $request['type'];
      if ($type == 'null') {
        $typeData = NULL;
      } else {
        $typeData = $type;
      }

      $driver_id = $request->driver_id;
      $transport_id = $request->transport_id;
      $another_expedition_id = $request->another_expedition_id;
      $data = OperationalExpense::selectRaw('
          `operational_expenses`.*,
          `expenses`.`name` AS `expense_name`
        ')
        ->with([
          'joborder',
          'joborder.costumer:id,name',
          'joborder.routefrom:id,name',
          'joborder.routeto:id,name',
          'joborder.transport:id,num_pol',
          'joborder.driver:id,name',
        ])
        ->leftJoin('expenses', 'expenses.id', '=', 'operational_expenses.expense_id')
        ->when($request['status'], function ($query) use ($request) {
          if ($request['status'] == 'all') {
          } else if ($request['status'] == 'pending') {
            return $query->whereNull('approved');
          } else if ($request['status'] == "ditolak") {
            return $query->where('approved', "0");
          } else if ($request['status'] == "disetujui") {
            return $query->where('approved', "1");
          }
        })
        ->whereHas('joborder', function ($query) use ($transport_id, $driver_id, $another_expedition_id, $request) {
          $query->when($driver_id, function ($query, $driver_id) {
            return $query->where('driver_id', $driver_id);
          });
          $query->when($transport_id, function ($query, $driver_id) {
            return $query->where('transport_id', $driver_id);
          });
          $query->when($another_expedition_id, function ($query, $another_expedition_id) {
            return $query->where('another_expedition_id', $another_expedition_id);
          });
          $query->when($request['status'], function ($query) use ($request) {
            if ($request['statusLDO'] == 'all') {
            } else if ($request['statusLDO'] == 'self') {
              return $query->whereNull('another_expedition_id');
            } else if ($request['statusLDO'] == "ldo") {
              return $query->WhereNotNull('another_expedition_id');
            }
          });
        })
        ->when($typeData, function ($query, $typeData) {
          return $query->where('type', $typeData);
        });

      if($request->filled('expense_id')){
        $data->where('expense_id', $request['expense_id']);
      }



      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('details_url', function (OperationalExpense $operationalExpense) {
          return route('backend.joborders.datatabledetail', $operationalExpense->job_order_id);
        })
        ->addColumn('action', function ($row) {
          $btnEdit = '';
          if ($row->approved == NULL) {
            $btnEdit = '<a href="#" data-toggle="modal"
            data-target="#modalEdit"
            data-id="' . $row->id . '"
            data-amount="' . $row->amount . '"
            data-tgl="' . Carbon::createFromFormat('Y-m-d H:i:s', $row->created_at)->isoFormat('DD MMM YYYY') . '"
            data-id="' . $row->id . '"
            data-description="' . $row->description . '"
            class="delete btn btn-primary">Aksi</a>';
          }
          return "$btnEdit";
        })
        ->make(true);
    }
    return view('backend.operational.submission.index', compact('config', 'page_breadcrumbs', 'selectCoa', 'saldoGroup', 'restRoadMoney'));
  }

  public function update($id, Request $request, JobOrderService $jobOrderService)
  {
    $validator = Validator::make($request->all(), [
      'approved' => 'required|integer',
      'approved_by' => 'integer',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $operationalExpense = OperationalExpense::findOrFail($id);
        $operationalExpense->update([
          'amount' => $request['amount'],
          'approved' => $request['approved'],
          'approved_by' => $request['approved_by'],
          'description' => $request['description'],
          'approved_date' => Carbon::now()->toDateString()
        ]);
        $coa = Coa::findOrFail($request['coa_id']);

        $data = JobOrder::with([
          'anotherexpedition:id,name',
          'driver:id,name',
          'costumer:id,cooperation_id,name',
          'costumer.cooperation:id,nickname',
          'cargo:id,name',
          'transport:id,num_pol',
          'routefrom:id,name',
          'routeto:id,name',
          'operationalexpense.expense'
        ])->find($operationalExpense->job_order_id);

        if ($request['approved'] == '1') {
          $checksaldo = DB::table('journals')
            ->select(DB::raw('
            IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
            (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
            '))
            ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
            ->where('journals.coa_id', $request['coa_id'])
            ->groupBy('journals.coa_id')
            ->first();

          if (($checksaldo->saldo ?? FALSE) && $operationalExpense->amount <= $checksaldo->saldo) {
            Journal::create([
              'coa_id' => $request['coa_id'],
              'date_journal' => Carbon::now()->toDateString(),
              'debit' => 0,
              'kredit' => $operationalExpense->amount,
              'table_ref' => 'operationalexpense',
              'code_ref' => $id,
              'description' => "Pengurangan saldo untuk uang jalan " . $data['num_bill'] . " " . $data->costumer->name . " dari " . $data->routefrom->name . " ke " . $data->routeto->name . " dengan No. Pol: " . $data->transport->num_pol
            ]);

            Journal::create([
              'coa_id' => 50,
              'date_journal' => Carbon::now()->toDateString(),
              'debit' => $operationalExpense->amount,
              'kredit' => 0,
              'table_ref' => 'operationalexpense',
              'code_ref' => $id,
              'description' => "Beban operasional uang jalan " . $data['num_bill'] . " " . $data->costumer->name . " dari " . $data->routefrom->name . " ke " . $data->routeto->name . " dengan No. Pol: " . $data->transport->num_pol
            ]);

            $roadMoneySystem = $data->road_money;
            $roadMoneyPrev = JobOrder::where([
              ['driver_id', $data['driver_id']],
              ['transport_id', $data['transport_id']],
              ['status_cargo', 'selesai'],
              ['id', '<', $data->id],
            ])->orderBy('created_at', 'desc')->first()->road_money_extra ?? 0;

            $roadMoney = OperationalExpense::where([
              ['type', 'roadmoney'],
              ['approved', '1'],
              ['job_order_id', $operationalExpense->job_order_id]
            ])->sum('amount');

            if ($data->type == 'self') {
              $roadMOneyExtra = ($roadMoneySystem + $roadMoneyPrev) - $roadMoney;
              $data->update([
                'road_money_prev' => $roadMoneyPrev,
                'road_money_extra' => $roadMOneyExtra
              ]);

              //UPDATE SELUEUH JO SETELAHNYA
              $updateJONext = JobOrder::where('id', '>', $data->id)->where('driver_id', $data['driver_id'])
                ->where('transport_id', $data['transport_id'])
                ->orderBy('created_at', 'asc')
                ->get();

              foreach ($updateJONext as $item):
                $roadMoneySystemNext = JobOrder::with('roadmoneydetail')->find($item->id);

                $roadMoneyPrevNext = JobOrder::where([
                  ['driver_id', $data['driver_id']],
                  ['transport_id', $data['transport_id']],
                  ['status_cargo', 'selesai'],
                  ['id', '<', $item->id]
                ])->orderBy('created_at', 'desc')->first()->road_money_extra ?? 0;

                $roadMoneyNext = OperationalExpense::where([
                  ['type', 'roadmoney'],
                  ['approved', '1'],
                  ['job_order_id', $item->id]
                ])->sum('amount');

                $roadMOneyExtraNext = (($roadMoneySystemNext->road_money ?? 0) + $roadMoneyPrevNext) - $roadMoneyNext;

                $roadMoneySystemNext->update([
                  'road_money_prev' => $roadMoneyPrevNext,
                  'road_money_extra' => $roadMOneyExtraNext
                ]);

                foreach ($roadMoneySystemNext->roadmoneydetail as $itemChild):
                  Journal::where('table_ref', 'operationalexpense')
                    ->where('code_ref', $itemChild->id)
                    ->delete();

                  Journal::create([
                    'coa_id' => $request['coa_id'],
                    'date_journal' => $itemChild['approved_date'] ?? Carbon::now()->toDateString(),
                    'debit' => 0,
                    'kredit' => $itemChild->amount,
                    'table_ref' => 'operationalexpense',
                    'code_ref' => $itemChild->id,
                    'description' => "Pengurangan saldo untuk uang jalan " . $item->num_bill . " " . $item->costumer->name . " dari " . $item->routefrom->name . " ke " . $item->routeto->name . " dengan No. Pol: " . $item->transport->num_pol
                  ]);

                  Journal::create([
                    'coa_id' => 50,
                    'date_journal' => $itemChild['approved_date'] ?? Carbon::now()->toDateString(),
                    'debit' => $itemChild->amount,
                    'kredit' => 0,
                    'table_ref' => 'operationalexpense',
                    'code_ref' => $itemChild->id,
                    'description' => "Beban operasional uang jalan " . $item->num_bill . " " . $item->costumer->name . " dari " . $item->routefrom->name . " ke " . $item->routeto->name . " dengan No. Pol: " . $item->transport->num_pol
                  ]);
                endforeach;

              endforeach;
            } else if ($data->type == 'ldo') {
              $data->update([
                'road_money' => $roadMoney,
              ]);
            }

            $jobOrderCalculate = $jobOrderService->calculate($data);
            $data->update($jobOrderCalculate);
          } else {
            DB::rollBack();
            return response()->json([
              'status' => 'errors',
              'message' => "Saldo $coa->name tidak ada/kurang",
            ]);
          }
        }
        DB::commit();
        return response()->json([
          'status' => 'success',
          'message' => 'Data has been updated',
        ]);
      } catch
      (\Throwable $throw) {
        Log::error($throw);
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function findbypk($id)
  {
    $operationalExpense = OperationalExpense::find($id);
    $jobOrder = JobOrder::withSum('roadmoneydetail', 'amount')->find($operationalExpense->job_order_id);
    $history = OperationalExpense::whereJobOrderId($operationalExpense['job_order_id'])
      ->where('id', '!=', $id)
      ->get()
      ->map(function ($data) {
        $arr['created_at'] = Carbon::createFromFormat('Y-m-d H:i:s', $data['created_at'])->isoFormat('DD MMM YYYY');
        $arr['amount'] = $data['amount'];
        return $arr;
      });

    if ($jobOrder->type == "self") {
      $roadMoney = ($jobOrder->road_money + $jobOrder->road_money_prev) - $jobOrder->roadmoneydetail_sum_amount;
    } else {
      $roadMoney = $jobOrder->roadmoneydetail_sum_amount;
    }
    return response()->json([
      'roadMoneyFormat' => number_format($roadMoney, 0, '.', ','),
      'roadMoneyFormatReal' => number_format($jobOrder['road_money'], 0, '.', ','),
      'roadMoney' => $roadMoney,
      'type' => $jobOrder->type,
      'history' => $history,
      'jobOrder' => $jobOrder
    ]);

  }

  public function datatable_history(Request $request)
  {
    if ($request->ajax()) {
      $data = JobOrder::selectRaw('
        `operational_expenses`.`created_at` AS `tgl_dibuat`,
        `operational_expenses`.`amount`,
        `operational_expenses`.`description`,
        `operational_expenses`.`type`,
        `costumers`.`name` AS `customer_name`,
        `rf`.`name` AS `route_from`,
        `rt`.`name` AS `route_to`
      ')
        ->leftJoin('operational_expenses', 'operational_expenses.job_order_id', '=', 'job_orders.id')
        ->leftJoin('costumers', 'costumers.id', '=', 'job_orders.costumer_id')
        ->leftJoin('routes AS rf', 'rf.id', '=', 'job_orders.route_from')
        ->leftJoin('routes AS rt', 'rt.id', '=', 'job_orders.route_to')
        ->where([
          ['driver_id', $request['driver_id']],
          ['transport_id', $request['transport_id']],
          ['costumer_id', $request['costumer_id']],
          ['route_from', $request['route_from']],
          ['route_to', $request['route_to']],
        ]);

      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
  }
}
