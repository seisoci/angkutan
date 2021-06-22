<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Coa;
use App\Traits\CarbonTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Null_;

class LedgerController extends Controller
{
  use CarbonTrait;

  public function index(Request $request)
  {
    $config['page_title'] = "Laporan Buku Besar";
    $config['page_description'] = "Laporan Buku Besar";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Buku Besar"],
    ];

    $date_begin = $request->input('date_begin') ? $this->getStartOfMonthByMonthYear($request->input('date_begin')) : NULL;
    $date_end = $request->input('date_begin') ? $this->getEndOfMonthByMonthYear($request->input('date_begin')) : NULL;
    $restSaldo = DB::table('coas')
      ->select(DB::raw('
      `coas`.id, IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)), (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`
      '))
      ->leftJoin('journals', 'coas.id', '=', 'journals.coa_id')
      ->groupBy('coas.id')
      ->when($request->input('date_begin'), function ($query) use ($date_begin, $date_end) {
        return $query->where('journals.date_journal', '<', $date_begin);
      }, function ($query) {
        return $query->whereNull('journals.date_journal');
      })
      ->where('coas.type', 'harta')
      ->where('coas.normal_balance', 'Db')
      ->get();

    $data = Coa::with(['children.journal' => function ($query) use ($request, $date_begin, $date_end) {
      $query->when($request->input('date_begin'), function ($query) use ($date_begin, $date_end) {
        return $query->whereBetween('journals.date_journal', [$date_begin, $date_end]);
      }, function ($query) {
        return $query->whereNull('journals.date_journal');
      });
    }])
      ->whereNull('parent_id')
      ->orderBy('code', 'asc')
      ->get();

    foreach ($restSaldo as $rest):
      foreach ($data as $keyParent => $itemParent):
        foreach ($itemParent->children as $key => $itemChilderen):
          if ($rest->id == $itemChilderen->id) {
            $data[$keyParent]['children'][$key]['rest_balance'] = $rest->saldo;
            break;
          }
        endforeach;
      endforeach;
    endforeach;
    return view('backend.report.reportledger.index', compact('config', 'page_breadcrumbs', 'data'));
  }

}
