<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DataTables;

class RouteController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:routes-list|routes-create|routes-edit|routes-delete', ['only' => ['index']]);
    $this->middleware('permission:routes-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:routes-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:routes-delete', ['only' => ['destroy']]);
  }

    public function index(Request $request)
    {
      $config['page_title']       = "List Rute";
      $config['page_description'] = "Daftar List Rute";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Rute"],
      ];
      if ($request->ajax()) {
        $data = Route::query();
        return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function($row){
            $actionBtn = '
            <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="'. $row->id.'" data-name="'.$row->name.'" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
            return $actionBtn;
        })->make(true);
      }
      return view('backend.masteroperational.routes.index', compact('config', 'page_breadcrumbs'));
    }

    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'name'    => 'required|string',
      ]);

      if($validator->passes()){
        Route::create([
          'name'      => $request->input('name'),
        ]);
        $response = response()->json([
          'status'  => 'success',
          'message' => 'Data has been saved',
        ]);
      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    public function update(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'name'    => 'required|string',
      ]);

      if($validator->passes()){
        $data = Route::find($id);
        $data->update([
          'name'      => $request->input('name'),
        ]);
        $response = response()->json([
          'status'  => 'success',
          'message' => 'Data has been updated',
        ]);
      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    public function destroy($id)
    {
      $response = response()->json([
        'status' => 'error',
        'message' => 'Data cannot be deleted',
      ]);

      $data = Route::find($id);
      if($data->delete()){
        $response = response()->json([
          'status'  => 'success',
          'message' => 'Data has been deleted',
        ]);
      }
      return $response;
    }

    public function select2(Request $request){
      $page = $request->page;
      $resultCount = 10;
      $offset = ($page - 1) * $resultCount;
      $data = Route::where('name', 'LIKE', '%' . $request->q. '%')
          ->orderBy('name')
          ->skip($offset)
          ->take($resultCount)
          ->selectRaw('id, name as text')
          ->get();

      $count = Route::where('name', 'LIKE', '%' . $request->q. '%')
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
