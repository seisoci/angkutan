<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;

class RolesController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:roles-list|roles-create|roles-edit|roles-delete', ['only' => ['index']]);
    $this->middleware('permission:roles-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:roles-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:roles-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "List Roles";
    $config['page_description'] = "Manage roles and permission every pages";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Roles"],
    ];
    if ($request->ajax()) {
      $data = Role::where('name', '!=', 'super-admin');
      return DataTables::eloquent($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '<a href="roles/' . $row->id . '/edit" class="edit btn btn-success btn-sm">Edit</a>';
          return $actionBtn;
        })->make(true);

    }
    return view('backend.roles.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config = array(
      "page_title" => "Create Roles",
      "title" => "Create Roles",
    );
    $data = Permission::get();
    return view('backend.roles.create', compact('config', 'data'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name' => 'required|string|unique:roles,name',
      'permission' => 'required|array',
    ]);

    if ($validator->passes()) {
      $role = Role::create(['name' => $request->input('name')]);
      $role->syncPermissions($request->input('permission'));
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => '/backend/roles'
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Roles";
    $page_breadcrumbs = [
      ['page' => '/backend/roles', 'title' => "List Roles"],
      ['page' => '#', 'title' => "Edit Roles"],
    ];
    $data = Role::find($id);
    $permission = Permission::all();
    $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
      ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
      ->all();

//    $rolePermissions = DB::table("role_has_permissions")
//      ->select(DB::raw('SUBSTRING_INDEX(`permissions`.`name`, "-", 1) as name'))
//      ->where("role_has_permissions.role_id", $id)
//      ->leftJoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id')
//      ->groupBy('permissions.title')
//      ->pluck('name')
//      ->all();
//    dd($rolePermissions);
    $group = $permission->mapToGroups(function ($item, $key) {
      $split = explode("-", $item['name']);
      return [$item['title'] => ['id' => $item->id, 'name' => $split[1]]];
    });

    $listPermission = $group->all();
    return view('backend.roles.edit', compact('config', 'page_breadcrumbs', 'data', 'listPermission', 'rolePermissions'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'permission' => 'array',
    ]);

    if ($validator->passes()) {
      $data = Role::find($id);
      $data->save();
      $data->syncPermissions($request->input('permission'));
      if ($data->save()) {
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/roles'
        ]);
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function destroy($id)
  {
    $data = Role::find($id);
    if ($data->delete()) {
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
        'redirect' => '/backend/roles'
      ]);
    }
    return $response;
  }
}
