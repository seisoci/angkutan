<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Sparepart;
use DataTables;
use File;
use Fileupload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SparepartController extends Controller
{

  function __construct()
  {
    $this->middleware('permission:spareparts-list|spareparts-create|spareparts-edit|spareparts-delete', ['only' => ['index']]);
    $this->middleware('permission:spareparts-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:spareparts-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:spareparts-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Spare Part";
    $config['page_description'] = "Daftar List Spare Part";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Spare Part"],
    ];

    if ($request->ajax()) {
      $categories_id = $request->category_id;
      $brand_id = $request->brand_id;
      $data = Sparepart::with(['supplier', 'brand', 'categories'])
        ->when($request->brand_id, function ($query, $brand_id) {
          return $query->where('brand_id', $brand_id);
        })
        ->whereHas('categories', function ($query) use ($categories_id) {
          isset($categories_id) ? $query->where('category_id', $categories_id) : NULL;
        })
        ->select('spareparts.*');
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
              <a href="spareparts/' . $row->id . '/edit" class="edit btn btn-warning btn-sm">Edit</a>
              <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete btn btn-danger btn-sm">Delete</a>';
          return $actionBtn;
        })->editColumn('image', function (Sparepart $data) {
          return !empty($data->photo) ? asset("storage/images/thumbnail/$data->photo") : asset('media/bg/no-content.svg');
        })->make(true);
    }
    return view('backend.mastersparepart.spareparts.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Create Spare Part";
    $page_breadcrumbs = [
      ['page' => '/backend/spareparts', 'title' => "List Spare Part"],
      ['page' => '#', 'title' => "Create Spare Part"],
    ];
    return view('backend.mastersparepart.spareparts.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'brand_id' => 'required|integer',
      'name' => 'required|string',
      'categories' => 'required|array',
      'categories.*' => 'required|distinct',
      'photo' => 'image|mimes:jpg,png,jpeg|max:2048',
    ]);

    if ($validator->passes()) {
      $dimensions = [array('200', '200', 'thumbnail')];
      $image = isset($request->photo) && !empty($request->photo) ? Fileupload::uploadImagePublic('photo', $dimensions, 'public') : NULL;
      $data = Sparepart::create([
        'brand_id' => $request->input('brand_id'),
        'name' => $request->input('name'),
        'photo' => $image,
      ]);
      $categories = Category::find($request->input('categories'));
      $data->categories()->attach($categories);

      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => '/backend/spareparts'
      ]);

    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Kendaraan";
    $page_breadcrumbs = [
      ['page' => '/backend/spareparts', 'title' => "List Kendaraan"],
      ['page' => '#', 'title' => "Edit Kendaraan"],
    ];

    $data = Sparepart::with(['brand', 'categories'])->findOrFail($id);

    return view('backend.mastersparepart.spareparts.edit', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function update(Request $request, Sparepart $sparepart)
  {
    $validator = Validator::make($request->all(), [
      'brand_id' => 'required|integer',
      'name' => 'required|string',
      'categories' => 'required|array',
      'categories.*' => 'required|distinct',
      'photo' => 'image|mimes:jpg,png,jpeg|max:2048',
    ]);

    if ($validator->passes()) {
      $dimensions = [array('200', '200', 'thumbnail')];
      $image = isset($request->photo) && !empty($request->photo) ? Fileupload::uploadImagePublic('photo', $dimensions, 'public', $sparepart->photo) : $sparepart->photo;
      $sparepart->update([
        'brand_id' => $request->input('brand_id'),
        'name' => $request->input('name'),
        'photo' => $image,
      ]);
      $categories = Category::find($request->input('categories'));
      $sparepart->categories()->sync($categories);

      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been updated',
        'redirect' => '/backend/spareparts'
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }

    return $response;
  }

  public function destroy(Sparepart $sparepart)
  {
    $response = response()->json([
      'status' => 'error',
      'message' => 'Data cannot be deleted',
    ]);
    File::delete(["images/original/$sparepart->photo", "images/thumbnail/$sparepart->photo"]);
    if ($sparepart->delete()) {
      $sparepart->categories()->detach();
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
    $data = Sparepart::where('name', 'LIKE', '%' . $request->q . '%')
      ->when($request->used, function ($query, $used) {
        return $query->whereNotIn('id', $used);
      })
      ->orderBy('name')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, name as text')
      ->get();

    $count = Sparepart::where('name', 'LIKE', '%' . $request->q . '%')
      ->when($request->used, function ($query, $used) {
        return $query->whereNotIn('id', $used);
      })
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
