<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\RoadMoney;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class RoadMoneyController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:roadmonies-list|roadmonies-create|roadmonies-edit|roadmonies-delete', ['only' => ['index']]);
    $this->middleware('permission:roadmonies-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:roadmonies-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:roadmonies-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Uang Jalan";
    $config['page_description'] = "Daftar List Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Uang Jalan"],
    ];
    if ($request->ajax()) {
      $costumer_id = $request['costumer_id'];
      $data = RoadMoney::with(['costumers', 'routefrom', 'routeto', 'cargo', 'typecapacities'])
        ->when($costumer_id, function ($query, $id) {
          return $query->where('costumer_id', $id);
        })
        ->select('road_money.*');
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          return '
            <a href="'.route('backend.roadmonies.edit', $row['id']).'" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row['id'] . '" class="delete btn btn-danger btn-sm">Delete</a>';
        })->make(true);
    }
    return view('backend.masteroperational.roadmonies.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Create Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '/backend/roadmonies', 'title' => "List Uang Jalan"],
      ['page' => '#', 'title' => "Create Uang Jalan"],
    ];
    return view('backend.masteroperational.roadmonies.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'costumer_id' => 'required|integer',
      'route_from' => 'string|nullable',
      'route_to' => 'string|nullable',
      'cargo_id' => 'string|nullable',
      'fee_thanks' => 'nullable',
      'tax_pph' => 'nullable',
      'amount' => 'integer|nullable',
    ]);

    if ($validator->passes()) {
      $check = RoadMoney::where([
        ['costumer_id', '=', $request->input('costumer_id')],
        ['route_from', '=', $request->input('route_from')],
        ['route_to', '=', $request->input('route_to')],
        ['cargo_id', '=', $request->input('cargo_id')]
      ])->count();
      if ($check <= 0) {
        $data = RoadMoney::create($request->all());

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => route('backend.roadmonies.edit', $data['id'])
        ]);
      } else {
        $response = response()->json([
          'status' => 'error',
          'message' => 'Data Already Exist',
        ]);
      }


    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Uang Jalan";
    $page_breadcrumbs = [
      ['page' => '/backend/roadmonies', 'title' => "List Uang Jalan"],
      ['page' => '#', 'title' => "Edit Uang Jalan"],
    ];

    $data = RoadMoney::with(['costumers', 'routefrom', 'routeto', 'cargo', 'typecapacities'])->findOrFail($id);

    return view('backend.masteroperational.roadmonies.edit', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'costumer_id' => 'required|integer',
      'route_from' => 'string|nullable',
      'route_to' => 'string|nullable',
      'cargo_id' => 'string|nullable',
      'fee_thanks' => 'nullable',
      'tax_pph' => 'nullable',
      'road_engkel' => 'integer|nullable',
      'road_tronton' => 'integer|nullable',
      'amount' => 'integer|nullable',
    ]);

    if ($validator->passes()) {
      $data = RoadMoney::find($id);
      $data->update($request->all());

      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been updated',
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
    $data = RoadMoney::find($id);

    if ($data->delete()) {
      $data->typecapacities()->detach();
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    }
    return $response;
  }

  public function typecapacities(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'type_capacity_id' => 'integer',
      'road_money_id' => 'integer',
    ]);

    if ($validator->passes()) {
      $data = RoadMoney::findOrFail($request->road_money_id)->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->where('type', $request->type)
        ->first();

      $response = response()->json([
        'data' => $data,
      ]);
    }
    return $response;
  }

  public function updatetypecapacities(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'type_capacity_id' => 'required|integer',
      'road_engkel' => 'integer|nullable',
      'road_tronton' => 'integer|nullable',
      'type' => 'in:fix,calculate',
    ]);

    if ($validator->passes()) {
      $data = RoadMoney::findOrFail($id);
      if ($data->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->wherePivot('type', $request->type)->count() >= 1) {
        $data->typecapacities()->where('type_capacity_id', $request->type_capacity_id)->wherePivot('type', $request->type)->updateExistingPivot($request->type_capacity_id, $request->except(['type_capacity_id', '_method']));
      } else {
        $data->typecapacities()->attach($request->type_capacity_id, $request->except(['type_capacity_id', '_method']));
      }
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been updated',
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

}
