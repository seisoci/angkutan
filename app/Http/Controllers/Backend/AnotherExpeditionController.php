<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\AnotherExpedition;
use App\Models\Driver;
use App\Models\Transport;
use Illuminate\Http\Request;
use DataTables;
use Validator;

class AnotherExpeditionController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:anotherexpedition-list|anotherexpedition-create|anotherexpedition-edit|anotherexpedition-delete', ['only' => ['index']]);
    $this->middleware('permission:anotherexpedition-create', ['only' => ['create', 'store', 'create_transport', 'create_driver']]);
    $this->middleware('permission:anotherexpedition-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:anotherexpedition-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List LDO";
    $config['page_description'] = "Daftar List LDO";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List LDO"],
    ];
    if ($request->ajax()) {
      $data = AnotherExpedition::query();
      return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <a href="anotherexpedition/' . $row->id . '"  class="btn btn-info btn-sm">Lihat Detail</a>
            <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" data-name="' . $row->name . '" data-amount="' . $row->amount . '" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
          return $actionBtn;
        })->make(true);
    }
    return view('backend.masteroperational.anotherexpedition.index', compact('config', 'page_breadcrumbs'));
  }

  public function show($id, Request $request)
  {
    $data = AnotherExpedition::findOrFail($id);
    $config['page_title'] = "List Detail LDO " . $data->name;
    $config['page_description'] = "Tabel Detail LDO " . $data->name;
    $config['page_title_driver'] = "List Supir LDO " . $data->name;
    $config['page_description_driver'] = "Tabel Supir LDO " . $data->name;
    $config['page_title_transport'] = "List Kendaraan LDO " . $data->name;
    $config['page_description_transport'] = "Tabel Kendaraan LDO " . $data->name;
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Detail LDO " . $data->name],
    ];

    return view('backend.masteroperational.anotherexpedition.show', compact('config', 'page_breadcrumbs', 'id'));
  }

  public function datatable_transport($id, Request $request)
  {
    $data = Transport::where('another_expedition_id', $id);
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('action', function ($row) {
        $actionBtn = '
          <a href="anotherexpedition/' . $row->id . '"  class="btn btn-info btn-sm">Lihat Detail</a>
          <a href="/backend/transports/' . $row->id . '/edit" class="edit btn btn-warning btn-sm">Edit</a>
          <a href="#" data-toggle="modal" data-target="#modalDeleteTransport" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
        return $actionBtn;
      })
      ->editColumn('image', function (Transport $data) {
        return !empty($data->photo) ? asset("storage/images/thumbnail/$data->photo") : asset('media/users/blank.png');
      })->make(true);
  }

  public function datatable_driver($id, Request $request)
  {
    $data = Driver::where('another_expedition_id', $id);
    return Datatables::of($data)
      ->addIndexColumn()
      ->addColumn('action', function ($row) {
        $actionBtn = '
          <a href="anotherexpedition/' . $row->id . '"  class="btn btn-info btn-sm">Lihat Detail</a>
          <a href="/backend/drivers/' . $row->id . '/edit" class="edit btn btn-warning btn-sm">Edit</a>
          <a href="#" data-toggle="modal" data-target="#modalDeleteDriver" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
        return $actionBtn;
      })
      ->editColumn('image', function (Driver $data) {
        return !empty($data->photo) ? asset("storage/images/thumbnail/$data->photo") : asset('media/users/blank.png');
      })->make(true);
  }

  public function create_transport($id)
  {
    $data = AnotherExpedition::findOrFail($id);
    $another_expedition_id = $id;
    $config['page_title'] = "Create Kendaraan LDO " . $data->name;
    $page_breadcrumbs = [
      ['page' => '/backend/driver', 'title' => "List Kendaraan"],
      ['page' => '#', 'title' => "Create Kendaraan"],
    ];
    return view('backend.masteroperational.transports.create', compact('config', 'page_breadcrumbs', 'another_expedition_id'));
  }

  public function create_driver($id)
  {
    $data = AnotherExpedition::findOrFail($id);
    $another_expedition_id = $id;
    $config['page_title'] = "Create Supir LDO " . $data->name;
    $page_breadcrumbs = [
      ['page' => '/backend/driver', 'title' => "List Supir"],
      ['page' => '#', 'title' => "Create Supir"],
    ];
    return view('backend.masteroperational.drivers.create', compact('config', 'page_breadcrumbs', 'another_expedition_id'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
    ]);

    if ($validator->passes()) {
      AnotherExpedition::create([
        'name' => $request->input('name'),
        'amount' => $request->input('amount'),
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
      $data = AnotherExpedition::find($id);
      $data->update([
        'name' => $request->input('name'),
        'amount' => $request->input('amount'),
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

  public function destroy($id)
  {
    $response = response()->json([
      'status' => 'error',
      'message' => 'Data cannot be deleted',
    ]);

    $data = AnotherExpedition::find($id);
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
    $data = AnotherExpedition::where('name', 'LIKE', '%' . $request->q . '%')
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = AnotherExpedition::where('name', 'LIKE', '%' . $request->q . '%')
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
