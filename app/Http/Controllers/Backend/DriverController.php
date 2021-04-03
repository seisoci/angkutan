<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use DataTables;

class DriverController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "Supir";
      $config['page_description'] = "Daftar List Supir";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Supir"],
      ];

      if ($request->ajax()) {
        $data = Driver::query();
        return Datatables::eloquent($data)
          ->addIndexColumn()
          ->addColumn('action', function($row){
              $actionBtn = '<a href="users/'.$row->id.'/edit" class="edit btn btn-success btn-sm">Edit</a>
              <a href="#" data-toggle="modal" data-target="#modalReset" data-id="'. $row->id.'" class="btn btn-info btn-sm">Reset Password</a>
              <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
              return $actionBtn;
          })->editColumn('image', function(Driver $user){
              return !empty($user->image) ? asset("storage/images/thumbnail/$user->image") : asset('media/users/blank.png');
          })->make(true);
      }
      return view('backend.drivers.index', compact('config', 'page_breadcrumbs'));
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
        ['page' => '/backend/driver','title' => "List Supir"],
        ['page' => '#','title' => "Create Supir"],
      ];
      return view('backend.drivers.create', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function show(Driver $driver)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function edit(Driver $driver)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Driver $driver)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Driver  $driver
     * @return \Illuminate\Http\Response
     */
    public function destroy(Driver $driver)
    {
        //
    }
}
