<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportFinanceController extends Controller
{
  function __construct()
  {
    $this->middleware('permission:finance-list|finance-create|finance-edit|finance-delete', ['only' => ['index']]);
  }

  public function index()
  {
    $config['page_title'] = "laporan Keuangan";
    $config['page_description'] = "Laporan Keuangan";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Keuangan"],
    ];
    $data = DB::table('coas')
      ->select(DB::raw('CONCAT(`coas`.`code`, " - ", `coas`.`name`) AS `name`,
          IFNULL(IF(`coas`.`normal_balance` = "Db", (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
          (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))), 0) AS `balance`
          '))
      ->leftJoin('journals', 'coas.id', '=', 'journals.coa_id')
      ->where('type', 'harta')
      ->where('normal_balance', 'Db')
      ->groupBy('coas.id')
      ->orderBy('coas.code', 'asc')
      ->get();

    return view('backend.report.reportfinance.index', compact('config', 'page_breadcrumbs', 'data'));
  }
}
