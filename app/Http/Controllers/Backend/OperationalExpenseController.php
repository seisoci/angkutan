<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;
use Validator;

class OperationalExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
  public function store(Request $request){
    $validator = Validator::make($request->all(), [
        'job_order_id'  => 'required|integer',
        'expense_id'    => 'required|integer',
        'amount'       => 'required|integer',
        'description'   => 'string|nullable',
    ]);
    if($validator->passes()){
      Expense::findOrFail($request->expense_id);
      OperationalExpense::create([
        'job_order_id' => $request->input('job_order_id'),
        'expense_id'   => $request->input('expense_id'),
        'description'  => $request->input('description'),
        'amount'      => $request->input('amount'),
      ]);

      $response = response()->json([
        'status'  => 'success',
        'message' => 'Data has been saved',
        'redirect' => 'reload',
      ]);
    }else{
        $response = response()->json(['error'=>$validator->errors()->all()]);
    }
    return $response;
  }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\OperationalExpense  $operationalExpense
     * @return \Illuminate\Http\Response
     */
    public function show(OperationalExpense $operationalExpense)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\OperationalExpense  $operationalExpense
     * @return \Illuminate\Http\Response
     */
    public function edit(OperationalExpense $operationalExpense)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\OperationalExpense  $operationalExpense
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, OperationalExpense $operationalExpense)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\OperationalExpense  $operationalExpense
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $response = response()->json([
          'status'  => 'error',
          'message' => 'Data cannot be deleted',
      ]);

      $data = OperationalExpense::find($id);
      if($data->delete()){
        $response = response()->json([
          'status'    => 'success',
          'message'   => 'Data has been deleted',
          'redirect'  => 'reload'
        ]);
      }
      return $response;
    }
}
