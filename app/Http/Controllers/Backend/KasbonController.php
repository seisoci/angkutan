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
    public function create()
    {
        //
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
        'driver_id'  => 'required|integer',
        'amount'     => 'required|integer',
      ]);

      if($validator->passes()){
        Kasbon::create([
          'driver_id'   => $request->input('driver_id'),
          'amount'      => $request->input('amount'),
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

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Kasbon  $kasbon
     * @return \Illuminate\Http\Response
     */
    public function show(Kasbon $kasbon)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Kasbon  $kasbon
     * @return \Illuminate\Http\Response
     */
    public function edit(Kasbon $kasbon)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Kasbon  $kasbon
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Kasbon $kasbon)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Kasbon  $kasbon
     * @return \Illuminate\Http\Response
     */
    public function destroy(Kasbon $kasbon)
    {
        //
    }
}
