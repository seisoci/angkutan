<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Costumer;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Validator;

class CostumerController extends Controller
{

  function __construct()
  {
    $this->middleware('permission:costumers-list|costumers-create|costumers-edit|costumers-delete', ['only' => ['index']]);
    $this->middleware('permission:costumers-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:costumers-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:costumers-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Pelanggan";
    $config['page_description'] = "Manage list Pelanggan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Pelanggan"],
    ];

    if ($request->ajax()) {
      $data = Costumer::with('cooperation');
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <a href="#" data-toggle="modal" data-target="#modalShow" data-name="' . $row->name . '" data-emergency_name="' . $row->emergency_name . '" data-emergency_phone="' . $row->emergency_phone . '" data-phone="' . $row->phone . '" data-address="' . $row->address . '" data-description="' . $row->description . '" data-cooperation="' . $row->cooperation->nickname . '"  class="btn btn-info btn-sm">Show Detail</a>
            <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" data-name="' . $row->name . '" data-emergency_name="' . $row->emergency_name . '" data-emergency_phone="' . $row->emergency_phone . '" data-phone="' . $row->phone . '" data-address="' . $row->address . '" data-description="' . $row->description . '" data-cooperation_id="' . $row->cooperation_id . '" data-cooperation_name="' . $row->cooperation->name . '" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
          return $actionBtn;
        })->make(true);

    }
    return view('backend.masteroperational.costumers.index', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'phone' => 'required|string',
      'cooperation_id' => 'required|integer',
    ]);

    if ($validator->passes()) {
      Costumer::create([
        'name' => $request->input('name'),
        'emergency_name' => $request->input('emergency_name'),
        'emergency_phone' => $request->input('emergency_phone'),
        'phone' => $request->input('phone'),
        'address' => $request->input('address'),
        'description' => $request->input('description'),
        'cooperation_id' => $request->input('cooperation_id'),
      ]);
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function update(Request $request, Costumer $costumer)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'phone' => 'required|string',
      'cooperation_id' => 'required|integer',
    ]);

    if ($validator->passes()) {
      $costumer->update([
        'name' => $request->input('name'),
        'emergency_name' => $request->input('emergency_name'),
        'emergency_phone' => $request->input('emergency_phone'),
        'phone' => $request->input('phone'),
        'address' => $request->input('address'),
        'description' => $request->input('description'),
        'cooperation_id' => $request->input('cooperation_id'),
      ]);
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }

    return $response;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param \App\Models\Costumer $costumer
   * @return \Illuminate\Http\Response
   */
  public function destroy(Costumer $costumer)
  {
    $response = response()->json([
      'status' => 'error',
      'message' => 'Data cannot be deleted',
    ]);
    if ($costumer->delete()) {
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    }
    return $response;
  }

  public function select2(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Costumer::where('name', 'LIKE', '%' . $request->q . '%')
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = Costumer::where('name', 'LIKE', '%' . $request->q . '%')
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
