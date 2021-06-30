<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Prefix;
use Illuminate\Http\Request;
use DataTables;
use Validator;

class PrefixController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:prefixes-list|prefixes-create|prefixes-edit|prefixes-delete', ['only' => ['index']]);
    $this->middleware('permission:prefixes-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:prefixes-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:prefixes-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Prefix";
    $config['page_description'] = "Daftar List Prefix";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Prefix"],
    ];
    if ($request->ajax()) {
      $data = Prefix::query();
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" data-name="' . $row->name . '" data-type="' . $row->type . '" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
          return $actionBtn;
        })->make(true);
    }
    return view('backend.masterfinance.prefixes.index', compact('config', 'page_breadcrumbs'));
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'type' => 'required|string',
    ]);

    if ($validator->passes()) {
      Prefix::create([
        'name' => $request->input('name'),
        'type' => $request->input('type'),
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
   * Update the specified resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @param \App\Models\Service $Service
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'type' => 'required|string',
    ]);

    if ($validator->passes()) {
      $data = Prefix::find($id);
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

  /**
   * Remove the specified resource from storage.
   *
   * @param \App\Models\Service $Service
   * @return \Illuminate\Http\Response
   */
  public function destroy($id)
  {
    $response = response()->json([
      'status' => 'error',
      'message' => 'Data cannot be deleted',
    ]);

    $data = Prefix::find($id);
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
    $data = Prefix::where('name', 'LIKE', '%' . $request->q . '%')
      ->where('type', $request->type)
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = Prefix::where('name', 'LIKE', '%' . $request->q . '%')
      ->where('type', $request->type)
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
