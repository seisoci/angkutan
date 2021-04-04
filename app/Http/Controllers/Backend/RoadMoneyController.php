<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Costumer;
use App\Models\RoadMoney;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Validator;

class RoadMoneyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "Uang Jalan";
      $config['page_description'] = "Daftar List Uang Jalan";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Uang Jalan"],
      ];

      if ($request->ajax()) {
        $data = RoadMoney::query();
        return Datatables::of($data)
          ->addIndexColumn()
          ->addColumn('action', function($row){
              $actionBtn = '
              <a href="roadmonies/'.$row->id.'/edit" class="edit btn btn-warning btn-sm">Edit</a>
              <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete btn btn-danger btn-sm">Delete</a>';
              return $actionBtn;
          })->make(true);
      }
      return view('backend.roadmonies.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
      $config['page_title'] = "Create Uang Jalan";
      $page_breadcrumbs = [
        ['page' => '/backend/roadmonies','title' => "List Uang Jalan"],
        ['page' => '#','title' => "Create Uang Jalan"],
      ];
      return view('backend.roadmonies.create', compact('config', 'page_breadcrumbs'));
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
        'costumer_id'   => 'required|integer',
        'route_from'    => 'string|nullable',
        'route_to'      => 'string|nullable',
        'cargo'         => 'string|nullable',
        'road_engkel'   => 'integer|nullable',
        'road_tronton'  => 'integer|nullable',
        'invoice'       => 'integer|nullable',
        'salary_engkel' => 'integer|nullable',
        'salary_tronton'=> 'integer|nullable',
        'amount'        => 'integer|nullable',
      ]);

      if($validator->passes()){
        RoadMoney::create([
          'costumer_id'   => $request->input('costumer_id'),
          'route_from'    => $request->input('route_from'),
          'route_to'      => $request->input('route_to'),
          'cargo'         => $request->input('cargo'),
          'road_engkel'   => $request->input('road_engkel'),
          'road_tronton'  => $request->input('road_tronton'),
          'invoice'       => $request->input('invoice'),
          'salary_engkel' => $request->input('salary_engkel'),
          'salary_tronton'=> $request->input('salary_tronton'),
          'amount'        => $request->input('amount')
        ]);

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/roadmonies'
        ]);

      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RoadMoney  $roadMoney
     * @return \Illuminate\Http\Response
     */
    public function edit(RoadMoney $roadMoney)
    {
      $config['page_title'] = "Edit Uang Jalan";
      $page_breadcrumbs = [
        ['page' => '/backend/roadmonies','title' => "List Uang Jalan"],
        ['page' => '#','title' => "Edit Uang Jalan"],
      ];

      $data = $roadMoney;

      return view('backend.roadmonies.edit',compact('config', 'page_breadcrumbs', 'data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RoadMoney  $roadMoney
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RoadMoney $roadMoney)
    {
      $validator = Validator::make($request->all(), [
        'costumer_id'   => 'required|integer',
        'route_from'    => 'string|nullable',
        'route_to'      => 'string|nullable',
        'cargo'         => 'string|nullable',
        'road_engkel'   => 'integer|nullable',
        'road_tronton'  => 'integer|nullable',
        'invoice'       => 'integer|nullable',
        'salary_engkel' => 'integer|nullable',
        'salary_tronton'=> 'integer|nullable',
        'amount'        => 'integer|nullable',
      ]);

      if($validator->passes()){
        $roadMoney->update([
          'costumer_id'   => $request->input('costumer_id'),
          'route_from'    => $request->input('route_from'),
          'route_to'      => $request->input('route_to'),
          'cargo'         => $request->input('cargo'),
          'road_engkel'   => $request->input('road_engkel'),
          'road_tronton'  => $request->input('road_tronton'),
          'invoice'       => $request->input('invoice'),
          'salary_engkel' => $request->input('salary_engkel'),
          'salary_tronton'=> $request->input('salary_tronton'),
          'amount'        => $request->input('amount')
        ]);

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/roadmonies'
        ]);

      }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
      }
      return $response;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RoadMoney  $roadMoney
     * @return \Illuminate\Http\Response
     */
    public function destroy(RoadMoney $roadMoney)
    {
      $response = response()->json([
          'status' => 'error',
          'message' => 'Data cannot be deleted',
      ]);
      if($roadMoney->delete()){
        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been deleted',
        ]);
      }
      return $response;
    }

    public function select2(Request $request){
      $page = $request->page;
      $resultCount = 5;
      $offset = ($page - 1) * $resultCount;
      $data = Costumer::where('name', 'LIKE', '%' . $request->q. '%')
          ->orderBy('name')
          ->skip($offset)
          ->take($resultCount)
          ->selectRaw('id, name as text')
          ->get();

      $count = Costumer::where('name', 'LIKE', '%' . $request->q. '%')
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
