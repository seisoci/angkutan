<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backend as Backend;
use App\Http\Controllers\PagesController;
use App\Models\TypeCapacity;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
  return redirect('/backend');
});

Route::get('backend', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('backend', [LoginController::class, 'login']);
Route::get('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/quick-search', [PagesController::class, 'quicksearch'])->name('quick-search');
Route::prefix('backend')->name('backend.')->middleware(['auth:web'])->group(function () {
  Route::group(['middleware' => ['role:super-admin|admin|operasional|akunting|sparepart']], function () {
    Route::post('resetpassword', [Backend\UsersController::class, 'resetpassword'])->name('users.resetpassword');
    Route::post('changepassword', [Backend\UsersController::class, 'changepassword'])->name('users.changepassword');
    Route::resource('users', Backend\UsersController::class)->except('show');
    Route::resource('roles', Backend\RolesController::class)->except(['create', 'show', 'destroy']);

    //Select2
    Route::get('costumers/select2', [Backend\CostumerController::class, 'select2'])->name('costumers.select2');
    Route::get('cargos/select2', [Backend\CargoController::class, 'select2'])->name('cargos.select2');
    Route::get('routes/select2', [Backend\RouteController::class, 'select2'])->name('routes.select2');
    Route::get('brands/select2', [Backend\BrandController::class, 'select2'])->name('brands.select2');
    Route::get('drivers/select2', [Backend\DriverController::class, 'select2'])->name('drivers.select2');
    Route::get('drivers/select2self', [Backend\DriverController::class, 'select2self'])->name('drivers.select2self');
    Route::get('drivers/select2ldo', [Backend\DriverController::class, 'select2ldo'])->name('drivers.select2ldo');
    Route::get('drivers/select2joborder', [Backend\DriverController::class, 'select2joborder'])->name('drivers.select2joborder');
    Route::get('joborders/select2costumers', [Backend\JobOrderController::class, 'select2costumers'])->name('joborders.select2costumers');
    Route::get('joborders/select2routefrom', [Backend\JobOrderController::class, 'select2routefrom'])->name('joborders.select2routefrom');
    Route::get('joborders/select2routeto', [Backend\JobOrderController::class, 'select2routeto'])->name('joborders.select2routeto');
    Route::get('joborders/select2cargos', [Backend\JobOrderController::class, 'select2cargos'])->name('joborders.select2cargos');
    Route::get('anotherexpedition/select2', [Backend\AnotherExpeditionController::class, 'select2'])->name('anotherexpedition.select2');
    Route::get('transports/select2', [Backend\TransportController::class, 'select2'])->name('transports.select2');
    Route::get('transports/select2tonase', [Backend\TransportController::class, 'select2tonase'])->name('transports.select2tonase');
    Route::get('transports/select2self', [Backend\TransportController::class, 'select2self'])->name('transports.select2self');
    Route::get('transports/select2ldo', [Backend\TransportController::class, 'select2ldo'])->name('transports.select2ldo');
    Route::get('transports/select2joborder', [Backend\TransportController::class, 'select2joborder'])->name('transports.select2joborder');
    Route::get('typecapacities/select2', [Backend\TypeCapacityController::class, 'select2'])->name('typecapacities.select2');
    Route::get('categories/select2', [Backend\CategoryController::class, 'select2'])->name('categories.select2');
    Route::get('supplierspareparts/select2', [Backend\SupplierSparepartController::class, 'select2'])->name('supplierspareparts.select2');
    Route::get('spareparts/select2', [Backend\SparepartController::class, 'select2'])->name('spareparts.select2');
    Route::get('prefixes/select2', [Backend\PrefixController::class, 'select2'])->name('prefixes.select2');
    Route::get('expenses/select2', [Backend\ExpenseController::class, 'select2'])->name('expenses.select2');
    Route::get('stocks/select2', [Backend\StockController::class, 'select2'])->name('stocks.select2');
    Route::get('employeesmaster/select2', [Backend\EmployeeMasterController::class, 'select2'])->name('employeesmaster.select2');
    Route::get('employee/select2', [Backend\EmployeeController::class, 'select2'])->name('employee.select2');
    Route::get('mastercoa/select2', [Backend\CoaController::class, 'select2'])->name('mastercoa.select2');
    Route::get('mastercoa/select2self', [Backend\CoaController::class, 'select2self'])->name('mastercoa.select2self');
    Route::get('stocks/select2Invoice', [Backend\StockController::class, 'select2Invoice'])->name('stocks.select2Invoice');
    Route::get('stocks/select2Opname', [Backend\StockController::class, 'select2Opname'])->name('stocks.select2Opname');
    Route::get('invoicereturpurchases/select2Invoice', [Backend\InvoiceReturPurchaseController::class, 'select2Invoice'])->name('invoicereturpurchases.select2Invoice');
    Route::get('invoicereturpurchases/select2SparePart', [Backend\InvoiceReturPurchaseController::class, 'select2SparePart'])->name('invoicereturpurchases.select2SparePart');
    Route::get('cooperation/select2', [Backend\CooperationController::class, 'select2'])->name('cooperation.select2');
    Route::get('banks/select2', [Backend\BankController::class, 'select2'])->name('banks.select2');
    Route::get('journals/select2', [Backend\JournalController::class, 'select2'])->name('journals.select2');
    Route::get('opname/select2opname', [Backend\OpnameController::class, 'select2Opname'])->name('opname.select2opname');
    Route::get('opname/select2invoice', [Backend\OpnameController::class, 'select2Invoice'])->name('opname.select2invoice');

    //Print
    Route::get('invoicesalaries/{id}/dotmatrix', [Backend\InvoiceSalaryController::class, 'dotMatrix']);
    Route::get('invoicesalaries/{id}/print', [Backend\InvoiceSalaryController::class, 'print']);
    Route::get('invoiceldo/{id}/print', [Backend\InvoiceLdoController::class, 'print']);
    Route::get('invoiceusageitems/{id}/print', [Backend\InvoiceUsageItemController::class, 'print'])->name('invoiceusageitems.print');
    Route::get('invoiceusageitems/{id}/dotmatrix', [Backend\InvoiceUsageItemController::class, 'printDotMatrix'])->name('invoiceusageitems.print-dotmatrix');
    Route::get('invoiceusageitemsoutside/{id}/print', [Backend\InvoiceUsageItemOutsideController::class, 'print'])->name('invoiceusageitemsoutside.print');
    Route::get('invoiceusageitemsoutside/{id}/dotmatrix', [Backend\InvoiceUsageItemOutsideController::class, 'printDotMatrix'])->name('invoiceusageitemsoutside.print-dotmatrix');
    Route::get('opnames/{id}/print', [Backend\OpnameController::class, 'print']);
    Route::get('invoicereturpurchases/{id}/print', [Backend\InvoiceReturPurchaseController::class, 'print']);
    Route::get('invoicepurchases/{id}/print', [Backend\InvoicePurchaseController::class, 'print'])->name('invoicepurchase.print');
    Route::get('invoicepurchases/{id}/dotmatrix', [Backend\InvoiceUsageItemOutsideController::class, 'printDotMatrix'])->name('invoicepurchase.print-dotmatrix');
    Route::get('joborders/{id}/print', [Backend\JobOrderController::class, 'print']);
    Route::get('reportsparepart/print', [Backend\SparepartController::class, 'print']);
    Route::get('reportsparepart/document', [Backend\SparepartController::class, 'document']);
    Route::get('reportcostumers/document', [Backend\ReportCostumerController::class, 'document']);
    Route::get('reportcostumers/print', [Backend\ReportCostumerController::class, 'print']);
    Route::get('reportcostumers/document', [Backend\ReportCostumerController::class, 'document']);
    Route::get('reportdrivers/document', [Backend\ReportDriverController::class, 'document']);
    Route::get('reportdrivers/print', [Backend\ReportDriverController::class, 'print']);
    Route::get('reportkasbondrivers/print', [Backend\ReportKasbonDriverController::class, 'print']);
    Route::get('reportkasbondrivers/document', [Backend\ReportKasbonDriverController::class, 'document']);
    Route::get('reporttransports/print', [Backend\ReportTransportController::class, 'print']);
    Route::get('reporttransports/document', [Backend\ReportTransportController::class, 'document']);
    Route::get('reportrecapjoborders/print', [Backend\ReportRecapJobOrderController::class, 'print']);
    Route::get('reportrecapjoborders/document', [Backend\ReportRecapJobOrderController::class, 'document']);
    Route::get('reportrecapsalaries/print', [Backend\ReportRecapSalaryController::class, 'print']);
    Route::get('reportrecapsalaries/document', [Backend\ReportRecapSalaryController::class, 'document']);
    Route::get('reportsalarydrivers/print', [Backend\ReportSalaryController::class, 'print']);
    Route::get('reportsalarydrivers/document', [Backend\ReportSalaryController::class, 'document']);
    Route::get('reportjoborders/print', [Backend\ReportJoborderController::class, 'print']);
    Route::get('reportjoborders/document', [Backend\ReportJoborderController::class, 'document']);
    Route::get('reportinvoicecostumers/print', [Backend\ReportInvoiceCostumerController::class, 'print']);
    Route::get('reportinvoicecostumers/document', [Backend\ReportInvoiceCostumerController::class, 'document']);
    Route::get('reportinvoiceldo/print', [Backend\ReportInvoiceLdoController::class, 'print']);
    Route::get('reportinvoiceldo/document', [Backend\ReportInvoiceLdoController::class, 'document']);
    Route::get('reportkasbonemployees/print', [Backend\ReportKasbonEmployeeController::class, 'print']);
    Route::get('reportkasbonemployees/document', [Backend\ReportKasbonEmployeeController::class, 'document']);
    Route::get('reportsalaryemployees/print', [Backend\ReportSalaryEmployeesController::class, 'print']);
    Route::get('reportsalaryemployees/document', [Backend\ReportSalaryEmployeesController::class, 'document']);
    Route::get('reportpurchaseorders/print', [Backend\ReportPurchaseOrderController::class, 'print']);
    Route::get('reportpurchaseorders/document', [Backend\ReportPurchaseOrderController::class, 'document']);
    Route::get('reportrecappurchaseorders/print', [Backend\ReportRecapPurchaseOrderController::class, 'print']);
    Route::get('reportrecappurchaseorders/document', [Backend\ReportRecapPurchaseOrderController::class, 'document']);
    Route::get('reportreturpurchases/print', [Backend\ReportReturPurchaseController::class, 'print']);
    Route::get('reportreturpurchases/document', [Backend\ReportReturPurchaseController::class, 'document']);
    Route::get('reportrecapreturpurchases/print', [Backend\ReportRecapReturPurchaseController::class, 'print']);
    Route::get('reportrecapreturpurchases/document', [Backend\ReportRecapReturPurchaseController::class, 'document']);
    Route::get('reportusageitems/print', [Backend\ReportUsageItemsController::class, 'print']);
    Route::get('reportusageitems/document', [Backend\ReportUsageItemsController::class, 'document']);
    Route::get('reportusageinsideoutside/print', [Backend\ReportUsageItemInsideOutsideController::class, 'print']);
    Route::get('reportusageinsideoutside/document', [Backend\ReportUsageItemInsideOutsideController::class, 'document']);
    Route::get('reportrecapusageitems/print', [Backend\ReportRecapUsageItemsController::class, 'print']);
    Route::get('reportrecapusageitems/document', [Backend\ReportRecapUsageItemsController::class, 'document']);
    Route::get('reportusageitemoutside/print', [Backend\ReportUsageItemOutsideController::class, 'print']);
    Route::get('reportusageitemoutside/document', [Backend\ReportUsageItemOutsideController::class, 'document']);
    Route::get('reportrecapusageitemoutside/print', [Backend\ReportRecapUsageItemOutsideController::class, 'print']);
    Route::get('reportrecapusageitemoutside/document', [Backend\ReportRecapUsageItemOutsideController::class, 'document']);
    Route::get('recapitulation/document', [Backend\RecapitulationController::class, 'document'])->name('recapitulation.document');
    Route::get('recapitulation/print', [Backend\RecapitulationController::class, 'print'])->name('recapitulation.print');
    Route::get('reportstocks/print', [Backend\ReportStockController::class, 'print']);
    Route::get('reportstocks/document', [Backend\ReportStockController::class, 'document']);
    Route::get('ledger/print', [Backend\ReportLedgerController::class, 'print']);
    Route::get('ledger/document', [Backend\ReportLedgerController::class, 'document']);
    Route::get('ledgeroperational/print', [Backend\ReportLedgerOperationalController::class, 'print']);
    Route::get('ledgeroperational/document', [Backend\ReportLedgerOperationalController::class, 'document']);
    Route::get('ledgersparepart/print', [Backend\ReportLedgerSparePartController::class, 'print']);
    Route::get('ledgersparepart/document', [Backend\ReportLedgerSparePartController::class, 'document']);
    Route::get('ledgeraccounting/print', [Backend\ReportLedgerAccountingController::class, 'print']);
    Route::get('ledgeraccounting/document', [Backend\ReportLedgerAccountingController::class, 'document']);
    Route::get('profitloss/print', [Backend\ReportProfitLossController::class, 'print']);
    Route::get('profitloss/document', [Backend\ReportProfitLossController::class, 'document']);
    Route::get('reportcustomerroadmoney/print', [Backend\ReportCustomerRoadMoneyController::class, 'print']);
    Route::get('reportcustomerroadmoney/document', [Backend\ReportCustomerRoadMoneyController::class, 'document']);
    Route::get('reportldonetprofit/print', [Backend\ReportLdoNetProfitController::class, 'print']);
    Route::get('reportldonetprofit/document', [Backend\ReportLdoNetProfitController::class, 'document']);

    Route::get('invoicekasbons/{id}/print', [Backend\InvoiceKasbonController::class, 'print']);
    Route::get('invoicecostumers/{id}/print', [Backend\InvoiceCostumerController::class, 'print']);
    Route::get('monthlysalarydetail/{id}/print', [Backend\MonthlySalaryDetailController::class, 'print']);
    Route::get('invoicekasbonemployees/{id}/print', [Backend\InvoiceKasbonEmployeeController::class, 'print']);
    Route::get('kasbon/{id}/print', [Backend\KasbonController::class, 'print'])->name('kasbon.print');
    Route::get('kasbon/{id}/dotmatrix', [Backend\KasbonController::class, 'printDotMatrix'])->name('kasbon.print-dotmatrix');
    Route::post('kasbon/dotMatrixMultiple', [Backend\KasbonController::class, 'printDotMatrixMultiple'])->name('kasbon.print-dotMatrixMultiple');
    Route::get('kasbon/printMultiple', [Backend\KasbonController::class, 'printMultiple'])->name('kasbon.printMultiple');

    Route::get('kasbonemployees/{id}/print', [Backend\KasbonEmployeeController::class, 'print']);
    Route::get('reportpiutanglunas/print', [Backend\ReportPiutangLunasController::class, 'print']);
    Route::get('reportpiutangbelumlunas/print', [Backend\ReportPiutangBelumLunasController::class, 'print']);
    Route::get('completepurchaseorder/{id}/print', [Backend\CompletePurchaseOrderController::class, 'print']);

    //Datatables Details
    Route::get('invoicesalaries/datatabledetail/{id}', [Backend\InvoiceSalaryController::class, 'datatabledetail'])->name('invoicesalaries.datatabledetail');
    Route::get('invoicecostumers/datatabledetail/{id}', [Backend\InvoiceCostumerController::class, 'datatabledetail'])->name('invoicecostumers.datatabledetail');
    Route::get('invoiceldo/datatabledetail/{id}', [Backend\InvoiceLdoController::class, 'datatabledetail'])->name('invoiceldo.datatabledetail');
    Route::get('invoicekasbons/datatabledetail/{id}', [Backend\InvoiceKasbonController::class, 'datatabledetail'])->name('invoicekasbons.datatabledetail');
    Route::get('employeessalary/datatabledetail/{id}', [Backend\EmployessSalaryController::class, 'datatabledetail'])->name('employeessalary.datatabledetail');
    Route::get('monthlysalarydetail/datatabledetail/{id}', [Backend\MonthlySalaryDetailController::class, 'datatabledetail'])->name('monthlysalarydetail.datatabledetail');
    Route::get('invoicekasbonemployees/datatabledetail/{id}', [Backend\InvoiceKasbonEmployeeController::class, 'datatabledetail'])->name('invoicekasbonemployees.datatabledetail');
    Route::get('reportinvoicecostumers/datatabledetail/{id}', [Backend\ReportInvoiceCostumerController::class, 'datatabledetail'])->name('reportinvoicecostumers.datatabledetail');
    Route::get('reportinvoiceldo/datatabledetail/{id}', [Backend\ReportInvoiceLdoController::class, 'datatabledetail'])->name('reportinvoiceldo.datatabledetail');
    Route::get('reportcustomerroadmoney/datatabledetail/{id}', [Backend\ReportCustomerRoadMoneyController::class, 'datatabledetail'])->name('reportcustomerroadmoney.datatabledetail');
    Route::get('joborders/datatabledetail/{id}', [Backend\JobOrderController::class, 'datatabledetail'])->name('joborders.datatabledetail');
    Route::get('kasbon/datatableshow/{id}', [Backend\KasbonController::class, 'datatableshow'])->name('kasbon.datatableshow');

    //Route Free
    Route::prefix('anotherexpedition')->name('anotherexpedition.')->group(function () {
      Route::get('{id}/create_driver/', [Backend\AnotherExpeditionController::class, 'create_driver'])->name('create_driver');
      Route::get('{id}/create_transport/', [Backend\AnotherExpeditionController::class, 'create_transport'])->name('create_transport');
      Route::get('{id}/datatable_transport/', [Backend\AnotherExpeditionController::class, 'datatable_transport'])->name('datatable_transport');
      Route::get('{id}/datatable_driver/', [Backend\AnotherExpeditionController::class, 'datatable_driver'])->name('datatable_driver');
    });
    Route::post('roadmonies/typecapacities', [Backend\RoadMoneyController::class, 'typecapacities'])->name('roadmonies.typecapacities');
    Route::put('joborders/{id}/updateJobOrder', [Backend\JobOrderController::class, 'updateJobOrder'])->name('joborders.updateJobOrder');
    Route::post('joborders/roadmoney', [Backend\JobOrderController::class, 'roadmoney'])->name('joborders.roadmoney');
    Route::put('roadmonies/{id}/updatetypecapacities', [Backend\RoadMoneyController::class, 'updatetypecapacities'])->name('roadmonies.updatetypecapacities');
    Route::get('invoicepurchases/{id}/cetakpdf', [Backend\InvoicePurchaseController::class, 'cetakPdfInvoice'])->name('invoicepurchases.cetakpdf');
    Route::post('joborders/storeexpense', [Backend\JobOrderController::class, 'storeexpense'])->name('joborders.storeexpense');
    Route::post('invoicesalaries/findbypk', [Backend\InvoiceSalaryController::class, 'findbypk'])->name('invoicesalaries.findbypk');
    Route::post('invoicekasbons/findbypk', [Backend\InvoiceKasbonController::class, 'findbypk'])->name('invoicekasbons.findbypk');
    Route::post('invoicecostumers/findbypk', [Backend\InvoiceCostumerController::class, 'findbypk'])->name('invoicecostumers.findbypk');
    Route::post('invoiceldo/findbypk', [Backend\InvoiceLdoController::class, 'findbypk'])->name('invoiceldo.findbypk');
    Route::get('invoicepurchases/{id}/showpayment', [Backend\InvoicePurchaseController::class, 'showpayment'])->name('invoicepurchases.showpayment');
    Route::post('invoicekasbonemployees/findbypk', [Backend\InvoiceKasbonEmployeeController::class, 'findbypk'])->name('invoicekasbonemployees.findbypk');
    Route::get('reportcustomerroadmoney/findbypk/{id}', [Backend\ReportCustomerRoadMoneyController::class, 'findbypk'])->name('reportcustomerroadmoney.findbypk');
    Route::get('operationalexpenses/findbypk/{id}', [Backend\OperationalExpenseController::class, 'findbypk'])->name('operationalexpense.findbypk');
    Route::get('submission/findbypk/{id}', [Backend\SubmissionController::class, 'findbypk'])->name('submission.findbypk');
    Route::post('completepurchaseorder/findbypk', [Backend\CompletePurchaseOrderController::class, 'findbypk'])->name('completepurchaseorder.findbypk');

    //Master Operationals
    Route::resource('costumers', Backend\CostumerController::class)->except(['create', 'edit', 'show']);
    Route::resource('banks', Backend\BankController::class);
    Route::resource('drivers', Backend\DriverController::class);
    Route::resource('routes', Backend\RouteController::class)->except(['create', 'edit', 'show']);
    Route::resource('cargos', Backend\CargoController::class)->except(['create', 'edit', 'show']);
    Route::resource('transports', Backend\TransportController::class)->except('show');
    Route::resource('roadmonies', Backend\RoadMoneyController::class)->except('show');
    Route::resource('expenses', Backend\ExpenseController::class)->except(['create', 'edit', 'show']);
    Route::resource('anotherexpedition', Backend\AnotherExpeditionController::class)->except(['create', 'edit']);
    Route::resource('typecapacities', Backend\TypeCapacityController::class)->except(['create', 'edit', 'show']);
    //Master Services
    Route::resource('supplierspareparts', Backend\SupplierSparepartController::class)->except(['create', 'edit', 'show']);
    Route::resource('spareparts', Backend\SparepartController::class)->except('show');
    Route::resource('brands', Backend\BrandController::class)->except(['create', 'edit', 'show']);
    Route::resource('categories', Backend\CategoryController::class)->except(['create', 'edit', 'show']);

    //Master Accounting
    Route::resource('prefixes', Backend\PrefixController::class)->except(['create', 'edit', 'show']);
    Route::resource('companies', Backend\CompanyController::class)->except(['create', 'edit', 'show']);
    Route::resource('employeesmaster', Backend\EmployeeMasterController::class)->except(['create', 'edit', 'show']);
    Route::resource('employees', Backend\EmployeeController::class);
    Route::resource('employeessalary', Backend\EmployessSalaryController::class);
    Route::prefix('employeessalary')->name('employeessalary.')->group(function () {
      Route::get('/', [Backend\EmployessSalaryController::class, 'index'])->name('index');
      Route::get('{id}/edit/', [Backend\EmployessSalaryController::class, 'edit'])->name('edit');
      Route::put('{id}', [Backend\EmployessSalaryController::class, 'put'])->name('put');
      Route::get('{id}/{employee_master_id}/destroy/', [Backend\EmployessSalaryController::class, 'destroy'])->name('destroy');
      Route::get('{id}/fetchdata/', [Backend\EmployessSalaryController::class, 'fetchdata'])->name('fetchdata');
    });
    Route::resource('monthlymaster', Backend\MonthlySalaryController::class);
    Route::prefix('monthlysalarydetail')->name('monthlysalarydetail.')->group(function () {
      Route::get('{id}', [Backend\MonthlySalaryDetailController::class, 'index'])->name('index');
      Route::get('{id}/detail', [Backend\MonthlySalaryDetailController::class, 'show'])->name('show');
      Route::put('{id}', [Backend\MonthlySalaryDetailController::class, 'update'])->name('update');
      Route::delete('{id}', [Backend\MonthlySalaryDetailController::class, 'destroy'])->name('delete');
    });
    Route::resource('kasbonemployees', Backend\KasbonEmployeeController::class);
    Route::resource('invoicekasbonemployees', Backend\InvoiceKasbonEmployeeController::class);

    //Purchase
    Route::resource('invoicepurchases', Backend\InvoicePurchaseController::class);
    Route::resource('invoicereturpurchases', Backend\InvoiceReturPurchaseController::class);
    Route::resource('stocks', Backend\StockController::class)->only('index');

    //Settings
    Route::resource('settings', Backend\SettingController::class);
    Route::resource('cooperation', Backend\CooperationController::class);

    //Job Order
    Route::resource('joborders', Backend\JobOrderController::class);
    Route::get('submission/datatable_history', [Backend\SubmissionController::class, 'datatable_history'])->name('submission.datatable-history');
    Route::resource('submission', Backend\SubmissionController::class);
    Route::resource('operationalexpenses', Backend\OperationalExpenseController::class)->only(['store', 'update', 'destroy', 'index']);
    Route::resource('salaries', Backend\SalaryController::class);
    Route::resource('recapitulation', Backend\RecapitulationController::class);
    Route::resource('invoicesalaries', Backend\InvoiceSalaryController::class);
    Route::resource('invoicecostumers', Backend\InvoiceCostumerController::class);
    Route::put('invoicecostumerstaxfee/{id}', [Backend\InvoiceCostumerController::class, 'taxfee']);
    Route::resource('invoiceldo', Backend\InvoiceLdoController::class);
    Route::resource('invoiceusageitemsoutside', Backend\InvoiceUsageItemOutsideController::class);
    Route::resource('invoiceusageitems', Backend\InvoiceUsageItemController::class);
    Route::resource('invoicekasbons', Backend\InvoiceKasbonController::class);
    Route::resource('paymentldo', Backend\PaymentLdoController::class);
    Route::resource('opnames', Backend\OpnameController::class);

    Route::get('dashboard', Backend\DashboardController::class);
    Route::resource('activitylog', Backend\ActivityLogController::class);

    //Report
    Route::resource('reportsparepart', Backend\ReportSparepartController::class);
    Route::resource('kasbon', Backend\KasbonController::class);
    Route::get('reportcostumers', [Backend\ReportCostumerController::class, 'index'])->name('reportcostumers.index');
    Route::get('reportdrivers', [Backend\ReportDriverController::class, 'index'])->name('reportdrivers.index');
    Route::get('reportkasbondrivers', [Backend\ReportKasbonDriverController::class, 'index'])->name('reportkasbondrivers.index');
    Route::get('reporttransports', [Backend\ReportTransportController::class, 'index'])->name('reportransports.index');
    Route::get('reportjoborders', [Backend\ReportJoborderController::class, 'index'])->name('reportjoborders.index');
    Route::get('reportrecapjoborders', [Backend\ReportRecapJobOrderController::class, 'index'])->name('reportrecapjoborders.index');
    Route::get('reportrecapsalaries', [Backend\ReportRecapSalaryController::class, 'index'])->name('reportrecapsalaries.index');
    Route::get('reportsalarydrivers', [Backend\ReportSalaryController::class, 'index'])->name('reportsalarydrivers.index');
    Route::get('reportinvoicecostumers', [Backend\ReportInvoiceCostumerController::class, 'index'])->name('reportinvoicecostumers.index');
    Route::get('reportinvoiceldo', [Backend\ReportInvoiceLdoController::class, 'index'])->name('reportinvoiceldo.index');
    Route::get('reportkasbonemployees', [Backend\ReportKasbonEmployeeController::class, 'index'])->name('reportkasbonemployees.index');
    Route::get('reportsalaryemployees', [Backend\ReportSalaryEmployeesController::class, 'index'])->name('reportsalaryemployees.index');
    Route::get('reportpurchaseorders', [Backend\ReportPurchaseOrderController::class, 'index'])->name('reportpurchaseorders.index');
    Route::get('reportrecappurchaseorders', [Backend\ReportRecapPurchaseOrderController::class, 'index'])->name('reportrecappurchaseorders.index');
    Route::get('reportreturpurchases', [Backend\ReportReturPurchaseController::class, 'index'])->name('reportreturpurchases.index');
    Route::get('reportrecapreturpurchases', [Backend\ReportRecapReturPurchaseController::class, 'index'])->name('reportrecapreturpurchases.index');
    Route::get('reportusageitems', [Backend\ReportUsageItemsController::class, 'index'])->name('reportusageitems.index');
    Route::get('reportrecapusageitems', [Backend\ReportRecapUsageItemsController::class, 'index'])->name('reportrecapusageitems.index');
    Route::get('reportusageitemoutside', [Backend\ReportUsageItemOutsideController::class, 'index'])->name('reportusageitemoutside.index');
    Route::get('reportrecapusageitemoutside', [Backend\ReportRecapUsageItemOutsideController::class, 'index'])->name('reportrecapusageitemoutside.index');
    Route::get('reportstocks', [Backend\ReportStockController::class, 'index'])->name('reportstocks.index');
    Route::get('reportcustomerroadmoney', [Backend\ReportCustomerRoadMoneyController::class, 'index'])->name('reportcustomerroadmoney.index');
    Route::get('reportldonetprofit', [Backend\ReportLdoNetProfitController::class, 'index'])->name('reportldonetprofit.index');
    Route::resource('mastercoa', Backend\CoaController::class);
    Route::resource('journals', Backend\JournalController::class);
    Route::resource('configcoa', Backend\ConfigCoaController::class);
    Route::resource('configledger', Backend\ConfigLedgerController::class);
    Route::resource('necarabalane', Backend\ReportNeracaBalanceController::class);
    Route::resource('ledger', Backend\ReportLedgerController::class);
    Route::resource('ledgeroperational', Backend\ReportLedgerOperationalController::class);
    Route::resource('ledgersparepart', Backend\ReportLedgerSparePartController::class);
    Route::resource('ledgeraccounting', Backend\ReportLedgerAccountingController::class);
    Route::resource('finance', Backend\ReportFinanceController::class);
    Route::resource('neraca', Backend\ReportNeracaBalanceController::class);
    Route::resource('profitloss', Backend\ReportProfitLossController::class);
    Route::resource('reportpiutanglunas', Backend\ReportPiutangLunasController::class);
    Route::resource('reportpiutangbelumlunas', Backend\ReportPiutangBelumLunasController::class);
    Route::resource('completepurchaseorder', Backend\CompletePurchaseOrderController::class);
    Route::resource('reportusageinsideoutside', Backend\ReportUsageItemInsideOutsideController::class);
    Route::resource('reportrecapkasbondrivers', Backend\ReportRecapKasbonDriverController::class);

    /* Laporan Rekap Gaji Supir Bulanan */
    Route::resource('l-rekap-gaji-bulanan', Backend\LaporanRekapGajiBulananController::class);

    /* Laporan Rekap Pengeluaran Sparepart Mobil */
    Route::resource('l-rekap-pengeluaran-sparepart', Backend\LaporanRekapPengeluaranMobilController::class);

    /* Laporan Rekap Pendapatan Kotor Mobill */
    Route::resource('l-rekap-pendapatan-kotor', Backend\LaporanRekapPendapatanKotorMobilController::class);
    Route::get('jo_calculate', [Backend\JobOrderController::class, 'jo_calculate']);

    /* Laporan Rekap Operasional */
    Route::get('laporan-rekap-operasional/export', [Backend\LaporanRekapOperasionalController::class, 'export']);
    Route::resource('laporan-rekap-operasional', Backend\LaporanRekapOperasionalController::class)->except('shot');

  });
});
