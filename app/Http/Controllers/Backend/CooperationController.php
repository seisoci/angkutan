<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Fileupload;
use App\Http\Controllers\Controller;
use App\Models\Cooperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;
use File;

class CooperationController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:cooperation-list|cooperation-create|cooperation-edit|cooperation-delete', ['only' => ['index']]);
    $this->middleware('permission:cooperation-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:cooperation-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:cooperation-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Kerjasama";
    $config['page_description'] = "Daftar List Kerjasama";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Kerjasama"],
    ];
    if ($request->ajax()) {
      $data = Cooperation::query();
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '<div class="btn-group-vertical" role="group" aria-label="Vertical button group">
            <div class="btn-group" role="group">
                <button id="btnGroupVerticalDrop1" type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fas fa-edit"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupVerticalDrop1">
                    <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="' . $row->id . '" data-image="' . $row->image . '" data-name="' . $row->name . '" data-nickname="' . $row->nickname . '" data-owner="' . $row->owner . '" data-email="' . $row->email . '" data-phone="' . $row->phone . '" data-fax="' . $row->fax . '" data-address="' . $row->address . '" data-default="' . $row->default . '" class="edit dropdown-item">Edit</a>
                    <a href="#" data-toggle="modal" data-target="#modalEditDefault" data-id="' . $row->id . '" class="edit dropdown-item">Ubah Sebagai Default</a>
                    <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete dropdown-item">Delete</a>
                </div>
            </div>
          </div>
          ';
          return $actionBtn;
        })->editColumn('image', function (Cooperation $data) {
          return !empty($data->image) ? asset("/storage/images/thumbnail/$data->image") : asset('media/bg/no-content.svg');
        })->make(true);
    }
    return view('backend.settings.cooperation.index', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string',
      'image' => 'image|mimes:jpg,png,jpeg|max:2048',
    ]);

    if ($validator->passes()) {
      $image = NULL;
      $dimensions = [array('500', '500', 'thumbnail')];
      if (isset($request->image) && !empty($request->image)) {
        $image = Fileupload::uploadImagePublic('image', $dimensions, 'storage');
      }

      Cooperation::create([
        'image' => $image,
        'name' => $request->input('name'),
        'nickname' => $request->input('nickname'),
        'owner' => $request->input('owner'),
        'email' => $request->input('email'),
        'phone' => $request->input('phone'),
        'fax' => $request->input('fax'),
        'address' => $request->input('address'),
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
      'name' => 'string',
      'image' => 'image|mimes:jpg,png,jpeg|max:2048',
    ]);

    if ($validator->passes()) {
      Cooperation::where('default', '1')->update(['default' => '0']);
      $data = Cooperation::find($id);
      if($request->default && !$request->name && !$request->niackname){
        $data->update([
          'default' => '1'
        ]);

        return response()->json([
          'status' => 'success',
          'message' => 'Data has been updated',
        ]);
      }
      $dimensions = [array('500', '500', 'thumbnail')];
      $image = isset($request->image) && !empty($request->image) ? Fileupload::uploadImagePublic('image', $dimensions, 'storage', $data->image) : $data->image;

      $data->update([
        'image' => $image,
        'name' => $request->input('name'),
        'nickname' => $request->input('nickname'),
        'owner' => $request->input('owner'),
        'email' => $request->input('email'),
        'phone' => $request->input('phone'),
        'fax' => $request->input('fax'),
        'default' => $request->input('default'),
        'address' => $request->input('address'),
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

    $data = Cooperation::find($id);
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
    $data = Cooperation::where('name', 'LIKE', '%' . $request->q . '%')
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = Cooperation::where('name', 'LIKE', '%' . $request->q . '%')
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
