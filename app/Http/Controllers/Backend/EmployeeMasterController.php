<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\EmployeeMaster;
use Illuminate\Http\Request;
use Validator;
use DataTables;

class EmployeeMasterController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:monthlymaster-list|monthlymaster-create|monthlymaster-edit|monthlymaster-delete', ['only' => ['index']]);
    $this->middleware('permission:monthlymaster-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:monthlymaster-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:monthlymaster-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Tipe Gaji";
    $config['page_description'] = "Daftar List Tipe Gaji";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Tipe Gaji"],
    ];
    if ($request->ajax()) {
      $data = EmployeeMaster::query();
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" data-name="' . $row->name . '" data-type="' . $row->type . '" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
          return $actionBtn;
        })->make(true);
    }
    return view('backend.accounting.employessmaster.index', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
    ]);

    if ($validator->passes()) {
      EmployeeMaster::create([
        'name' => $request->input('name'),
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

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
    ]);

    if ($validator->passes()) {
      $data = EmployeeMaster::find($id);
      $data->update([
        'name' => $request->input('name'),
        'type' => $request->input('type'),
      ]);
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

    $data = EmployeeMaster::find($id);
    if ($data->delete()) {
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
    $data = EmployeeMaster::where('name', 'LIKE', '%' . $request->q . '%')
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = EmployeeMaster::where('name', 'LIKE', '%' . $request->q . '%')
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
