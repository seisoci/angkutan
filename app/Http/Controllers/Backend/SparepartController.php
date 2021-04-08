<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Sparepart;
use Illuminate\Http\Request;
use DataTables;
use DB;
use File;
use Fileupload;
use Illuminate\Support\Facades\Validator;

class SparepartController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Spare Part";
      $config['page_description'] = "Daftar List Spare Part";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Spare Part"],
      ];

      // dd(Sparepart::with(['supplier', 'brand'])->get()->toArray());
      if ($request->ajax()) {
        $categories_id = $request->category_id;
        $data = Sparepart::with(['supplier', 'brand', 'categories'])
        ->when($request->supplier_sparepart_id, function ($query, $id) {
          return $query->where('supplier_sparepart_id', $id);
        })
        ->when($request->brand_id, function ($query, $id) {
          return $query->where('brand_id', $id);
        })
        ->whereHas('categories', function($query) use($categories_id) {
          isset($categories_id) ? $query->where('category_id', $categories_id) : NULL;
        })->select('spareparts.*', DB::raw('price * qty as amount'));
        return Datatables::of($data)
          ->addIndexColumn()
          ->addColumn('action', function($row){
              $actionBtn = '
              <a href="spareparts/'.$row->id.'/edit" class="edit btn btn-warning btn-sm">Edit</a>
              <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
              return $actionBtn;
          })->editColumn('image', function(Sparepart $data){
              return !empty($data->photo) ? asset("/images/thumbnail/$data->photo") : asset('media/bg/no-content.svg');
          })->make(true);
      }
      return view('backend.mastersparepart.spareparts.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $config['page_title'] = "Create Spare Part";
      $page_breadcrumbs = [
        ['page' => '/backend/spareparts','title' => "List Spare Part"],
        ['page' => '#','title' => "Create Spare Part"],
      ];
      return view('backend.mastersparepart.spareparts.create', compact('config', 'page_breadcrumbs'));
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
        'supplier_sparepart_id' => 'required|integer',
        'brand_id'      => 'required|integer',
        'name'          => 'required|string',
        'qty'           => 'string|nullable',
        'price'         => 'string|nullable',
        'categories'    => 'required|array',
        'categories'    => 'required|distinct',
        'photo'         => 'image|mimes:jpg,png,jpeg|max:2048',
      ]);

      if($validator->passes()){
        $dimensions = [array('200', '200', 'thumbnail')];
        $image = isset($request->photo) && !empty($request->photo) ? Fileupload::uploadImagePublic('photo', $dimensions, 'public') : NULL;
        $data = Sparepart::create([
          'supplier_sparepart_id' => $request->input('supplier_sparepart_id'),
          'brand_id'              => $request->input('brand_id'),
          'name'                  => $request->input('name'),
          'qty'                   => $request->input('qty'),
          'price'                 => $request->input('price'),
          'photo'                 => $image,
        ]);
        $categories = Category::find($request->input('categories'));
        $data->categories()->attach($categories);

        $response = response()->json([
          'status'    => 'success',
          'message'   => 'Data has been saved',
          'redirect'  => '/backend/spareparts'
        ]);

      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sparepart  $sparepart
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
      $config['page_title'] = "Edit Kendaraan";
      $page_breadcrumbs = [
        ['page' => '/backend/spareparts','title' => "List Kendaraan"],
        ['page' => '#','title' => "Edit Kendaraan"],
      ];

      $data = Sparepart::with(['supplier', 'brand', 'categories'])->findOrFail($id);

      return view('backend.mastersparepart.spareparts.edit',compact('config', 'page_breadcrumbs', 'data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sparepart  $sparepart
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sparepart $sparepart)
    {
      $validator = Validator::make($request->all(), [
        'supplier_sparepart_id' => 'required|integer',
        'brand_id'      => 'required|integer',
        'name'          => 'required|string',
        'qty'           => 'string|nullable',
        'price'         => 'string|nullable',
        'categories'    => 'required|array',
        'categories'    => 'required|distinct',
        'photo'         => 'image|mimes:jpg,png,jpeg|max:2048',
      ]);

      if($validator->passes()){
        $dimensions = [array('200', '200', 'thumbnail')];
        $image = isset($request->photo) && !empty($request->photo) ? Fileupload::uploadImagePublic('photo', $dimensions, 'public', $sparepart->photo) : $sparepart->photo;
        $sparepart->update([
          'supplier_sparepart_id' => $request->input('supplier_sparepart_id'),
          'brand_id'              => $request->input('brand_id'),
          'name'                  => $request->input('name'),
          'qty'                   => $request->input('qty'),
          'price'                 => $request->input('price'),
          'photo'                 => $image,
        ]);
        $categories = Category::find($request->input('categories'));
        $sparepart->categories()->sync($categories);

        $response = response()->json([
          'status'    => 'success',
          'message'   => 'Data has been updated',
          'redirect'  => '/backend/spareparts'
        ]);
      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }

      return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Transport  $transport
     * @return \Illuminate\Http\Response
     */
    public function destroy(Sparepart $sparepart)
    {
      $response = response()->json([
        'status' => 'error',
        'message' => 'Data cannot be deleted',
      ]);
      File::delete(["images/original/$sparepart->photo", "images/thumbnail/$sparepart->photo"]);
      if($sparepart->delete()){
        $sparepart->categories()->detach();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been deleted',
        ]);
      }
      return $response;
    }
}
