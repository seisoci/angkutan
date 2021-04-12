<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Fileupload;
use Illuminate\Http\Request;
use Validator;

class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $config['page_title'] = "Settings";
      $page_breadcrumbs = [
        ['page' => '#','title' => "Settings"],
      ];
      $favicon = Setting::where('name', 'favicon_url')->first();
      $logo = Setting::where('name', 'logo_url')->first();
      $data = Setting::where('type', 'settings')->get();
      return view('backend.settings.index', compact('config', 'data', 'logo', 'favicon'));
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
        'logo'     => 'image|mimes:jpg,png,jpeg|max:2048',
        'favicon'  => 'image|mimes:jpg,png,jpeg|max:2048',
      ]);
      if($validator->passes()){
        $logo_dimensions     = [array('300', '300', 'thumbnail')];
        $favicon_dimensions  = [array('50', '50', 'thumbnail')];
        $favicon  = Setting::where('name', 'favicon_url')->first();
        $logo     = Setting::where('name', 'logo_url')->first();

        $logo_val     = isset($request->logo) && !empty($request->logo) ? Fileupload::uploadImagePublic('logo', $logo_dimensions, 'public', $request->logo, $request->logo) : NULL;
        if($logo_val != NULL){
          $logo->value = $logo_val;
          $logo->save();
        }

        $favicon_val = isset($request->favicon) && !empty($request->favicon) ? Fileupload::uploadImagePublic('favicon', $favicon_dimensions, 'public', $request->favicon) : NULL;
        if($favicon_val != NULL){
          $favicon->value = $favicon_val;
          $favicon->save();
        }

        foreach($request->id as $key => $id){
          $data          = Setting::find($id);
          $data->value   = $request->value[$key];
          if($data->save()){
            $response = response()->json([
                'status' => 'success',
                'message' => 'Data has been saved',
                'redirect'  => "/backend/settings"
            ]);
          }
        }
      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

}
