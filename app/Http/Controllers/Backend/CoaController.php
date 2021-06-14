<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CoaController extends Controller
{

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
        $parent = Coa::where('parent_id', $request->parent_id)->max('code');
        $parent_id = $request->parent_id;
        $normal_balance = $request->normal_balance;
        if ($parent) {
          $val = explode('.', $parent);
          $lastNum = end($val);
          array_pop($val);
          $code = implode('.', $val) . "." . ++$lastNum;
        } else {
          $parent = Coa::findOrFail($request->parent_id);
          $code = $parent->code . ".1";
        }
      } elseif ($request->parent_id == 'none') {
        $parent = Coa::whereNull('parent_id')->where('type', $request->type)->max('code');
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
          $val = explode('.', $parent);
          $lastNum = end($val);
          array_pop($val);
          $code = implode('.', $val) . "." . ++$lastNum;
        }
      }
      Coa::create([
        'name' => $request->name,
        'code' => $code,
        'parent_id' => $parent_id,
        'type' => $request->type,
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
