<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Facades\Fileupload;
use App\Models\Driver;
use Illuminate\Http\Request;
use DataTables;
use DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Request as Req;

class DriverController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:drivers-list|drivers-create|drivers-edit|drivers-delete', ['only' => ['index']]);
    $this->middleware('permission:drivers-create|anotherexpedition-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:drivers-edit|anotherexpedition-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:drivers-delete|anotherexpedition-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Supir";
    $config['page_description'] = "Daftar List Supir";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Supir"],
    ];

    if ($request->ajax()) {
      $data = Driver::where('another_expedition_id', NULL);
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
              <a href="drivers/' . $row->id . '" class="btn btn-info btn-sm">Show Detail</a>
              <a href="drivers/' . $row->id . '/edit" class="edit btn btn-warning btn-sm">Edit</a>
              <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
          return $actionBtn;
        })->editColumn('image', function (Driver $data) {
          return !empty($data->photo) ? asset("/images/thumbnail/$data->photo") : asset('media/users/blank.png');
        })->make(true);
    }
    return view('backend.masteroperational.drivers.index', compact('config', 'page_breadcrumbs'));
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function create()
  {
    $config['page_title'] = "Create Supir";
    $page_breadcrumbs = [
      ['page' => '/backend/driver', 'title' => "List Supir"],
      ['page' => '#', 'title' => "Create Supir"],
    ];
    return view('backend.masteroperational.drivers.create', compact('config', 'page_breadcrumbs'));
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
      'another_expedition_id' => 'integer|nullable',
      'name' => 'required',
      'address' => 'string|nullable',
      'bank_name' => 'string|nullable',
      'no_card' => 'string|nullable',
      'phone' => 'string|nullable',
      'ktp' => 'string|nullable',
      'sim' => 'string|nullable',
      'expired_sum' => 'date_format:Y-m-d|nullable',
      'status' => 'required|in:active,inactive',
      'description' => 'string|nullable',
      'profile_avatar' => 'image|mimes:jpg,png,jpeg|max:2048',
      'photo_ktp' => 'image|mimes:jpg,png,jpeg|max:2048',
      'photo_sim' => 'image|mimes:jpg,png,jpeg|max:2048',
      'profile_avatar_remove' => 'between:0,1,NULL'
    ]);

    if ($validator->passes()) {
      $dimensions = [array('1280', '720', 'thumbnail')];
      $dimensions_profile = [array('300', '300', 'thumbnail')];
      $image = isset($request->profile_avatar) && !empty($request->profile_avatar) ? Fileupload::uploadImagePublic('profile_avatar', $dimensions_profile, 'public') : NULL;
      $photo_ktp = isset($request->photo_ktp) && !empty($request->photo_ktp) ? Fileupload::uploadImagePublic('photo_ktp', $dimensions, 'public') : NULL;
      $photo_sim = isset($request->photo_sim) && !empty($request->photo_sim) ? Fileupload::uploadImagePublic('photo_sim', $dimensions, 'public') : NULL;
      Driver::create([
        'another_expedition_id' => $request->input('another_expedition_id') ?? NULL,
        'name' => $request->input('name'),
        'bank_name' => $request->input('bank_name'),
        'no_card' => $request->input('no_card'),
        'address' => $request->input('address'),
        'phone' => $request->input('phone'),
        'ktp' => $request->input('ktp'),
        'sim' => $request->input('sim'),
        'expired_sim' => $request->input('expired_sim'),
        'status' => $request->input('status'),
        'description' => $request->input('description'),
        'photo' => $image,
        'photo_ktp' => $photo_ktp,
        'photo_sim' => $photo_sim,
      ]);

      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => !empty($request->input('another_expedition_id')) ? '/backend/anotherexpedition/' . $request->input('another_expedition_id') : '/backend/drivers'
      ]);

    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  /**
   * Display the specified resource.
   *
   * @param \App\Models\Driver $driver
   * @return \Illuminate\Http\Response
   */
  public function show(Driver $driver)
  {
    $config['page_title'] = "Detail Supir";
    $page_breadcrumbs = [
      ['page' => '/backend/drivers', 'title' => "List Supir"],
      ['page' => '#', 'title' => "Detail Supir"],
    ];

    $data = $driver;
    return view('backend.masteroperational.drivers.show', compact('config', 'page_breadcrumbs', 'data'));
  }

  /**
   * Show the form for editing the specified resource.
   *
   * @param \App\Models\Driver $driver
   * @return \Illuminate\Http\Response
   */
  public function edit(Driver $driver)
  {
    $config['page_title'] = "Edit Supir";
    $page_breadcrumbs = [
      ['page' => '/backend/drivers', 'title' => "List Supir"],
      ['page' => '#', 'title' => "Edit Supir"],
    ];

    $data = $driver;

    return view('backend.masteroperational.drivers.edit', compact('config', 'page_breadcrumbs', 'data'));
  }

  /**
   * Update the specified resource in storage.
   *
   * @param \Illuminate\Http\Request $request
   * @param \App\Models\Driver $driver
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request, Driver $driver)
  {
    $validator = Validator::make($request->all(), [
      'another_expedition_id' => 'integer|nullable',
      'name' => 'required',
      'address' => 'string|nullable',
      'bank_name' => 'string|nullable',
      'no_card' => 'string|nullable',
      'phone' => 'string|nullable',
      'ktp' => 'string|nullable',
      'sim' => 'string|nullable',
      'expired_sum' => 'date_format:Y-m-d|nullable',
      'status' => 'required|in:active,inactive',
      'description' => 'string|nullable',
      'profile_avatar' => 'image|mimes:jpg,png,jpeg|max:2048',
      'photo_ktp' => 'image|mimes:jpg,png,jpeg|max:2048',
      'photo_sim' => 'image|mimes:jpg,png,jpeg|max:2048',
      'profile_avatar_remove' => 'between:0,1,NULL'
    ]);

    if ($validator->passes()) {
      $dimensions = [array('1280', '720', 'thumbnail')];
      $dimensions_profile = [array('300', '300', 'thumbnail')];
      $image = isset($request->profile_avatar) && !empty($request->profile_avatar) ? Fileupload::uploadImagePublic('profile_avatar', $dimensions_profile, 'public', $driver->photo) : $driver->photo;
      $photo_ktp = isset($request->photo_ktp) && !empty($request->photo_ktp) ? Fileupload::uploadImagePublic('photo_ktp', $dimensions, 'public', $driver->photo_ktp) : $driver->photo_ktp;
      $photo_sim = isset($request->photo_sim) && !empty($request->photo_sim) ? Fileupload::uploadImagePublic('photo_sim', $dimensions, 'public', $driver->photo_sim) : $driver->photo_sim;
      $driver->update([
        'another_expedition_id' => $request->input('another_expedition_id') ?? NULL,
        'name' => $request->input('name'),
        'bank_name' => $request->input('bank_name'),
        'no_card' => $request->input('no_card'),
        'address' => $request->input('address'),
        'phone' => $request->input('phone'),
        'ktp' => $request->input('ktp'),
        'sim' => $request->input('sim'),
        'expired_sim' => $request->input('expired_sim'),
        'status' => $request->input('status'),
        'description' => $request->input('description'),
        'photo' => $image,
        'photo_ktp' => $photo_ktp,
        'photo_sim' => $photo_sim,
      ]);
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been updated',
        'redirect' => !empty($request->input('another_expedition_id')) ? '/backend/anotherexpedition/' . $request->input('another_expedition_id') : '/backend/drivers'
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }

    return $response;
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param \App\Models\Driver $driver
   * @return \Illuminate\Http\Response
   */
  public function destroy(Driver $driver)
  {
    $response = response()->json([
      'status' => 'error',
      'message' => 'Data cannot be deleted',
    ]);
    File::delete(["images/original/$driver->photo", "images/thumbnail/$driver->photo",
      "images/original/$driver->photo_ktp", "images/thumbnail/$driver->photo_ktp",
      "images/original/$driver->photo_sim", "images/thumbnail/$driver->photo_sim"]);
    if ($driver->delete()) {
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
    $type = !empty($request->type) || isset($request->type) ? $request->type : NULL;
    $status = $request->status ?? NULL;
    $data = Driver::where('name', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', $type)
      ->when($status, function ($q, $status) {
        return $q->where('status', $status);
      })
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = Driver::where('name', 'LIKE', '%' . $request->q . '%')
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

  public function select2self(Request $request)
  {
    $page = $request->page;
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $type = !empty($request->type) || isset($request->type) ? $request->type : NULL;
    $status = $request->status ?? NULL;
    $data = Driver::where('name', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', NULL)
      ->when($status, function ($q, $status) {
        return $q->where('status', $status);
      })
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = Driver::where('name', 'LIKE', '%' . $request->q . '%')
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
    $type = !empty($request->type) || isset($request->type) ? $request->type : NULL;
    $status = $request->status ?? NULL;
    $data = Driver::where('name', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', '<>', NULL)
      ->when($status, function ($q, $status) {
        return $q->where('status', $status);
      })
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = Driver::where('name', 'LIKE', '%' . $request->q . '%')
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
    $resultCount = 10;
    $offset = ($page - 1) * $resultCount;
    $type = !empty($request->type) || isset($request->type) ? $request->type : NULL;
    $status = $request->status ?? NULL;
    $data = Driver::where('name', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', $type)
      ->when($status, function ($q, $status) {
        return $q->where('status', $status);
      })
      ->whereNotIn('id', [DB::raw('SELECT driver_id FROM job_orders WHERE `status_cargo`= "mulai"')])
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = Driver::where('name', 'LIKE', '%' . $request->q . '%')
      ->where('another_expedition_id', $type)
      ->when($status, function ($q, $status) {
        return $q->where('status', $status);
      })
      ->whereNotIn('id', [DB::raw('SELECT driver_id FROM job_orders WHERE `status_cargo`= "mulai"')])
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
