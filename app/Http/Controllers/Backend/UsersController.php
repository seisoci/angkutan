<?php

namespace App\Http\Controllers\Backend;

use App\Facades\Fileupload;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Role;
use Validator;

class UsersController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index']]);
    $this->middleware('permission:user-create', ['only' => ['create','store']]);
    $this->middleware('permission:user-edit', ['only' => ['edit','update']]);
    $this->middleware('permission:user-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title']       = "Users";
    $config['page_description'] = "Manage Users list";
    $page_breadcrumbs = [
      ['page' => '#','title' => "List Users"],
    ];

    if ($request->ajax()) {
      $data = User::query();
      return Datatables::eloquent($data)
        ->addIndexColumn()
        ->addColumn('action', function($row){
            $actionBtn = '<a href="users/'.$row->id.'/edit" class="edit btn btn-success btn-sm">Edit</a>
            <a href="#" data-toggle="modal" data-target="#modalReset" data-id="'. $row->id.'" class="btn btn-info btn-sm">Reset Password</a>
            <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
            return $actionBtn;
        })->editColumn('image', function(User $user){
            return !empty($user->image) ? asset("storage/images/thumbnail/$user->image") : asset('media/users/blank.png');
        })->make(true);
    }
    return view('backend.users.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Create Users";
    $page_breadcrumbs = [
      ['page' => '/backend/users','title' => "List Users"],
      ['page' => '#','title' => "Create User"],
    ];

    $data = array(
      'roles' => Role::select('name')->where('name', '!=', 'super-admin')->get()
    );
    return view('backend.users.create', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'name'      => 'required',
      'email'     => 'required|email',
      'active'    => 'required|between:0,1',
      'password'  => 'required|confirmed',
      'role'      => 'required|string',
      'profile_avatar'         => 'image|mimes:jpg,png,jpeg|max:2048',
      'profile_avatar_remove'  => 'between:0,1,NULL'
    ]);

    if($validator->passes()){
      $image = NULL;
      $dimensions = [array('300', '300', 'thumbnail')];
      DB::beginTransaction();
      try {
        if(isset($request->profile_avatar) && !empty($request->profile_avatar)){
          $image = Fileupload::uploadImagePublic('profile_avatar', $dimensions, 'public');
        }
        $user = User::create([
          'name'      => $request->input('name'),
          'password'  => Hash::make($request->password),
          'email'     => $request->input('email'),
          'active'    => $request->input('active'),
          'image'     => $image,
        ]);

        $user->assignRole($request->role);
        DB::commit();
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/users'
        ]);
      } catch (\Exception $e) {
        DB::rollback();
        File::delete(["original/$image", "thumbnail/$image"]);
        $response = response()->json([
          'status' => 'error',
          'error' => ['Email Already Exists']
        ]);

      }
    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Users";
    $page_breadcrumbs = [
      ['page' => '/backend/users','title' => "List Users"],
      ['page' => '#','title' => "Edit User"],
    ];
    $user = User::findOrFail($id);
    $userRole = $user->roles->first();
    $roles = Role::query()->select('name');
    $roles->when($userRole->name == 'super-admin', function($q){
      return $q->where('name', '=', 'super-admin')->pluck('name','name');
    });
    $roles->when($userRole->name != 'super-admin', function($q){
      return $q->where('name', '!=', 'super-admin')->pluck('name','name');
    });
    $data = array(
      'user' => $user,
      'roles' => $roles->get(),
      'userRole' => $userRole
    );

    return view('backend.users.edit',compact('config', 'page_breadcrumbs', 'data'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'name'      => 'required',
      'email'     => 'required|email',
      'active'    => 'required|between:0,1',
      'role'      => 'required|string',
      'profile_avatar'         => 'image|mimes:jpg,png,jpeg|max:2048',
      'profile_avatar_remove'  => 'between:0,1,NULL'
    ]);

    $data = User::findOrFail($id);
    $userRole = $data->roles->first();
    if($validator->passes()){
      $image = NULL;
      $dimensions = [array('300', '300', 'thumbnail')];
      DB::beginTransaction();
      try {
        if(isset($request->profile_avatar) && !empty($request->profile_avatar)){
          $image = Fileupload::uploadImagePublic('profile_avatar', $dimensions, 'public');
          File::delete(["images/original/$data->image", "images/thumbnail/$data->image"]);
        }
        $data->update([
          'name'      => $request->input('name'),
          'email'     => $request->input('email'),
          'active'    => $request->input('active'),
          'image'     => $image,
        ]);
        $data->removeRole($userRole->name);
        $data->assignRole($request->role);
        DB::commit();
        $response = response()->json([
            'status'   => 'success',
            'message'  => 'Data has been saved',
            'redirect' => '/backend/users'
        ]);
      } catch (\Exception $e) {
        DB::rollback();
        File::delete(["images/original/$image", "images/thumbnail/$image"]);
        $response = response()->json([
          'status' => 'error',
          'error' => ['Email Already Exists']
        ]);

      }
    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }

  public function destroy($id)
  {
    $data = User::find($id);
    File::delete(["images/original/$data->image", "images/thumbnail/$data->image"]);
    if($data->delete()){
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been deleted',
        ]);
    }
    return $response;
  }

  public function resetpassword(Request $request){
    $validator = Validator::make($request->all(), [
      'id'        => 'required|integer',
    ]);

    if($validator->passes()){
      $data = User::find($request->id);
      $data->password = Hash::make($data->email);
      if($data->save()){
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved'
        ]);
      }
    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }

  public function changepassword(Request $request, $id){
    $validator = Validator::make($request->all(), [
      'old_password' => ['required', new MatchOldPassword($id)],
      'password'     => 'required|between:6,255|confirmed',
    ]);


    if($validator->passes()){
      $data = User::find($id);
      $data->password = Hash::make($request->password);
      if($data->save()){
          $response = response()->json([
            'status' => 'success',
            'message' => 'Data has been saved'
          ]);
      }
    }else{
      $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }
}
