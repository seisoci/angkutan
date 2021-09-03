<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\InvoiceCostumer;
use App\Models\InvoiceKasbon;
use App\Models\InvoiceKasbonEmployee;
use App\Models\InvoiceLdo;
use App\Models\InvoicePurchase;
use App\Models\InvoiceReturPurchase;
use App\Models\InvoiceSalary;
use App\Models\InvoiceUsageItem;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\Kasbon;
use App\Models\KasbonEmployee;
use App\Models\MonthlySalaryDetail;
use App\Models\OperationalExpense;
use App\Models\UsageItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CoaController extends Controller
{

  function __construct()
  {
    $this->middleware('permission:mastercoa-list|mastercoa-create|mastercoa-edit|mastercoa-delete', ['only' => ['index']]);
    $this->middleware('permission:mastercoa-create', ['only' => ['create', 'store']]);
  }

  public function index()
  {
    $config['page_title'] = "Master Akun COA";
    $config['page_description'] = "Master Akun COA";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Master Akun COA"],
    ];

    $data = Coa::with('children')->whereNull('parent_id')->orderBy('code', 'asc')->get();
    $collection = collect($data)->groupBy('type');
    return view('backend.masterfinance.coa.index', compact('config', 'page_breadcrumbs', 'collection'));
  }

  public function create()
  {
    $config['page_title'] = "Create Akun COA";
    $page_breadcrumbs = [
      ['page' => '/backend/mastercoa', 'title' => "List Master Akun COA"],
      ['page' => '#', 'title' => "Create Akun COA"],
    ];
    return view('backend.masterfinance.coa.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|unique:coas|string',
      'parent_id' => 'required',
      'normal_balance' => 'nullable',
      'type' => 'required|in:harta,kewajiban,modal,pendapatan,beban'
    ]);

    if ($validator->passes()) {
      if (is_numeric($request->parent_id)) {
        $parentCode = Coa::where('parent_id', $request->parent_id)->max('code');
        $parent = Coa::selectRaw('SUBSTRING_INDEX(TRIM(`code`), ".", -1) AS `max`')->where('parent_id', $request->parent_id)->get();
        $type = Coa::findOrFail($request->parent_id)->type ?? NULL;
        $parent_id = $request->parent_id;
        $normal_balance = $request->normal_balance;
        if ($parent) {
          $val = explode('.', $parentCode);
          $array = $parent->pluck('max') ?? array();
          $max = max($array->toArray());
          $lastNum = $max;

          array_pop($val);
          $code = implode('.', $val) . "." . ++$lastNum;
        } else {
          $parent = Coa::findOrFail($request->parent_id);
          $code = $parent->code . ".1";
        }
      } elseif ($request->parent_id == 'none') {
        $type = $request->type;
        $parentId = Coa::whereNull('parent_id')->where('type', $request->type)->max('code');
        $parent = Coa::selectRaw('SUBSTRING_INDEX(TRIM(`code`), ".", -1) AS `max`')->whereNull('parent_id')->where('type', $request->type)->get();
        $parent_id = NULL;
        $normal_balance = NULL;
        if ($parent == NULL) {
          switch ($request->type) {
            case 'harta':
              $code = '1.0';
              break;
            case 'kewajiban':
              $code = '2.0';
              break;
            case 'modal':
              $code = '3.0';
              break;
            case 'pendapatan':
              $code = '4.0';
              break;
            case 'beban':
              $code = '5.0';
              break;
            default:
          }
        } else {
          $val = explode('.', $parentId);
          $array = $parent->pluck('max') ?? array();
          $max = max($array->toArray());
          $lastNum = $max;
          array_pop($val);
          $code = implode('.', $val) . "." . ++$lastNum;
        }
      }
      Coa::create([
        'name' => $request->name,
        'code' => $code,
        'parent_id' => $parent_id,
        'type' => $type,
        'normal_balance' => $normal_balance,
      ]);
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => '/backend/mastercoa'
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
    ]);

    if ($validator->passes()) {
      $data = Coa::find($id);
      $data->update([
        'name' => $request->input('name'),
      ]);
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => 'reload'
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function destroy($id)
  {
    $response = response()->json([
      'status' => 'error',
      'message' => 'Data cannot be deleted',
    ]);
    $coa = Coa::with('children')->findOrFail($id);

    try {
      DB::beginTransaction();
      $journal = Journal::where('coa_id', $coa->id)->get();
      foreach ($journal as $item):
        Journal::where('table_ref', $item->table_ref)->where('code_ref', $item->code_ref)->delete();
        if ($item->table_ref === 'joborders') {
          $data = JobOrder::find($item->code_ref);
          if ($data != NULL): $data->delete(); endif;
        } elseif ($item->table_ref === 'operationalexpense') {
          $data = OperationalExpense::find($item->code_ref);
          if ($data != NULL): $data->delete(); endif;
        } elseif ($item->table_ref === 'invoicepurchases') {
          $dataPurchase = InvoicePurchase::find($item->code_ref);
          if ($dataPurchase != NULL): $dataPurchase->delete(); endif;
          $usageItem = UsageItem::where('invoice_purchase_id', $item->code_ref)->get();
          foreach ($usageItem as $usage) {
            $data = InvoiceUsageItem::find($usageItem->invoice_usage_item_id)->delete();
            if ($data != NULL): $data->delete(); endif;
          }
        } elseif ($item->table_ref === 'invoiceusageitems') {
          $data = InvoiceUsageItem::find($item->code_ref);
          if ($data != NULL): $data->delete(); endif;
        } elseif ($item->table_ref === 'invoiceusageitemsoutside') {
          $data = InvoiceUsageItem::find($item->code_ref);
          if ($data != NULL): $data->delete(); endif;
        } elseif ($item->table_ref === 'kasbon') {
          $data = Kasbon::find($item->code_ref);
          if ($data != NULL): $data->delete(); endif;
        } elseif ($item->table_ref === 'invoicekasbons') {
          $data = InvoiceKasbon::find($item->code_ref);
          if ($data != NULL): $data->delete(); endif;
        } elseif ($item->table_ref === 'kasbonemployees') {
          $data = KasbonEmployee::find($item->code_ref);
          if ($data != NULL): $data->delete(); endif;
        } elseif ($item->table_ref === 'invoicekasbonemployees') {
          $data = InvoiceKasbonEmployee::find($item->code_ref);
          if ($data != NULL): $data->delete(); endif;
        } elseif ($item->table_ref === 'invoicesalaries') {
          $data = InvoiceSalary::find($item->code_ref);
          if ($data != NULL): $data->delete(); endif;
        } elseif ($item->table_ref === 'monthlysalarydetail') {
          $data = MonthlySalaryDetail::find($item->code_ref);
          if ($data != NULL): $data->update(['status' => '0']); endif;
        } elseif ($item->table_ref === 'invoicecostumers') {
          $data = InvoiceCostumer::find($item->code_ref);
          if ($dataPurchase != NULL): $dataPurchase->delete(); endif;
        } elseif ($item->table_ref === 'invoiceldo') {
          $data = InvoiceLdo::find($item->code_ref);
          if ($dataPurchase != NULL): $dataPurchase->delete(); endif;
        } elseif ($item->table_ref === 'invoicereturpurchases') {
          $data = InvoiceReturPurchase::find($item->code_ref);
          if ($dataPurchase != NULL): $dataPurchase->delete(); endif;
        }
      endforeach;
      $coa->delete();
      //Children COA
      foreach ($coa->children as $itemCoa):
        $coaChildren = Coa::findOrFail($itemCoa->id);
        $journalChildren = Journal::where('coa_id', $coaChildren->id)->get();
        foreach ($journalChildren as $item) {
          Journal::where('table_ref', $item->table_ref)->where('code_ref', $item->code_ref)->delete();
          if ($item->table_ref === 'joborders') {
            $data = JobOrder::find($item->code_ref);
            if ($data != NULL): $data->delete(); endif;
          } elseif ($item->table_ref === 'operationalexpense') {
            $data = OperationalExpense::find($item->code_ref);
            if ($data != NULL): $data->delete(); endif;
          } elseif ($item->table_ref === 'invoicepurchases') {
            $dataPurchase = InvoicePurchase::find($item->code_ref);
            if ($dataPurchase != NULL): $dataPurchase->delete(); endif;
            $usageItem = UsageItem::where('invoice_purchase_id', $item->code_ref)->get();
            foreach ($usageItem as $usage) {
              $data = InvoiceUsageItem::find($usageItem->invoice_usage_item_id)->delete();
              if ($data != NULL): $data->delete(); endif;
            }
          } elseif ($item->table_ref === 'invoiceusageitems') {
            $data = InvoiceUsageItem::find($item->code_ref);
            if ($data != NULL): $data->delete(); endif;
          } elseif ($item->table_ref === 'invoiceusageitemsoutside') {
            $data = InvoiceUsageItem::find($item->code_ref);
            if ($data != NULL): $data->delete(); endif;
          } elseif ($item->table_ref === 'kasbon') {
            $data = Kasbon::find($item->code_ref);
            if ($data != NULL): $data->delete(); endif;
          } elseif ($item->table_ref === 'invoicekasbons') {
            $data = InvoiceKasbon::find($item->code_ref);
            if ($data != NULL): $data->delete(); endif;
          } elseif ($item->table_ref === 'kasbonemployees') {
            $data = KasbonEmployee::find($item->code_ref);
            if ($data != NULL): $data->delete(); endif;
          } elseif ($item->table_ref === 'invoicekasbonemployees') {
            $data = InvoiceKasbonEmployee::find($item->code_ref);
            if ($data != NULL): $data->delete(); endif;
          } elseif ($item->table_ref === 'invoicesalaries') {
            $data = InvoiceSalary::find($item->code_ref);
            if ($data != NULL): $data->delete(); endif;
          } elseif ($item->table_ref === 'monthlysalarydetail') {
            $data = MonthlySalaryDetail::find($item->code_ref);
            if ($data != NULL): $data->update(['status' => '0']); endif;
          } elseif ($item->table_ref === 'invoicecostumers') {
            $data = InvoiceCostumer::find($item->code_ref);
            if ($dataPurchase != NULL): $dataPurchase->delete(); endif;
          } elseif ($item->table_ref === 'invoiceldo') {
            $data = InvoiceLdo::find($item->code_ref);
            if ($dataPurchase != NULL): $dataPurchase->delete(); endif;
          } elseif ($item->table_ref === 'invoicereturpurchases') {
            $data = InvoiceReturPurchase::find($item->code_ref);
            if ($dataPurchase != NULL): $dataPurchase->delete(); endif;
          }
        }
        $coaChildren->delete();
      endforeach;
      DB::commit();
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
        'redirect' => 'reload'
      ]);
    } catch (\Throwable $throw) {
      DB::rollBack();
      $response = $throw;
//      $response = response()->json([
//        'status' => 'error',
//        'message' => 'Data cannot be deleted',
//      ]);
    }
    return $response;
  }

  public function select2(Request $request)
  {
    $page = $request->page;
    $resultCount = 25;
    $offset = ($page - 1) * $resultCount;
    $status = $request->status ?? NULL;
    $data = Coa::where('name', 'LIKE', '%' . $request->q . '%')
      ->whereNull('parent_id')
      ->when($status, function ($q, $status) {
        return $q->where('status', $status);
      })
      ->orderBy('code')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, CONCAT(`code`, " - ", `name`) as text')
      ->get();

    $count = Coa::where('name', 'LIKE', '%' . $request->q . '%')
      ->whereNull('parent_id')
      ->get()
      ->count();
    $prepend = $data->prepend(array("id" => "none", "text" => "Master Utama"));
    $endCount = $offset + $resultCount;
    $morePages = $count > $endCount;

    $results = array(
      "results" => $prepend,
      "pagination" => array(
        "more" => $morePages
      )
    );

    return response()->json($results);
  }

  public function select2self(Request $request)
  {
    $page = $request->page;
    $resultCount = 25;
    $offset = ($page - 1) * $resultCount;
    $status = $request->status ?? NULL;
    $data = Coa::where('name', 'LIKE', '%' . $request->q . '%')
      ->whereNotNull('parent_id')
      ->when($status, function ($q, $status) {
        return $q->where('status', $status);
      })
      ->orderBy('code')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, CONCAT(`code`, " - ", `name`) as text')
      ->get();

    $count = Coa::where('name', 'LIKE', '%' . $request->q . '%')
      ->whereNotNull('parent_id')
      ->get()
      ->count();
    $endCount = $offset + $resultCount;
    $morePages = $count > $endCount;

    $results = array(
      "results" => $data,
      "pagination" => array(
        "more" => $morePages
      )
    );

    return response()->json($results);
  }


}
