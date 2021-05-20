<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\KasbonEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class KasbonEmployeeController extends Controller
{
  public function index(Request $request)
  {
    $config['page_title']       = "List Kasbon Karyawaan";
    $config['page_description'] = "Daftar List Kasbon Karyawaan";
    $page_breadcrumbs = [
      ['page' => '#','title' => "List Kasbon Karyawaan"],
    ];

    if ($request->ajax()) {
      $data = KasbonEmployee::with('employee:id,name');
      return DataTables::of($data)
        ->addIndexColumn()
        // ->addColumn('action', function($row){
        //   $actionBtn = '
        //     <div class="dropdown">
        //         <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        //             <i class="fas fa-eye"></i>
        //         </button>
        //         <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
        //           <a href="opnames/'.$row->id.'" class="dropdown-item">Detail Opname</a>
        //         </div>
        //     </div>
        //   ';
        //     return $actionBtn;
        // })
        ->make(true);
    }
    return view('backend.accounting.kasbonemployee.index', compact('config', 'page_breadcrumbs'));
  }

  public function create(Request $request)
  {
    $config['page_title']       ="Create Invoice Kasbon Supir";
    $config['page_description'] = "Create Invoice Kasbon Supir";
    $page_breadcrumbs = [
      ['page' => '#','title' => "Create Invoice Kasbon Supir"],
    ];
    $employee_id    = $request->employee_id;
    if ($request->ajax()) {
      $data = KasbonEmployee::with(['employee:id,name'])
        ->when($employee_id, function ($query, $employee_id) {
          return $query->where('employee_id', $employee_id);
        });
      return DataTables::of($data)
        ->addIndexColumn()
        ->make(true);
    }
    return view('backend.invoice.invoicekasbons.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'employee_id'   => 'required|integer',
      'amount'      => 'required|integer',
      'memo'        => 'required|string',
    ]);

    if($validator->passes()){
      KasbonEmployee::create([
        'employee_id'   => $request->input('employee_id'),
        'amount'      => $request->input('amount'),
        'memo'      => $request->input('memo'),
      ]);
      $response = response()->json([
        'status'  => 'success',
        'message' => 'Data has been saved',
      ]);
    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }
}
