<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Cargo;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Validator;

class CargoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Muatan";
      $config['page_description'] = "Daftar List Muatan";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Muatan"],
      ];
      if ($request->ajax()) {
        $data = Cargo::query();
        return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function($row){
            $actionBtn = '
            <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="'. $row->id.'" data-name="'.$row->name.'" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
            return $actionBtn;
        })->make(true);
      }
      return view('backend.masteroperational.cargos.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $validator = Validator::make($request->all(), [
        'name'    => 'required|string',
      ]);

      if($validator->passes()){
        Cargo::create([
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
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cargo  $cargo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'name'    => 'required|string',
      ]);

      if($validator->passes()){
        $data = Cargo::find($id);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cargo  $cargo
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $response = response()->json([
        'status' => 'error',
        'message' => 'Data cannot be deleted',
      ]);

      $data = Cargo::find($id);
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
      $data = Cargo::where('name', 'LIKE', '%' . $request->q. '%')
          ->orderBy('name')
          ->skip($offset)
          ->take($resultCount)
          ->selectRaw('id, name as text')
          ->get();

      $count = Cargo::where('name', 'LIKE', '%' . $request->q. '%')
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
