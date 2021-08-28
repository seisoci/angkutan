<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {

    $permissions = [
      'user-list',
      'user-create',
      'user-edit',
      'user-delete',
      'role-list',
      'role-create',
      'role-edit',
      'role-delete',
    ];

    $appends = [
      'list',
      'create',
      'edit',
      'delete'
    ];

    $listData = [
      ['name' => 'users', 'title' => 'Users'],
      ['name' => 'activitylog', 'title' => 'Activity Log'],
      ['name' => 'roles', 'title' => 'Roles'],
      ['name' => 'costumer', 'title' => 'Master Pelanggan'],
      ['name' => 'driver', 'title' => 'Master Supir'],
      ['name' => 'transports', 'title' => 'Master Kendaraan'],
      ['name' => 'expenses', 'title' => 'Master Biaya'],
      ['name' => 'routes', 'title' => 'Master Rute'],
      ['name' => 'cargos', 'title' => 'Master Muatan'],
      ['name' => 'typecapacities', 'title' => 'Master Kapasitas'],
      ['name' => 'roadmonies', 'title' => 'Master Uang Jalan'],
      ['name' => 'anotherexpedition', 'title' => 'Master LDO'],
      ['name' => 'supplierspareparts', 'title' => 'Master Suplier Spare Part'],
      ['name' => 'brands', 'title' => 'Master Brand'],
      ['name' => 'categories', 'title' => 'Master Kategori'],
      ['name' => 'spareparts', 'title' => 'Master Sparepart'],
      ['name' => 'mastercoa', 'title' => 'Master Akun'],
      ['name' => 'journals', 'title' => 'Jurnal Transaksi'],
      ['name' => 'kasbonemployees', 'title' => 'Kasbon Karyawan'],
      ['name' => 'invoicekasbonemployees', 'title' => 'Invoice Kasbon Karyawan'],
      ['name' => 'employees', 'title' => 'Master Karyawan'],
      ['name' => 'employeesmaster', 'title' => 'Master Jenis Gaji'],
      ['name' => 'employeessalary', 'title' => 'Master Gaji Bulanan'],
      ['name' => 'monthlymaster', 'title' => 'Gaji Bulanan'],
      ['name' => 'joborders', 'title' => 'Job Order'],
      ['name' => 'salaries', 'title' => 'Status Gaji Supir'],
      ['name' => 'paymentldo', 'title' => 'Status Pembayaran LDO'],
      ['name' => 'invoicepurchases', 'title' => 'Pembelian Barang'],
      ['name' => 'invoicereturpurchases', 'title' => 'Retur Pembelian'],
      ['name' => 'invoiceusageitems', 'title' => 'Pemakaian Barang'],
      ['name' => 'invoiceusageitemsoutside', 'title' => 'Pemakaian Barang Diluar'],
      ['name' => 'stocks', 'title' => 'Stok Barang'],
      ['name' => 'opnames', 'title' => 'Stok Opname'],
      ['name' => 'kasbon', 'title' => 'Kasbon Supir'],
      ['name' => 'invoicekasbons', 'title' => 'Invoice Kasbon Supir'],
      ['name' => 'invoicesalaries', 'title' => 'Invoice Gaji Supir'],
      ['name' => 'invoicecustomers', 'title' => 'Invoice Pelanggan'],
      ['name' => 'invoiceldo', 'title' => 'Invoice LDO'],
      ['name' => 'ledger', 'title' => 'Laporan Buku Besar'],
      ['name' => 'finance', 'title' => 'Laporan Keuangan'],
      ['name' => 'profitloss', 'title' => 'Laporan Laba Rugi'],
      ['name' => 'recapitulation', 'title' => 'Laporan Rekapitulasi'],
      ['name' => 'reportrecapsalaries', 'title' => 'Laporan Rekap Gaji Supir'],
      ['name' => 'reportrecapjoborders', 'title' => 'Laporan Rekap Tagihan Job Order'],
      ['name' => 'reportrecappurchaseorders', 'title' => 'Laporan Rekap Purchase Order'],
      ['name' => 'reportrecapreturpurchases', 'title' => 'Laporan Rekap Retur Purchase Order'],
      ['name' => 'reportrecapusageitems', 'title' => 'Laporan Rekap Pemakaian Barang'],
      ['name' => 'reportrecapusageitemoutside', 'title' => 'Laporan Rekap Pembelian Barang Diluar'],
      ['name' => 'reportcustomers', 'title' => 'Laporan Data Pelanggan'],
      ['name' => 'reportcustomerroadmoney', 'title' => 'Laporan Data Uang Jalan Pelanggan'],
      ['name' => 'reportdrivers', 'title' => 'Laporan Data Supir'],
      ['name' => 'reporttransports', 'title' => 'Laporan Data Kendaraan'],
      ['name' => 'reportjoborders', 'title' => 'Laporan Tagihan Job Order'],
      ['name' => 'reportinvoicecustomers', 'title' => 'Laporan Invoice Customer & Fee'],
      ['name' => 'reportinvoiceldo', 'title' => 'Laporan Invoice LDO'],
      ['name' => 'reportldonetprofit', 'title' => 'Laporan Rekap Laba LDO'],
      ['name' => 'reportkasbondrivers', 'title' => 'Laporan Hutang Supir'],
      ['name' => 'reportsalarydrivers', 'title' => 'Laporan Gaji Supir'],
      ['name' => 'reportkasbonemployees', 'title' => 'Laporan Hutang Karyawan'],
      ['name' => 'reportsalaryemployees', 'title' => 'Laporan Gaji Karyawan'],
      ['name' => 'reportpurchaseorders', 'title' => 'Laporan Purchase Order'],
      ['name' => 'reportreturpurchases', 'title' => 'Laporan Retur Purchase Order'],
      ['name' => 'reportusageitems', 'title' => 'Laporan Pemakaian Barang'],
      ['name' => 'reportusageitemoutside', 'title' => 'Laporan Pembelian Barang Diluar'],
      ['name' => 'reportstocks', 'title' => 'Laporan Stok Barang'],
      ['name' => 'settings', 'title' => 'Settings'],
      ['name' => 'prefixes', 'title' => 'List Prefix'],
      ['name' => 'configcoa', 'title' => 'Config COA']
    ];

    foreach ($listData as $item):
      foreach ($appends as $append):
        Permission::create([
          'name' => $item['name'] . '-' . $append,
          'title' => $item['title'],
          'guard_name' => 'web'
        ]);
      endforeach;
    endforeach;

    $master_account = [
      'cash-list',
      'cash-create',
      'cash-edit',
      'cash-delete',
      'bank-list',
      'bank-create',
      'bank-edit',
      'bank-delete',
      'company-list',
      'company-create',
      'company-edit',
      'company-delete',
    ];

//    foreach ($permissions as $permission) {
//      Permission::create(['name' => $permission]);
//    }


  }
}
