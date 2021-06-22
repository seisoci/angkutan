<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportNeracaBalanceController extends Controller
{
  public function index()
  {
    $config['page_title'] = "laporan Neraca";
    $config['page_description'] = "Laporan Neraca";
    $page_breadcrumbs = [
      ['page' => '#', 'title' => "Laporan Neraca"],
    ];

    $data = DB::table('coas')
      ->select(DB::raw('
      CONCAT(`coas`.`code`, " - ", `coas`.`name`) AS `name`,
       `coas`.`normal_balance`,
       IF(`coas`.`normal_balance` = "Db",
          IF((SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)) > 0,
             (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
             0),
          IF((SUM(`journals`.`kredit`) - SUM(`journals`.`debit`)) < 0,
             (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`)),
             0))                                   as       `debit`,
       IF(`coas`.`normal_balance` = "Kr",
          IF((SUM(`journals`.`kredit`) - SUM(`journals`.`debit`)) > 0,
             (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`)),
             0),
          IF((SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)) < 0,
             (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
             0))                                   as `kredit`
      '))
      ->leftJoin('journals', 'journals.coa_id', '=', 'coas.id')
      ->where('coas.normal_balance', '!=', '')
      ->groupBy('coas.id')
      ->orderBy('coas.code', 'asc')
      ->get();
    return view('backend.report.reportneraca.index', compact('config', 'page_breadcrumbs', 'data'));

  }

  public function show($id)
  {

  }
}
