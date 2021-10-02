<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\JobOrder;
use App\Models\Journal;
use App\Models\OperationalExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
      try {
        DB::beginTransaction();

        OperationalExpense::create([
          'job_order_id' => $request['job_order_id'],
          'expense_id' => $request['expense_id'],
          'amount' => $request['amount'],
          'description' => $request['description'],
          'created_by' => Auth::id(),
          'type' => 'operational',
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

  public function destroy($id)
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
