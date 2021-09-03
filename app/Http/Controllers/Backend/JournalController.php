<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Models\ConfigCoa;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class JournalController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:journals-list|journals-create|journals-edit|journals-delete', ['only' => ['index']]);
    $this->middleware('permission:journals-create', ['only' => ['create', 'store']]);
    $this->middleware('permission:journals-edit', ['only' => ['edit', 'update']]);
    $this->middleware('permission:journals-delete', ['only' => ['destroy']]);
  }

  public function index(Request $request)
  {
    $config['page_title'] = "Jurnal Transaksi";
    $config['page_description'] = "Jurnal Transaksi";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Jurnal Transaksi"],
    ];
    if ($request->ajax()) {
      $date = $request->date;
      $data = Journal::with('coa')
        ->when($date, function ($query, $date) {
          $date_format = explode(" / ", $date);
          $date_begin = $date_format[0];
          $date_end = $date_format[1];
          return $query->whereBetween('date_journal', [$date_begin, $date_end]);
        });

      return DataTables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function ($row) {
          $editJournal = $row->can_delete == 1 ? '<a href="journals/' . $row->code_ref . '/edit" class="dropdown-item">Edit</a>' : NULL;
          $deleteJournal = $row->can_delete == 1 ? '<a href="#" data-toggle="modal" data-target="#modalDelete" data-id="' . $row->code_ref . '" class="delete dropdown-item">Delete</a>' : NULL;
          if ($editJournal == NULL && $deleteJournal == NULL) {
            $actionBtn = NULL;
          } else {
            $actionBtn = '
          <div class="dropdown">
            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-eye"></i>
            </button>
             <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                ' . $editJournal . $deleteJournal . '
             </div>
          </div>
           ';
          }
          return $actionBtn;
        })->make(true);

    }
    return view('backend.masterfinance.journal.index', compact('config', 'page_breadcrumbs'));
  }

  public function create()
  {
    $config['page_title'] = "Create Jurnal Transaksi";
    $config['page_description'] = "Create Jurnal Transaksi";
    $page_breadcrumbs = [
      ['page' => '/backend/journals', 'title' => "Jurnal Transaksi"],
      ['page' => '#', 'title' => "Create Jurnal Transaksi"],
    ];

    return view('backend.masterfinance.journal.create', compact('config', 'page_breadcrumbs'));
  }

  public function store(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'date_journal' => 'required|date_format:Y-m-d',
      'items.coa_id' => 'required|array',
      'items.coa_id.*' => 'required|integer',
      'items.description' => 'required|array',
      'items.description.*' => 'string',
      'items.debit' => 'required|array',
      'items.debit.*' => 'integer|nullable',
      'items.kredit' => 'array',
      'items.kredit.*' => 'integer|nullable',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        $totalDebit = 0;
        $totalKredit = 0;
        $items = $request->items;

        foreach ($items['coa_id'] as $key => $item):
          $totalDebit += $items['debit'][$key];
          $totalKredit += $items['kredit'][$key];
        endforeach;

        if ($totalDebit !== $totalKredit || $totalDebit <= 0 || $totalKredit <= 0) {
          return response()->json([
            'status' => 'error',
            'message' => 'Total Saldo Tidak Balance',
          ]);
        }
        $max_ref = Journal::where('table_ref', 'self')->max('code_ref');
        $max_ref += 1;

        foreach ($items['coa_id'] as $key => $item):
          Journal::create([
            'coa_id' => $items['coa_id'][$key],
            'date_journal' => $request->date_journal,
            'debit' => $items['debit'][$key] ?? 0,
            'kredit' => $items['kredit'][$key] ?? 0,
            'table_ref' => 'self',
            'code_ref' => $max_ref,
            'description' => $items['description'][$key],
            'can_delete' => '1',
          ]);
        endforeach;

        DB::commit();

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/journals',
        ]);
      } catch (\Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function edit($id)
  {
    $config['page_title'] = "Edit Jurnal Transaksi";
    $config['page_description'] = "Edit Jurnal Transaksi";
    $page_breadcrumbs = [
      ['page' => '/backend/journals', 'title' => "Jurnal Transaksi"],
      ['page' => '#', 'title' => "Edit Jurnal Transaksi"],
    ];

    $data = Journal::with('coa:id,code,name')->where('table_ref', 'self')->where('code_ref', $id)->get();

    return view('backend.masterfinance.journal.edit', compact('config', 'page_breadcrumbs', 'data'));
  }

  public function update(Request $request, $id)
  {
    $validator = Validator::make($request->all(), [
      'date_journal' => 'required|date_format:Y-m-d',
      'items.coa_id' => 'required|array',
      'items.coa_id.*' => 'required|integer',
      'items.description' => 'required|array',
      'items.description.*' => 'string',
      'items.debit' => 'required|array',
      'items.debit.*' => 'integer|nullable',
      'items.kredit' => 'array',
      'items.kredit.*' => 'integer|nullable',
    ]);

    if ($validator->passes()) {
      try {
        DB::beginTransaction();
        Journal::where('table_ref', 'self')->where('code_ref', $id)->delete();
        $totalDebit = 0;
        $totalKredit = 0;
        $items = $request->items;

        foreach ($items['coa_id'] as $key => $item):
          $totalDebit += $items['debit'][$key];
          $totalKredit += $items['kredit'][$key];
        endforeach;

        if ($totalDebit !== $totalKredit || $totalDebit <= 0 || $totalKredit <= 0) {
          return response()->json([
            'status' => 'error',
            'message' => 'Total Saldo Tidak Balance',
          ]);
        }

        foreach ($items['coa_id'] as $key => $item):
          Journal::create([
            'coa_id' => $items['coa_id'][$key],
            'date_journal' => $request->date_journal,
            'debit' => $items['debit'][$key] ?? 0,
            'kredit' => $items['kredit'][$key] ?? 0,
            'table_ref' => 'self',
            'code_ref' => $id,
            'description' => $items['description'][$key],
            'can_delete' => '1',
          ]);
        endforeach;

        DB::commit();

        $response = response()->json([
          'status' => 'success',
          'message' => 'Data has been saved',
          'redirect' => '/backend/journals',
        ]);
      } catch (\Throwable $throw) {
        DB::rollBack();
        $response = $throw;
      }
    } else {
      $response = response()->json(['error' => $validator->errors()->all()]);
    }
    return $response;
  }

  public function destroy($id)
  {
    $response = response()->json([
      'status' => 'error',
      'message' => 'Data cannot be deleted',
    ]);
    $data = Journal::where('table_ref', 'self')->where('code_ref', $id);
    if ($data->delete()) {
      $response = response()->json([
        'status' => 'success',
        'message' => 'Data has been deleted',
      ]);
    }
    return $response;
  }

  public function select2(Request $request)
  {
    $userRole = Auth::user()->roles[0]->name;
    $plucked = NULL;
     if ($userRole == 'operasional') {
      $configCoa = ConfigCoa::with('coa')->where('type', 'ledger')->where('name_page', 'ledgeroperational')->first();
      $plucked = $configCoa->coa->pluck('id') ?? array();
    } else if ($userRole == 'akunting') {
      $configCoa = ConfigCoa::with('coa')->where('type', 'ledger')->where('name_page', 'ledgeraccounting')->first();
      $plucked = $configCoa->coa->pluck('id') ?? array();
    } else if ($userRole == 'sparepart') {
      $configCoa = ConfigCoa::with('coa')->where('type', 'ledger')->where('name_page', 'ledgersparepart')->first();
      $plucked = $configCoa->coa->pluck('id') ?? array();
    }
    $page = $request->page;
    $resultCount = 25;
    $offset = ($page - 1) * $resultCount;
    $data = Coa::where('name', 'LIKE', '%' . $request->q . '%')
      ->whereNotNull('parent_id')
      ->when($plucked, function ($query) use($plucked) {
        return $query->whereIn('id', $plucked);
      })
      ->orderBy('code')
      ->skip($offset)
      ->take($resultCount)
      ->selectRaw('id, CONCAT(`code`, " - ", `name`) as text')
      ->get();

    $count = Coa::where('name', 'LIKE', '%' . $request->q . '%')
      ->whereNotNull('parent_id')
      ->when($plucked, function ($query) use($plucked) {
        return $query->whereIn('id', $plucked);
      })
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
