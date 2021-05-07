<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use App\Models\Costumer;
use App\Models\RoadMoney;
use App\Models\Transport;
use App\Models\TypeCapacity;
use Illuminate\Http\Request;
use DataTables;
use DB;
use Illuminate\Support\Facades\Validator;
use PDF;
use Route;

class RoadMoneyController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    //TODO: HAPUS FIELD INVOICE (TIDAK TERPAKAI)
    $config['page_title']       = "Uang Jalan";
    $config['page_description'] = "Daftar List Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '#','title' => "List Uang Jalan"],
    ];
    if ($request->ajax()) {
      $costumer_id = $request->costumer_id;
      $data = RoadMoney::with(['costumers', 'routefrom', 'routeto', 'cargo', 'typecapacities'])
      ->when($costumer_id, function ($query, $id) {
        return $query->where('costumer_id', $id);
      })
      ->select('road_money.*');
      return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function($row){
            $actionBtn = '
            <a href="roadmonies/'.$row->id.'/edit" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
            return $actionBtn;
        })->make(true);
    }
    return view('backend.masteroperational.roadmonies.index', compact('config', 'page_breadcrumbs'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $config['page_title'] = "Create Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '/backend/roadmonies','title' => "List Uang Jalan"],
      ['page' => '#','title' => "Create Uang Jalan"],
    ];
    return view('backend.masteroperational.roadmonies.create', compact('config', 'page_breadcrumbs'));
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
      'costumer_id'   => 'required|integer',
      'route_from'    => 'string|nullable',
      'route_to'      => 'string|nullable',
      'cargo_id'      => 'string|nullable',
      'fee_thanks'    => 'nullable',
      'tax_pph'       => 'nullable',
      'amount'        => 'integer|nullable',
    ]);

    if($validator->passes()){
      $check = RoadMoney::where([
        ['costumer_id', '=', $request->costumer_id],
        ['route_from', '=', $request->route_from],
        ['route_to', '=', $request->route_to],
        ['cargo_id', '=', $request->cargo_id]
      ])->count();
      if($check <= 0){
        $data = RoadMoney::create([
          'costumer_id'   => $request->input('costumer_id'),
          'route_from'    => $request->input('route_from'),
          'route_to'      => $request->input('route_to'),
          'cargo_id'      => $request->input('cargo_id'),
          'fee_thanks'    => $request->input('fee_thanks') ?? 0,
          'tax_pph'       => $request->input('tax_pph') ?? 0,
          'amount'        => $request->input('amount')
        ]);
        $type_capacities = TypeCapacity::all();
        $data->typecapacities()->attach($type_capacities);

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/roadmonies/'.$data->id.'/edit'
        ]);
      }else{
          $response = response()->json([
          'status' => 'error',
          'message' => 'Data Already Exist',
        ]);
      }


    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param  \App\Models\RoadMoney  $roadMoney
   * @return \Illuminate\Http\Response
   */
  public function edit($id)
  {
    $config['page_title'] = "Edit Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '/backend/roadmonies','title' => "List Uang Jalan"],
      ['page' => '#','title' => "Edit Uang Jalan"],
    ];

    $data = RoadMoney::with(['costumers', 'routefrom', 'routeto', 'cargo'])->findOrFail($id);
    return view('backend.masteroperational.roadmonies.edit',compact('config', 'page_breadcrumbs', 'data'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \App\Models\RoadMoney  $roadMoney
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'costumer_id'   => 'required|integer',
      'route_from'    => 'string|nullable',
      'route_to'      => 'string|nullable',
      'cargo_id'      => 'string|nullable',
      'fee_thanks'    => 'nullable',
      'tax_pph'       => 'nullable',
      'road_engkel'   => 'integer|nullable',
      'road_tronton'  => 'integer|nullable',
      'amount'        => 'integer|nullable',
    ]);

    if($validator->passes()){
      $data = RoadMoney::find($id);
      $data->update([
        'costumer_id'   => $request->input('costumer_id'),
        'route_from'    => $request->input('route_from'),
        'route_to'      => $request->input('route_to'),
        'cargo_id'      => $request->input('cargo_id'),
        'fee_thanks'    => $request->input('fee_thanks') ?? 0,
        'tax_pph'       => $request->input('tax_pph') ?? 0,
        'road_engkel'   => $request->input('road_engkel'),
        'road_tronton'  => $request->input('road_tronton'),
        'amount'        => $request->input('amount')
      ]);

      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been updated',
        'redirect' => '/backend/roadmonies'
      ]);

    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  \App\Models\RoadMoney  $roadMoney
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $response = response()->json([
        'status' => 'error',
        'message' => 'Data cannot be deleted',
    ]);
    $data = RoadMoney::find($id);

    if($data->delete()){
      $data->typecapacities()->detach();
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    }
    return $response;
  }

  public function typecapacities(Request $request){
    $validator = Validator::make($request->all(), [
      'type_capacity_id'  => 'integer',
      'road_money_id'     => 'integer',
    ]);

    if($validator->passes()){
      $data = RoadMoney::findOrFail($request->road_money_id)->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->where('type', $request->type)
      ->first();

      $response = response()->json([
        'data' => $data,
      ]);
    }
    return $response;
  }

  public function updatetypecapacities(Request $request, $id){
    $validator = Validator::make($request->all(), [
      'type_capacity_id'  => 'required|integer',
      'road_engkel'       => 'integer|nullable',
      'road_tronton'      => 'integer|nullable',
      'type'              => 'in:fix,calculate',
    ]);

    if($validator->passes()){
      $data = RoadMoney::findOrFail($id);
      if($data->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->wherePivot('type', $request->type)->count() >= 1){
        $data->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->wherePivot('type', $request->type)->updateExistingPivot($request->type_capacity_id, $request->except(['type_capacity_id', '_method']));
      }else{
        $data->typecapacities()->attach($request->type_capacity_id, $request->except(['type_capacity_id','_method']));
      }
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been updated',
      ]);
    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }

}
