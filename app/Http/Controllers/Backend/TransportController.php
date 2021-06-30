<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Fileupload;
use App\Http\Controllers\Controller;
use App\Models\Transport;
use Illuminate\Http\Request;
use DataTables;
use DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class TransportController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:transports-list|transports-create|transports-edit|transports-delete', ['only' => ['index']]);
    $this->middleware('permission:transports-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:transports-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:transports-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Kendaraan";
    $config['page_description'] = "Daftar List Kendaraan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Kendaraan"],
    ];

    if ($request->ajax()) {
      $data = Transport::where('another_expedition_id', NULL);
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <a href="#" data-toggle="modal" data-target="#modalShow" data-num_pol="' . $row->num_pol . '" data-merk="' . $row->merk . '" data-type="' . $row->type . '" data-type_car="' . $row->type_car . '" data-dump="' . $row->dump . '" data-year="' . $row->year . '" data-max_weight="' . $row->max_weight . '" data-expired_stnk="' . $row->expired_stnk . '" data-expired_kir="' . $row->expired_kir . '" data-description="' . $row->description . '" data-photo="' . $row->photo . '"  class="btn btn-info btn-sm">Show Detail</a>
            <a href="transports/' . $row->id . '/edit" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
          return $actionBtn;
        })->editColumn('image', function (Transport $data) {
          return !empty($data->photo) ? asset("/images/thumbnail/$data->photo") : asset('media/bg/no-content.svg');
        })->make(true);
    }
    return view('backend.masteroperational.transports.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Create Kendaraan";
    $page_breadcrumbs = [
      ['page' => '/backend/transports', 'title' => "List Kendaraan"],
      ['page' => '#', 'title' => "Create Kendaraan"],
    ];
    return view('backend.masteroperational.transports.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'another_expedition_id' => 'integer|nullable',
      'num_pol' => 'required|string',
      'merk' => 'string|nullable',
      'type' => 'string|nullable',
      'type_car' => 'string|nullable',
      'year' => 'date_format:Y|nullable',
      'max_weight' => 'integer|nullable',
      'expired_stnk' => 'date_format:Y-m-d|nullable',
      'expired_kir' => 'date_format:Y-m-d|nullable',
      'description' => 'string|nullable',
      'photo' => 'image|mimes:jpg,png,jpeg|max:2048',
    ]);

    if ($validator->passes()) {
      $dimensions = [array('1280', '720', 'thumbnail')];
      $image = isset($request->photo) && !empty($request->photo) ? Fileupload::uploadImagePublic('photo', $dimensions, 'public') : NULL;
      Transport::create([
        'another_expedition_id' => $request->input('another_expedition_id') ?? NULL,
        'num_pol' => $request->input('num_pol'),
        'merk' => $request->input('merk'),
        'type' => $request->input('type'),
        'type_car' => $request->input('type_car'),
        'dump' => $request->input('dump'),
        'year' => $request->input('year'),
        'max_weight' => $request->input('max_weight'),
        'expired_stnk' => $request->input('expired_stnk'),
        'expired_kir' => $request->input('expired_kir'),
        'description' => $request->input('description'),
        'photo' => $image,
      ]);

      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => !empty($request->input('another_expedition_id')) ? '/backend/anotherexpedition/' . $request->input('another_expedition_id') : '/backend/transports'
      ]);

    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function edit(Transport $transport)
  {
    $config['page_title'] = "Edit Kendaraan";
    $page_breadcrumbs = [
      ['page' => '/backend/transports', 'title' => "List Kendaraan"],
      ['page' => '#', 'title' => "Edit Kendaraan"],
    ];

    $data = $transport;

    return view('backend.masteroperational.transports.edit', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function update(Request $request, Transport $transport)
  {
    $validator = Validator::make($request->all(), [
      'another_expedition_id' => 'integer|nullable',
      'num_pol' => 'required|string',
      'merk' => 'string|nullable',
      'type' => 'string|nullable',
      'type_car' => 'string|nullable',
      'year' => 'date_format:Y|nullable',
      'max_weight' => 'integer|nullable',
      'expired_stnk' => 'date_format:Y-m-d|nullable',
      'expired_kir' => 'date_format:Y-m-d|nullable',
      'description' => 'string|nullable',
      'photo' => 'image|mimes:jpg,png,jpeg|max:2048'
    ]);

    if ($validator->passes()) {
      $dimensions = [array('1280', '720', 'thumbnail')];
      $image = isset($request->photo) && !empty($request->photo) ? Fileupload::uploadImagePublic('photo', $dimensions, 'public', $transport->photo) : $transport->photo;
      $transport->update([
        'another_expedition_id' => $request->input('another_expedition_id') ?? NULL,
        'num_pol' => $request->input('num_pol'),
        'merk' => $request->input('merk'),
        'type' => $request->input('type'),
        'type_car' => $request->input('type_car'),
        'year' => $request->input('year'),
        'max_weight' => $request->input('max_weight'),
        'expired_stnk' => $request->input('expired_stnk'),
        'expired_kir' => $request->input('expired_kir'),
        'description' => $request->input('description'),
        'photo' => $image,
      ]);
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been updated',
        'redirect' => !empty($request->input('another_expedition_id')) ? '/backend/anotherexpedition/' . $request->input('another_expedition_id') : '/backend/transports'
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }

    return $response;
  }

  public function destroy($id)
  {
    $transport = Transport::findOrFail($id);
    $response = response()->json([
      'status' => 'error',
      'message' => 'Data cannot be deleted',
    ]);
    File::delete(["images/original/$transport->photo", "images/thumbnail/$transport->photo"]);
    if ($transport->delete()) {
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    }
    return $response;
  }

  public function select2tonase(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $type = !empty($request->type) || isset($request->type) ? $request->type : NULL;
    $data = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', $type)
      ->orderBy('num_pol')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, num_pol as text')
      ->get();

    $count = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', $type)
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

  public function select2(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
      ->orderBy('num_pol')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, CONCAT(`num_pol`," (", UPPER(`type_car`), ")") as text')
      ->get();

    $count = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
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

  public function select2self(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', NULL)
      ->orderBy('num_pol')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, CONCAT(`num_pol`," (", UPPER(`type_car`), ")") as text')
      ->get();

    $count = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', NULL)
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

  public function select2ldo(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', '<>', NULL)
      ->orderBy('num_pol')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, CONCAT(`num_pol`," (", UPPER(`type_car`), ")") as text')
      ->get();

    $count = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', '<>', NULL)
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

  public function select2joborder(Request $request)
  {
    $page = $request->page;
    $type = !empty($request->type) || isset($request->type) ? $request->type : NULL;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $data = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', $type)
      ->whereNotIn('id', [DB::raw('SELECT transport_id FROM job_orders WHERE `status_cargo`= "mulai"')])
      ->orderBy('num_pol')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, CONCAT(`num_pol`," (", UPPER(`type_car`), ")") as text')
      ->get();

    $count = Transport::where('num_pol', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', $type)
      ->whereNotIn('id', [DB::raw('SELECT transport_id FROM job_orders WHERE `status_cargo`= "mulai"')])
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
