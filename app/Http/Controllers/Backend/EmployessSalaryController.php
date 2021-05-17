<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeMaster;
use App\Models\InvoiceKasbon;
use App\Models\JobOrder;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Validator;

class EmployessSalaryController extends Controller
{
  public function index(Request $request)
  {
    $config['page_title'] = "Gaji Karyawaan";
    $config['page_description'] = "Manage Gaji Karyawaan list";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "List Gaji Karyawaan"],
    ];
    if ($request->ajax()) {
      $data = Employee::with('salaries');
      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $actionBtn = '
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-eye"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                  <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->id . '" class="delete dropdown-item">Show Detail</a>
                  <a href="employeessalary/' . $row->id . '/edit" class="edit dropdown-item">Edit Gaji</a>
                </div>
            </div>';
          return $actionBtn;
        })
        ->editColumn('photo', function (Employee $employee) {
          return !empty($employee->photo) ? asset("/images/thumbnail/$employee->photo") : asset('media/users/blank.png');
        })
        ->addColumn('details_url', function (Employee $employee) {
          return route('backend.employeessalary.datatabledetail', $employee->id);
        })->make(true);
    }
    return view('backend.accounting.employeessalary.index', compact('config', 'page_breadcrumbs'));
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Karyawaan";
    $page_breadcrumbs = [
      ['page' => '/backend/employees', 'title' => "List Karyawaan"],
      ['page' => '#', 'title' => "Edit User"],
    ];
    $data = Employee::findOrFail($id)->salaries()->get();
    return view('backend.accounting.employeessalary.edit', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'employee_master_id' => 'required|integer',
      'amount' => 'required|integer',
    ]);

    $data = Employee::findOrFail($id);
    if ($validator->passes()) {
      $pivot = $data->salaries();
      if ($pivot->where('employee_master_id', $request->employee_master_id)->count() >= 1) {
        $pivot->where('employee_master_id', $request->employee_master_id)->updateExistingPivot($request->employee_master_id, $request->except(['type_capacity_id', '_method']));
      } else {
        $pivot->attach($request->employee_master_id, ['amount' => $request->amount]);
      }
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been saved',
        'redirect' => '/backend/employees'
      ]);
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function destroy($id, $employee_master_id)
  {
    $data = Employee::findOrFail($id);
    $data->salaries()->detach($employee_master_id);
    if ($data) {
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    }
    return $response;
  }

  public function datatabledetail($id)
  {
    $data = Employee::findOrFail($id)->salaries();

    return Datatables::of($data)->make(true);
  }

  public function fetchdata($id)
  {
    $data = Employee::findOrFail($id)->salaries()->get();
    return response()->json([
      'data' => $data,
      'status' => 'success',
      'message' => 'Data has been deleted',
    ]);
  }
}
