<?php


namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Kasbon;
use DataTables;
use Illuminate\Http\Request;
use Validator;

class KasbonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Kasbon";
      $config['page_description'] = "Daftar List Kasbon";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Kasbon"],
      ];

      if ($request->ajax()) {
        $data = Kasbon::with('driver:id,name');
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
      return view('backend.invoice.kasbon.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
      $config['page_title']       ="Create Invoice Kasbon Supir";
      $config['page_description'] = "Create Invoice Kasbon Supir";
      $page_breadcrumbs = [
        ['page' => '#','title' => "Create Invoice Kasbon Supir"],
      ];
      $driver_id    = $request->driver_id;
      if ($request->ajax()) {
        $data = Kasbon::with(['driver:id,name'])
        ->when($driver_id, function ($query, $driver_id) {
          return $query->where('driver_id', $driver_id);
        });
        return DataTables::of($data)
          ->addIndexColumn()
          ->make(true);
      }
      return view('backend.invoice.invoicekasbons.create', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'driver_id'   => 'required|integer',
        'amount'      => 'required|integer',
        'memo'        => 'required|string',
      ]);

      if($validator->passes()){
        Kasbon::create([
          'driver_id'   => $request->input('driver_id'),
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
