<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Facades\Fileupload;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use DataTables;
use Exception;
use Validator;

class EmployeeController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:employees-list|employees-create|employees-edit|employees-delete', ['only' => ['index']]);
    $this->middleware('permission:employees-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:employees-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:employees-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Master Karyawaan";
    $config['page_description'] = "Manage Karyawaan list";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Karyawaan"],
    ];
    if ($request->ajax()) {
      $data = Employee::query();
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a href="#" data-toggle="modal" data-target="#modalShow" data-name="' . $row->name . '" data-position="' . $row->position . '" data-photo="' . $row->photo . '" data-photo_ktp="' . $row->photo_ktp . '" data-status="' . $row->status . '" data-no_card="' . $row->no_card . '"  class="dropdown-item">Show Detail</a>
                  <a href="employees/' . $row->id . '/edit" class="edit dropdown-item">Edit</a>
                  <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete dropdown-item">Delete</a>
                </div>
            </div>';
          return $actionBtn;
        })
        ->editColumn('photo', function (Employee $employee) {
          return !empty($employee->photo) ? asset("storage/images/thumbnail/$employee->photo") : asset('media/users/blank.png');
        })->make(true);
    }
    return view('backend.accounting.employees.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Create Master Karyawaan";
    $page_breadcrumbs = [
      ['page' => '/backend/employees', 'title' => "List Karyawaan"],
      ['page' => '#', 'title' => "Create Karyawaan"],
    ];

    return view('backend.accounting.employees.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required',
      'position' => 'nullable|string',
      'no_card' => 'nullable|string',
      'status' => 'required',
      'profile_avatar' => 'image|mimes:jpg,png,jpeg|max:2048',
      'profile_avatar_remove' => 'between:0,1,NULL',
      'photo_ktp' => 'image|mimes:jpg,png,jpeg|max:2048',
    ]);

    if ($validator->passes()) {
      $image = NULL;
      $dimensions = [array('300', '300', 'thumbnail')];
      $dimensions_ktp = [array('1280', '720', 'thumbnail')];
      $photo = isset($request->profile_avatar) && !empty($request->profile_avatar) ? Fileupload::uploadImagePublic('profile_avatar', $dimensions, 'public') : NULL;
      $photo_ktp = isset($request->photo_ktp) && !empty($request->photo_ktp) ? Fileupload::uploadImagePublic('photo_ktp', $dimensions_ktp, 'public') : NULL;
      $user = Employee::create([
        'name' => $request->input('name'),
        'position' => $request->input('position'),
        'no_card' => $request->input('no_card'),
        'photo' => $photo,
        'photo_ktp' => $photo_ktp,
        'status' => $request->input('status'),
      ]);

      DB::commit();
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => '/backend/employees'
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Master Karyawaan";
    $page_breadcrumbs = [
      ['page' => '/backend/employees', 'title' => "List Karyawaan"],
      ['page' => '#', 'title' => "Edit User"],
    ];
    $data = Employee::findOrFail($id);

    return view('backend.accounting.employees.edit', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required',
      'position' => 'nullable|string',
      'no_card' => 'nullable|string',
      'status' => 'required',
      'profile_avatar' => 'image|mimes:jpg,png,jpeg|max:2048',
      'profile_avatar_remove' => 'between:0,1,NULL',
      'photo_ktp' => 'image|mimes:jpg,png,jpeg|max:2048',
    ]);

    $data = Employee::findOrFail($id);
    if ($validator->passes()) {
      $dimensions = [array('300', '300', 'thumbnail')];
      $dimensions_ktp = [array('1280', '720', 'thumbnail')];
      $photo = isset($request->profile_avatar) && !empty($request->profile_avatar) ? Fileupload::uploadImagePublic('profile_avatar', $dimensions, $data->photo) : $data->photo;
      $photo_ktp = isset($request->photo_ktp) && !empty($request->photo_ktp) ? Fileupload::uploadImagePublic('photo_ktp', $dimensions_ktp, $data->photo_ktp) : $data->photo_ktp;
      $data->update([
        'name' => $request->input('name'),
        'position' => $request->input('position'),
        'no_card' => $request->input('no_card'),
        'photo' => $photo,
        'photo_ktp' => $photo_ktp,
        'status' => $request->input('status'),
      ]);
      DB::commit();
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => '/backend/employees'
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function destroy($id)
  {
    $data = Employee::find($id);
    File::delete(["images/original/$data->image", "images/thumbnail/$data->image"]);
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
    $type = !empty($request->type) || isset($request->type) ? $request->type : NULL;
    $status = $request->status ?? NULL;
    $data = Employee::where('name', 'LIKE', '%' . $request->q . '%')
      ->when($status, function ($q, $status) {
        return $q->where('status', $status);
      })
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = Employee::where('name', 'LIKE', '%' . $request->q . '%')
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
