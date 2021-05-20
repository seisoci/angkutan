<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use DataTables;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
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