<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\InvoicePurchase;
use App\Models\Setting;
use Illuminate\Http\Request;
use DataTables;
use DB;
use PDF;
class InvoicePurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $config['page_title']       = "List Invoice Pembelian";
      $config['page_description'] = "Daftar List Invoice Pembelian";
      $page_breadcrumbs = [
        ['page' => '#','title' => "List Invoice Pembelian"],
      ];
      if ($request->ajax()) {
        $data = InvoicePurchase::query()
        ->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'));
        return Datatables::of($data)
        ->addIndexColumn()
        ->addColumn('action', function($row){
            $actionBtn = '
              <div class="dropdown">
                  <button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="fas fa-eye"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a href="invoicepurchases/'.$row->id.'" class="dropdown-item">Invoice Detail</a>
                    <a href="#" data-toggle="modal" data-target="#modalDelete" data-id="'. $row->id.'" class="delete dropdown-item">Delete</a>
                  </div>
              </div>
            ';
            return $actionBtn;
        })->make(true);

      }
      return view('backend.sparepart.invoicepurchases.index', compact('config', 'page_breadcrumbs'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\InvoicePurchase  $invoicePurchase
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
      $config['page_title'] = "Detail Supir";
      $page_breadcrumbs = [
        ['page' => '/backend/drivers','title' => "List Supir"],
        ['page' => '#','title' => "Detail Supir"],
      ];
      $collection = Setting::all();
      $profile = collect($collection)->mapWithKeys(function ($item) {
          return [$item['name'] => $item['value']];
      });
      $data = InvoicePurchase::where('id', $id)->select(DB::raw('*, CONCAT(prefix, "-", num_bill) AS prefix_invoice'))->with(['purchases', 'supplier'])->firstOrFail();
      return view('backend.sparepart.invoicepurchases.show',compact('config', 'page_breadcrumbs', 'data', 'profile'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\InvoicePurchase  $invoicePurchase
     * @return \Illuminate\Http\Response
     */
    public function destroy(InvoicePurchase $invoicePurchase)
    {
        //
    }

}
