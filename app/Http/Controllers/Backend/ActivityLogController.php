<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:activitylog-list|activitylog-create|activitylog-edit|activitylog-delete', ['only' => ['index']]);
  }
  public function index(Request $request)
  {
    $config['page_title'] = "Activity Log";
    $config['page_description'] = "Activity Log";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Activity Log"],
    ];
    if ($request->ajax()) {
      $data = Activity::with('user');
      return DataTables::of($data)
        ->addColumn('action', function ($row) {
          $actionBtn = '
        <a href="#" data-toggle="modal" data-target="#modalShow" data-id="' . $row->id . '" class="edit btn btn-primary btn-sm">Lihat Perubahan</a>';
          return $actionBtn;
        })
        ->editColumn('created_at', function ($row){
          return $row['created_at'];
        })
        ->make(true);
    }
    return view('backend.activitylog', compact('config', 'page_breadcrumbs'));
  }

  public function show($id)
  {
    $data = Activity::select('properties')->findOrFail($id);
    return $data->properties;
  }
}
