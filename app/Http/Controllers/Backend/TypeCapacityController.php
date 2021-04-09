<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\TypeCapacity;
use Illuminate\Http\Request;
use DataTables;
use Validator;

class TypeCapacityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Tipe Kapasitas";
      $config['page_description'] = "Daftar List Tipe Kapasitas";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Tipe Kapasitas"],
      ];
      if ($request->ajax()) {
        $data = TypeCapacity::query();
        return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function($row){
            $actionBtn = '
            <a href="#" data-toggle="modal" data-target="#modalEdit" data-id="'. $row->id.'" data-name="'.$row->name.'" class="edit btn btn-warning btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
            return $actionBtn;
        })->make(true);
      }
      return view('backend.masteroperational.typecapacities.index', compact('config', 'page_breadcrumbs'));
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
        TypeCapacity::create([
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
     * @param  \App\Models\TypeCapacity  $TypeCapacity
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
      $validator = Validator::make($request->all(), [
        'name'    => 'required|string',
      ]);

      if($validator->passes()){
        $data = TypeCapacity::find($id);
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
     * @param  \App\Models\TypeCapacity  $TypeCapacity
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $response = response()->json([
          'status' => 'error',
          'message' => 'Data cannot be deleted',
      ]);

      $data = TypeCapacity::find($id);
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
      $data = TypeCapacity::where('name', 'LIKE', '%' . $request->q. '%')
          ->orderBy('name')
          ->skip($offset)
          ->take($resultCount)
          ->selectRaw('id, name as text')
          ->get();

      $count = TypeCapacity::where('name', 'LIKE', '%' . $request->q. '%')
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
