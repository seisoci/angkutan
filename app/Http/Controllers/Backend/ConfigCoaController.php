<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use Illuminate\Http\Request;

class ConfigCoaController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:configcoa-list|configcoa-create|configcoa-edit|configcoa-delete', ['only' => ['index']]);
    $this->middleware('permission:configcoa-create', ['only' => ['create', 'store']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Config COA";
    $config['page_description'] = "Config COA";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Config COA"],
    ];
    $data = ConfigCoa::with('coa')->where('type', 'configcoa')->get();
    return view('backend.settings.configcoa.index', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function store(Request $request)
  {
    $coa = $request->coa ?? [];
    $idIn = [];
    foreach ($coa as $key => $item):
      $idIn[] = $key;
      $data = ConfigCoa::find($key);
      $itemCategories = Coa::find($coa[$key]);
      $data->coa()->sync($itemCategories);
    endforeach;
    $idNotIn = ConfigCoa::whereNotIn('id', $idIn)->where('type', 'configcoa')->pluck('id') ?? [];
    foreach ($idNotIn as $item):
      $data = ConfigCoa::find($item);
      $data->coa()->detach();
    endforeach;
    $response = response()->json([
      'status' => 'success',
      'message' => 'Data has been updated',
      'redirect' => 'reload'
    ]);
    return $response;
  }

}
