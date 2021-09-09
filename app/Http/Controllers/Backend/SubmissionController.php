<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

      $data = OperationalExpense::with(['joborder'])
        ->when($request['status'], function ($query, $typeData) use ($request) {
          if ($request['status'] == 'all') {
          } else if ($request['status'] == 'pending') {
            return $query->whereNull('approved');
          } else {
            return $query->where('approved', $request['status']);
          }
        })
        ->when($typeData, function ($query, $typeData) {
          return $query->where('type', $typeData);
        });

      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $btnEdit = '';
          if ($row->approved == NULL) {
            $btnEdit = '<a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" data-description="' . $row->description . '" class="delete btn btn-primary">Aksi</a>';
          }
          $actionBtn = "$btnEdit";
          return $actionBtn;
        })
        ->make(true);
    }
    return view('backend.operational.submission.index', compact('config', 'page_breadcrumbs', 'selectCoa', 'saldoGroup', 'restRoadMoney'));
  }

  public function update(Request $request, $id)
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
          'approved' => $request['approved'],
          'approved_by' => $request['approved_by'],
          'description' => $request['description'],
        ]);
        $coa = Coa::findOrFail($request['coa_id']);

        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,cooperation_id,name', 'costumer.cooperation:id,nickname', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name', 'operationalexpense.expense'])->find($operationalExpense->job_order_id);

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
              'date_journal' => $data->date_begin,
              'debit' => 0,
              'kredit' => $operationalExpense->amount,
              'table_ref' => 'operationalexpense',
              'code_ref' => $id,
              'description' => "Pengurangan saldo untuk uang jalan " . $data->costumer->name . " dari " . $data->routefrom->name . " ke " . $data->routeto->name . " dengan No. Pol: " . $data->transport->num_pol
            ]);

            Journal::create([
              'coa_id' => 50,
              'date_journal' => $data->date_begin,
              'debit' => $operationalExpense->amount,
              'kredit' => 0,
              'table_ref' => 'operationalexpense',
              'code_ref' => $id,
              'description' => "Beban operasional uang jalan " . $data->costumer->name . " dari " . $data->routefrom->name . " ke " . $data->routeto->name . " dengan No. Pol: " . $data->transport->num_pol
            ]);

            $roadMoneySystem = $data->road_money;
            $roadMoneyPrev = JobOrder::where([
                ['driver_id', $data['driver_id']],
                ['transport_id', $data['transport_id']],
                ['status_cargo', 'selesai'],
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
            } else if ($data->type == 'ldo') {
              $data->update([
                'road_money' => $roadMoney,
              ]);
            }
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
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }
}
