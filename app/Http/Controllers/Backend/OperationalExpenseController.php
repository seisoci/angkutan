<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class OperationalExpenseController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:joborders-list|joborders-create|joborders-edit|joborders-delete', ['only' => ['index']]);
    $this->middleware('permission:joborders-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:joborders-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:joborders-delete', ['only' => ['destroy']]);
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'job_order_id' => 'required|integer',
      'expense_id' => 'required|integer',
      'amount' => 'required|integer',
      'description' => 'string|nullable',
    ]);
    if ($validator->passes()) {
      $data = JobOrder::with(['driver', 'routefrom', 'routeto', 'costumer'])->findOrFail($request->job_order_id);
      try {
        DB::beginTransaction();
        $operationalExpense = OperationalExpense::create([
          'job_order_id' => $request->input('job_order_id'),
          'expense_id' => $request->input('expense_id'),
          'description' => $request->input('description'),
          'amount' => $request->input('amount'),
        ]);

        Journal::create([
          'coa_id' => $request->input('coa_id'),
          'date_journal' => $data->date_begin,
          'debit' => 0,
          'kredit' => $request->input('amount'),
          'table_ref' => 'operationalexpense',
          'code_ref' => $operationalExpense->id,
          'description' => "Pengurangan saldo untuk uang jalan " . "$data->prefix-$data->num_bill " . $data->costumer->name . " dari " . $data->routefrom->name . " ke " . $data->routeto->name
        ]);

        Journal::create([
          'coa_id' => 50,
          'date_journal' => $data->date_begin,
          'debit' => $request->input('amount'),
          'kredit' => 0,
          'table_ref' => 'operationalexpense',
          'code_ref' => $operationalExpense->id,
          'description' => "Beban operasional tambahan " . "$data->prefix-$data->num_bill " . $data->costumer->name . " dari " . $data->routefrom->name . " ke " . $data->routeto->name
        ]);

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => 'reload',
        ]);
        DB::commit();
      } catch (\Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'job_order_id' => 'required|integer',
      'amount' => 'required|integer',
      'created_by' => 'integer',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
//        $data = JobOrder::with(['anotherexpedition:id,name', 'driver:id,name', 'costumer:id,cooperation_id,name','costumer.cooperation:id,nickname', 'cargo:id,name', 'transport:id,num_pol', 'routefrom:id,name', 'routeto:id,name', 'operationalexpense.expense'])->find($id);

        OperationalExpense::create([
          'job_order_id' => $id,
          'amount' => $request['amount'],
          'description' => $request['description'],
          'created_by' => $request['created_by'],
          'type' => 'roadmoney',
        ]);

        DB::commit();
        return response()->json([
          'status' => 'success',
          'message' => 'Data has been updated',
        ]);
      } catch
      (\Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function findbypk($id)
  {
    $data = JobOrder::withSum('operationalexpense', 'amount')->where('id', $id)->sole();
    $roadMoney = OperationalExpense::where([
      ['job_order_id', $id],
      ['type', 'roadmoney'],
      ['approved', '1']
    ])->sum('amount');
    $response = [
      'data' => $data,
      'roadMoney' => $roadMoney,
      'status' => 'success'
    ];
    return $response;
  }

  public
  function destroy($id)
  {
    try {
      DB::beginTransaction();
      $data = OperationalExpense::find($id);
      $journal = Journal::where('table_ref', 'operationalexpense')->where('code_ref', $id);
      $data->delete();
      $journal->delete();
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
        'redirect' => 'reload'
      ]);

      DB::commit();
    } catch (\Throwable $throw) {
      DB::rollBack();
      $response = $throw;
    }

    return $response;
  }
}
