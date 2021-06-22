<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NeracaBalanceController extends Controller
{
  public function index()
  {
    $data = DB::table('journals')
      ->select(DB::raw('`coas`.name,IF(`coas`.`normal_balance` = "Db",
      (SUM(`journals`.`debit`) - SUM(`journals`.`kredit`)),
      (SUM(`journals`.`kredit`) - SUM(`journals`.`debit`))) AS `saldo`'))
      ->leftJoin('coas', 'coas.id', '=', 'journals.coa_id')
      ->get();
    dd($data->toArray());
  }

  public function show($id){

  }
}
