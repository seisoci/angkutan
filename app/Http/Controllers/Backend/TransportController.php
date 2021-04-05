<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Fileupload;
use App\Http\Controllers\Controller;
use App\Models\Transport;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class TransportController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "Kendaraan";
      $config['page_description'] = "Daftar List Kendaraan";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Kendaraan"],
      ];

      if ($request->ajax()) {
        $data = Transport::query();
        return Datatables::of($data)
          ->addIndexColumn()
          ->addColumn('action', function($row){
              $actionBtn = '
              <a href="#" data-toggle="modal" data-target="#modalShow" data-num_pol="'.$row->num_pol.'" data-merk="'.$row->merk.'" data-type="'.$row->type.'" data-type_car="'.$row->type_car.'" data-dump="'.$row->dump.'" data-year="'.$row->year.'" data-max_weight="'.$row->max_weight.'" data-expired_stnk="'.$row->expired_stnk.'" data-description="'.$row->description.'" data-photo="'.$row->photo.'"  class="btn btn-info btn-sm">Show Detail</a>
              <a href="transports/'.$row->id.'/edit" class="edit btn btn-warning btn-sm">Edit</a>
              <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
              return $actionBtn;
          })->editColumn('image', function(Transport $data){
              return !empty($data->photo) ? asset("/images/thumbnail/$data->photo") : asset('media/bg/no-content.svg');
          })->make(true);
      }
      return view('backend.masteroperational.transports.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $config['page_title'] = "Create Kendaraan";
      $page_breadcrumbs = [
        ['page' => '/backend/transports','title' => "List Kendaraan"],
        ['page' => '#','title' => "Create Kendaraan"],
      ];
      return view('backend.masteroperational.transports.create', compact('config', 'page_breadcrumbs'));
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
        'num_pol'       => 'required|string',
        'merk'          => 'string|nullable',
        'type'          => 'string|nullable',
        'type_car'      => 'string|nullable',
        'dump'          => 'required|in:ya,tidak',
        'year'          => 'date_format:Y|nullable',
        'max_weight'    => 'integer|nullable',
        'expired_stnk'  => 'date_format:Y-m-d|nullable',
        'description'   => 'string|nullable',
        'photo'         => 'image|mimes:jpg,png,jpeg|max:2048',
      ]);

      if($validator->passes()){
        $dimensions = [array('1280', '720', 'thumbnail')];
        $image = isset($request->photo) && !empty($request->photo) ? Fileupload::uploadImagePublic('photo', $dimensions, 'public') : NULL;
        Transport::create([
          'num_pol'       => $request->input('num_pol'),
          'merk'          => $request->input('merk'),
          'type'          => $request->input('type'),
          'type_car'      => $request->input('type_car'),
          'dump'          => $request->input('dump'),
          'year'          => $request->input('year'),
          'max_weight'    => $request->input('max_weight'),
          'expired_stnk'  => $request->input('expired_stnk'),
          'description'   => $request->input('description'),
          'photo'         => $image,
        ]);

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/transports'
        ]);

      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Transport  $transport
     * @return \Illuminate\Http\Response
     */
    public function edit(Transport $transport)
    {
      $config['page_title'] = "Edit Kendaraan";
      $page_breadcrumbs = [
        ['page' => '/backend/transports','title' => "List Kendaraan"],
        ['page' => '#','title' => "Edit Kendaraan"],
      ];

      $data = $transport;

      return view('backend.masteroperational.transports.edit',compact('config', 'page_breadcrumbs', 'data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transport  $transport
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Transport $transport)
    {
      $validator = Validator::make($request->all(), [
        'num_pol'       => 'required|string',
        'merk'          => 'string|nullable',
        'type'          => 'string|nullable',
        'type_car'      => 'string|nullable',
        'dump'          => 'required|in:ya,tidak',
        'year'          => 'date_format:Y|nullable',
        'max_weight'    => 'integer|nullable',
        'expired_stnk'  => 'date_format:Y-m-d|nullable',
        'description'   => 'string|nullable',
        'photo'         => 'image|mimes:jpg,png,jpeg|max:2048'
      ]);

      if($validator->passes()){
        $dimensions = [array('1280', '720', 'thumbnail')];
        $image = isset($request->photo) && !empty($request->photo) ? Fileupload::uploadImagePublic('photo', $dimensions, 'public', $transport->photo) : $transport->photo;
        $transport->update([
          'num_pol'       => $request->input('num_pol'),
          'merk'          => $request->input('merk'),
          'type'          => $request->input('type'),
          'type_car'      => $request->input('type_car'),
          'dump'          => $request->input('dump'),
          'year'          => $request->input('year'),
          'max_weight'    => $request->input('max_weight'),
          'expired_stnk'  => $request->input('expired_stnk'),
          'description'   => $request->input('description'),
          'photo'         => $image,
        ]);
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been updated',
          'redirect' => '/backend/transports'
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
    public function destroy(Transport $transport)
    {
      $response = response()->json([
        'status' => 'error',
        'message' => 'Data cannot be deleted',
      ]);
      File::delete(["images/original/$transport->photo", "images/thumbnail/$transport->photo"]);
      if($transport->delete()){
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been deleted',
        ]);
      }
      return $response;
    }
}
