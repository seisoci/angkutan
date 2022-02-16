<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backend\UsersController as BackendUsersController;
use App\Http\Controllers\Backend\RolesController as BackendRolesController;
use App\Http\Controllers\Backend\CostumerController as BackendCostumerController;
use App\Http\Controllers\Backend\DriverController as BackendDriverController;
use App\Http\Controllers\Backend\RouteController as BackendRouteController;
use App\Http\Controllers\Backend\CargoController as BackendCargoController;
use App\Http\Controllers\Backend\TransportController as BackendTransportController;
use App\Http\Controllers\Backend\RoadMoneyController as BackendRoadMoneyController;
use App\Http\Controllers\Backend\ExpenseController as BackendExpenseController;
use App\Http\Controllers\Backend\AnotherExpeditionController as BackendAnotherExpeditionController;
use App\Http\Controllers\Backend\SupplierSparepartController as BackendSupplierSparepartController;
use App\Http\Controllers\Backend\SparepartController as BackendSparepartController;
use App\Http\Controllers\Backend\BrandController as BackendBrandController;
use App\Http\Controllers\Backend\CategoryController as BackendCategoryController;
use App\Http\Controllers\Backend\CompanyController as BackendCompanyController;
use App\Http\Controllers\Backend\TypeCapacityController as BackendTypeCapacityController;
use App\Http\Controllers\Backend\PrefixController as BackendPrefixController;
use App\Http\Controllers\Backend\StockController as BackendStockController;
use App\Http\Controllers\Backend\InvoicePurchaseController as BackendInvoicePurchaseController;
use App\Http\Controllers\Backend\SettingController as BackendSettingController;
use App\Http\Controllers\Backend\CooperationController as BackendCooperationController;
use App\Http\Controllers\Backend\JobOrderController as BackendJobOrderController;
use App\Http\Controllers\Backend\OperationalExpenseController as BackendOperationalExpenseController;
use App\Http\Controllers\Backend\SalaryController as BackendSalaryController;
use App\Http\Controllers\Backend\RecapitulationController as BackendRecapitulationController;
use App\Http\Controllers\Backend\InvoiceCostumerController as BackendInvoiceCostumerController;
use App\Http\Controllers\Backend\InvoiceLdoController as BackendInvoiceLdoController;
use App\Http\Controllers\Backend\PaymentLdoController as BackendPaymentLdoController;
use App\Http\Controllers\Backend\InvoiceUsageItemController as BackendInvoiceUsageItemController;
use App\Http\Controllers\Backend\InvoiceUsageItemOutsideController as BackendInvoiceUsageItemOutsideController;
use App\Http\Controllers\Backend\OpnameController as BackendOpnameController;
use App\Http\Controllers\Backend\InvoiceReturPurchaseController as BackendInvoiceReturPurchaseController;
use App\Http\Controllers\Backend\DashboardController as BackendDashboardController;
use App\Http\Controllers\Backend\KasbonController as BackendKasbonController;
use App\Http\Controllers\Backend\BankController as BackendBankController;
use App\Http\Controllers\Backend\ReportSparepartController as BackendReportSparepartController;
use App\Http\Controllers\Backend\InvoiceKasbonController as BackendInvoiceKasbonController;
use App\Http\Controllers\Backend\ActivityLogController as BackendActivityLogController;
use App\Http\Controllers\Backend\EmployeeMasterController as BackendEmployeeMasterController;
use App\Http\Controllers\Backend\EmployeeController as BackendEmployeeController;
use App\Http\Controllers\Backend\EmployessSalaryController as BackendEmployeeSalaryController;
use App\Http\Controllers\Backend\MonthlySalaryController as BackendMonthlySalaryController;
use App\Http\Controllers\Backend\MonthlySalaryDetailController as BackendMonthlySalaryDetailController;
use App\Http\Controllers\Backend\InvoiceKasbonEmployeeController as BackendInvoiceKasbonEmployeeController;
use App\Http\Controllers\Backend\KasbonEmployeeController as BackendKasbonEmployeeController;
use App\Http\Controllers\Backend\ReportCostumerController as BackendReportCostumerController;
use App\Http\Controllers\Backend\ReportDriverController as BackendReportDriverController;
use App\Http\Controllers\Backend\ReportKasbonDriverController as BackendReportKasbonDriverController;
use App\Http\Controllers\Backend\ReportTransportController as BackendReportTransportController;
use App\Http\Controllers\Backend\ReportJobOrderController as BackendReportJoborderController;
use App\Http\Controllers\Backend\ReportRecapJobOrderController as BackendReportRecapJobOrderController;
use App\Http\Controllers\Backend\ReportRecapSalaryController as BackendReportRecapSalaryController;
use App\Http\Controllers\Backend\ReportSalaryController as BackendReportSalaryController;
use App\Http\Controllers\Backend\ReportInvoiceCostumerController as BackendReportInvoiceCostumer;
use App\Http\Controllers\Backend\ReportInvoiceLdoController as BackendReportInvoiceLdoController;
use App\Http\Controllers\Backend\ReportKasbonEmployeeController as BackendReportKasbonEmployeeController;
use App\Http\Controllers\Backend\ReportSalaryEmployeesController as BackendReportSalaryEmployeesController;
use App\Http\Controllers\Backend\ReportPurchaseOrderController as BackendReportPurchaseOrderController;
use App\Http\Controllers\Backend\ReportRecapPurchaseOrderController as BackendReportRecapPurchaseOrderController;
use App\Http\Controllers\Backend\ReportReturPurchaseController as BackendReportReturPurchaseController;
use App\Http\Controllers\Backend\ReportRecapReturPurchaseController as ReportRecapReturPurchaseController;
use App\Http\Controllers\Backend\ReportUsageItemsController as BackendReportUsageItemsController;
use App\Http\Controllers\Backend\ReportRecapUsageItemsController as BackendReportRecapUsageItemsController;
use App\Http\Controllers\Backend\ReportUsageItemOutsideController as BackendReportUsageItemOutsideController;
use App\Http\Controllers\Backend\ReportRecapUsageItemOutsideController as BackendReportRecapUsageItemOutsideController;
use App\Http\Controllers\Backend\ReportStockController as BackendReportStockController;
use App\Http\Controllers\Backend\CoaController as BackendCoaController;
use App\Http\Controllers\Backend\JournalController as BackendJournalController;
use App\Http\Controllers\Backend\ConfigCoaController as BackendConfigCoaController;
use App\Http\Controllers\Backend\ConfigLedgerController as BackendConfigLedgerController;
use App\Http\Controllers\Backend\ReportNeracaBalanceController as BackendNeracaBalanceController;
use App\Http\Controllers\Backend\ReportLedgerController as BackendReportLedgerController;
use App\Http\Controllers\Backend\ReportLedgerOperationalController as BackendReportLedgerOperationalController;
use App\Http\Controllers\Backend\ReportLedgerSparePartController as BackendReportLedgerSparePartControllerlController;
use App\Http\Controllers\Backend\ReportLedgerAccountingController as BackendReportLedgerAccountingController;
use App\Http\Controllers\Backend\ReportFinanceController as BackendReportFinanceController;
use App\Http\Controllers\Backend\ReportNeracaBalanceController as BackendReportNeracaBalanceController;
use App\Http\Controllers\Backend\ReportProfitLossController as BackendReportProfitLossController;
use App\Http\Controllers\Backend\ReportCustomerRoadMoneyController as BackendReportCustomerRoadMoneyController;
use App\Http\Controllers\Backend\ReportLdoNetProfitController as BackendReportLdoNetProfitController;
use App\Http\Controllers\Backend\SubmissionController as BackendSubmissionController;
use App\Http\Controllers\PagesController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Backend as Backend;

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
    Route::post('resetpassword', [BackendUsersController::class, 'resetpassword'])->name('users.resetpassword');
    Route::post('changepassword', [BackendUsersController::class, 'changepassword'])->name('users.changepassword');
    Route::resource('users', BackendUsersController::class)->except('show');
    Route::resource('roles', BackendRolesController::class)->except(['create', 'show', 'destroy']);

    //Select2
    Route::get('costumers/select2', [BackendCostumerController::class, 'select2'])->name('costumers.select2');
    Route::get('cargos/select2', [BackendCargoController::class, 'select2'])->name('cargos.select2');
    Route::get('routes/select2', [BackendRouteController::class, 'select2'])->name('routes.select2');
    Route::get('brands/select2', [BackendBrandController::class, 'select2'])->name('brands.select2');
    Route::get('drivers/select2', [BackendDriverController::class, 'select2'])->name('drivers.select2');
    Route::get('drivers/select2self', [BackendDriverController::class, 'select2self'])->name('drivers.select2self');
    Route::get('drivers/select2ldo', [BackendDriverController::class, 'select2ldo'])->name('drivers.select2ldo');
    Route::get('drivers/select2joborder', [BackendDriverController::class, 'select2joborder'])->name('drivers.select2joborder');
    Route::get('joborders/select2costumers', [BackendJobOrderController::class, 'select2costumers'])->name('joborders.select2costumers');
    Route::get('joborders/select2routefrom', [BackendJobOrderController::class, 'select2routefrom'])->name('joborders.select2routefrom');
    Route::get('joborders/select2routeto', [BackendJobOrderController::class, 'select2routeto'])->name('joborders.select2routeto');
    Route::get('joborders/select2cargos', [BackendJobOrderController::class, 'select2cargos'])->name('joborders.select2cargos');
    Route::get('anotherexpedition/select2', [BackendAnotherExpeditionController::class, 'select2'])->name('anotherexpedition.select2');
    Route::get('transports/select2', [BackendTransportController::class, 'select2'])->name('transports.select2');
    Route::get('transports/select2tonase', [BackendTransportController::class, 'select2tonase'])->name('transports.select2tonase');
    Route::get('transports/select2self', [BackendTransportController::class, 'select2self'])->name('transports.select2self');
    Route::get('transports/select2ldo', [BackendTransportController::class, 'select2ldo'])->name('transports.select2ldo');
    Route::get('transports/select2joborder', [BackendTransportController::class, 'select2joborder'])->name('transports.select2joborder');
    Route::get('typecapacities/select2', [BackendTypeCapacityController::class, 'select2'])->name('typecapacities.select2');
    Route::get('categories/select2', [BackendCategoryController::class, 'select2'])->name('categories.select2');
    Route::get('supplierspareparts/select2', [BackendSupplierSparepartController::class, 'select2'])->name('supplierspareparts.select2');
    Route::get('spareparts/select2', [BackendSparepartController::class, 'select2'])->name('spareparts.select2');
    Route::get('prefixes/select2', [BackendPrefixController::class, 'select2'])->name('prefixes.select2');
    Route::get('expenses/select2', [BackendExpenseController::class, 'select2'])->name('expenses.select2');
    Route::get('stocks/select2', [BackendStockController::class, 'select2'])->name('stocks.select2');
    Route::get('employeesmaster/select2', [BackendEmployeeMasterController::class, 'select2'])->name('employeesmaster.select2');
    Route::get('employee/select2', [BackendEmployeeController::class, 'select2'])->name('employee.select2');
    Route::get('mastercoa/select2', [BackendCoaController::class, 'select2'])->name('mastercoa.select2');
    Route::get('mastercoa/select2self', [BackendCoaController::class, 'select2self'])->name('mastercoa.select2self');
    Route::get('stocks/select2Invoice', [BackendStockController::class, 'select2Invoice'])->name('stocks.select2Invoice');
    Route::get('stocks/select2Opname', [BackendStockController::class, 'select2Opname'])->name('stocks.select2Opname');
    Route::get('invoicereturpurchases/select2Invoice', [BackendInvoiceReturPurchaseController::class, 'select2Invoice'])->name('invoicereturpurchases.select2Invoice');
    Route::get('invoicereturpurchases/select2SparePart', [BackendInvoiceReturPurchaseController::class, 'select2SparePart'])->name('invoicereturpurchases.select2SparePart');
    Route::get('cooperation/select2', [BackendCooperationController::class, 'select2'])->name('cooperation.select2');
    Route::get('banks/select2', [BackendBankController::class, 'select2'])->name('banks.select2');
    Route::get('journals/select2', [BackendJournalController::class, 'select2'])->name('journals.select2');

    //Print
    Route::get('kasbon/{id}/dotmatrix', [Backend\KasbonController::class, 'dotMatrix']);
    Route::post('kasbon/dotMatrixMultiple', [Backend\KasbonController::class, 'dotMatrixMultiple'])->name('kasbon.dotMatrixMultiple');
    Route::get('invoicesalaries/{id}/dotmatrix', [Backend\InvoiceSalaryController::class, 'dotMatrix']);
    Route::get('invoicesalaries/{id}/print', [Backend\InvoiceSalaryController::class, 'print']);
    Route::get('invoiceldo/{id}/print', [BackendInvoiceLdoController::class, 'print']);
    Route::get('invoiceusageitems/{id}/print', [BackendInvoiceUsageItemController::class, 'print']);
    Route::get('invoiceusageitemsoutside/{id}/print', [BackendInvoiceUsageItemOutsideController::class, 'print']);
    Route::get('opnames/{id}/print', [BackendOpnameController::class, 'print']);
    Route::get('invoicereturpurchases/{id}/print', [BackendInvoiceReturPurchaseController::class, 'print']);
    Route::get('invoicepurchases/{id}/print', [BackendInvoicePurchaseController::class, 'print']);
    Route::get('joborders/{id}/print', [BackendJobOrderController::class, 'print']);
    Route::get('reportsparepart/print', [BackendSparepartController::class, 'print']);
    Route::get('reportsparepart/document', [BackendSparepartController::class, 'document']);
    Route::get('reportcostumers/document', [BackendReportCostumerController::class, 'document']);
    Route::get('reportcostumers/print', [BackendReportCostumerController::class, 'print']);
    Route::get('reportcostumers/document', [BackendReportCostumerController::class, 'document']);
    Route::get('reportdrivers/document', [BackendReportDriverController::class, 'document']);
    Route::get('reportdrivers/print', [BackendReportDriverController::class, 'print']);
    Route::get('reportkasbondrivers/print', [BackendReportKasbonDriverController::class, 'print']);
    Route::get('reportkasbondrivers/document', [BackendReportKasbonDriverController::class, 'document']);
    Route::get('reporttransports/print', [BackendReportTransportController::class, 'print']);
    Route::get('reporttransports/document', [BackendReportTransportController::class, 'document']);
    Route::get('reportrecapjoborders/print', [BackendReportRecapJobOrderController::class, 'print']);
    Route::get('reportrecapjoborders/document', [BackendReportRecapJobOrderController::class, 'document']);
    Route::get('reportrecapsalaries/print', [BackendReportRecapSalaryController::class, 'print']);
    Route::get('reportrecapsalaries/document', [BackendReportRecapSalaryController::class, 'document']);
    Route::get('reportsalarydrivers/print', [BackendReportSalaryController::class, 'print']);
    Route::get('reportsalarydrivers/document', [BackendReportSalaryController::class, 'document']);
    Route::get('reportjoborders/print', [BackendReportJoborderController::class, 'print']);
    Route::get('reportjoborders/document', [BackendReportJoborderController::class, 'document']);
    Route::get('reportinvoicecostumers/print', [BackendReportInvoiceCostumer::class, 'print']);
    Route::get('reportinvoicecostumers/document', [BackendReportInvoiceCostumer::class, 'document']);
    Route::get('reportinvoiceldo/print', [BackendReportInvoiceLdoController::class, 'print']);
    Route::get('reportinvoiceldo/document', [BackendReportInvoiceLdoController::class, 'document']);
    Route::get('reportkasbonemployees/print', [BackendReportKasbonEmployeeController::class, 'print']);
    Route::get('reportkasbonemployees/document', [BackendReportKasbonEmployeeController::class, 'document']);
    Route::get('reportsalaryemployees/print', [BackendReportSalaryEmployeesController::class, 'print']);
    Route::get('reportsalaryemployees/document', [BackendReportSalaryEmployeesController::class, 'document']);
    Route::get('reportpurchaseorders/print', [BackendReportPurchaseOrderController::class, 'print']);
    Route::get('reportpurchaseorders/document', [BackendReportPurchaseOrderController::class, 'document']);
    Route::get('reportrecappurchaseorders/print', [BackendReportRecapPurchaseOrderController::class, 'print']);
    Route::get('reportrecappurchaseorders/document', [BackendReportRecapPurchaseOrderController::class, 'document']);
    Route::get('reportreturpurchases/print', [BackendReportReturPurchaseController::class, 'print']);
    Route::get('reportreturpurchases/document', [BackendReportReturPurchaseController::class, 'document']);
    Route::get('reportrecapreturpurchases/print', [ReportRecapReturPurchaseController::class, 'print']);
    Route::get('reportrecapreturpurchases/document', [ReportRecapReturPurchaseController::class, 'document']);
    Route::get('reportusageitems/print', [BackendReportUsageItemsController::class, 'print']);
    Route::get('reportusageitems/document', [BackendReportUsageItemsController::class, 'document']);
    Route::get('reportusageinsideoutside/print', [Backend\ReportUsageItemInsideOutsideController::class, 'print']);
    Route::get('reportusageinsideoutside/document', [Backend\ReportUsageItemInsideOutsideController::class, 'document']);
    Route::get('reportrecapusageitems/print', [BackendReportRecapUsageItemsController::class, 'print']);
    Route::get('reportrecapusageitems/document', [BackendReportRecapUsageItemsController::class, 'document']);
    Route::get('reportusageitemoutside/print', [BackendReportUsageItemOutsideController::class, 'print']);
    Route::get('reportusageitemoutside/document', [BackendReportUsageItemOutsideController::class, 'document']);
    Route::get('reportrecapusageitemoutside/print', [BackendReportRecapUsageItemOutsideController::class, 'print']);
    Route::get('reportrecapusageitemoutside/document', [BackendReportRecapUsageItemOutsideController::class, 'document']);
    Route::get('recapitulation/document', [BackendRecapitulationController::class, 'document'])->name('recapitulation.document');
    Route::get('recapitulation/print', [BackendRecapitulationController::class, 'print'])->name('recapitulation.print');
    Route::get('reportstocks/print', [BackendReportStockController::class, 'print']);
    Route::get('reportstocks/document', [BackendReportStockController::class, 'document']);
    Route::get('ledger/print', [BackendReportLedgerController::class, 'print']);
    Route::get('ledger/document', [BackendReportLedgerController::class, 'document']);
    Route::get('ledgeroperational/print', [BackendReportLedgerOperationalController::class, 'print']);
    Route::get('ledgeroperational/document', [BackendReportLedgerOperationalController::class, 'document']);
    Route::get('ledgersparepart/print', [BackendReportLedgerSparePartControllerlController::class, 'print']);
    Route::get('ledgersparepart/document', [BackendReportLedgerSparePartControllerlController::class, 'document']);
    Route::get('ledgeraccounting/print', [BackendReportLedgerAccountingController::class, 'print']);
    Route::get('ledgeraccounting/document', [BackendReportLedgerAccountingController::class, 'document']);
    Route::get('profitloss/print', [BackendReportProfitLossController::class, 'print']);
    Route::get('profitloss/document', [BackendReportProfitLossController::class, 'document']);
    Route::get('reportcustomerroadmoney/print', [BackendReportCustomerRoadMoneyController::class, 'print']);
    Route::get('reportcustomerroadmoney/document', [BackendReportCustomerRoadMoneyController::class, 'document']);
    Route::get('reportldonetprofit/print', [BackendReportLdoNetProfitController::class, 'print']);
    Route::get('reportldonetprofit/document', [BackendReportLdoNetProfitController::class, 'document']);

    Route::get('invoicekasbons/{id}/print', [BackendInvoiceKasbonController::class, 'print']);
    Route::get('invoicecostumers/{id}/print', [BackendInvoiceCostumerController::class, 'print']);
    Route::get('monthlysalarydetail/{id}/print', [BackendMonthlySalaryDetailController::class, 'print']);
    Route::get('invoicekasbonemployees/{id}/print', [BackendInvoiceKasbonEmployeeController::class, 'print']);
    Route::get('kasbon/{id}/print', [BackendKasbonController::class, 'print']);
    Route::get('kasbonemployees/{id}/print', [BackendKasbonEmployeeController::class, 'print']);
    Route::get('reportpiutanglunas/print', [Backend\ReportPiutangLunasController::class, 'print']);
    Route::get('reportpiutangbelumlunas/print', [Backend\ReportPiutangBelumLunasController::class, 'print']);
    Route::get('completepurchaseorder/{id}/print', [Backend\CompletePurchaseOrderController::class, 'print']);

    //Datatables Details
    Route::get('invoicesalaries/datatabledetail/{id}', [Backend\InvoiceSalaryController::class, 'datatabledetail'])->name('invoicesalaries.datatabledetail');
    Route::get('invoicecostumers/datatabledetail/{id}', [BackendInvoiceCostumerController::class, 'datatabledetail'])->name('invoicecostumers.datatabledetail');
    Route::get('invoiceldo/datatabledetail/{id}', [BackendInvoiceLdoController::class, 'datatabledetail'])->name('invoiceldo.datatabledetail');
    Route::get('invoicekasbons/datatabledetail/{id}', [BackendInvoiceKasbonController::class, 'datatabledetail'])->name('invoicekasbons.datatabledetail');
    Route::get('employeessalary/datatabledetail/{id}', [BackendEmployeeSalaryController::class, 'datatabledetail'])->name('employeessalary.datatabledetail');
    Route::get('monthlysalarydetail/datatabledetail/{id}', [BackendMonthlySalaryDetailController::class, 'datatabledetail'])->name('monthlysalarydetail.datatabledetail');
    Route::get('invoicekasbonemployees/datatabledetail/{id}', [BackendInvoiceKasbonEmployeeController::class, 'datatabledetail'])->name('invoicekasbonemployees.datatabledetail');
    Route::get('reportinvoicecostumers/datatabledetail/{id}', [BackendReportInvoiceCostumer::class, 'datatabledetail'])->name('reportinvoicecostumers.datatabledetail');
    Route::get('reportinvoiceldo/datatabledetail/{id}', [BackendReportInvoiceLdoController::class, 'datatabledetail'])->name('reportinvoiceldo.datatabledetail');
    Route::get('reportcustomerroadmoney/datatabledetail/{id}', [BackendReportCustomerRoadMoneyController::class, 'datatabledetail'])->name('reportcustomerroadmoney.datatabledetail');
    Route::get('joborders/datatabledetail/{id}', [BackendJobOrderController::class, 'datatabledetail'])->name('joborders.datatabledetail');
    Route::get('kasbon/datatableshow/{id}', [Backend\KasbonController::class, 'datatableshow'])->name('kasbon.datatableshow');

    //Route Free
    Route::prefix('anotherexpedition')->name('anotherexpedition.')->group(function () {
      Route::get('{id}/create_driver/', [BackendAnotherExpeditionController::class, 'create_driver'])->name('create_driver');
      Route::get('{id}/create_transport/', [BackendAnotherExpeditionController::class, 'create_transport'])->name('create_transport');
      Route::get('{id}/datatable_transport/', [BackendAnotherExpeditionController::class, 'datatable_transport'])->name('datatable_transport');
      Route::get('{id}/datatable_driver/', [BackendAnotherExpeditionController::class, 'datatable_driver'])->name('datatable_driver');
    });
    Route::post('roadmonies/typecapacities', [BackendRoadMoneyController::class, 'typecapacities'])->name('roadmonies.typecapacities');
    Route::put('joborders/{id}/updateJobOrder', [BackendJobOrderController::class, 'updateJobOrder'])->name('joborders.updateJobOrder');
    Route::post('joborders/roadmoney', [BackendJobOrderController::class, 'roadmoney'])->name('joborders.roadmoney');
    Route::put('roadmonies/{id}/updatetypecapacities', [BackendRoadMoneyController::class, 'updatetypecapacities'])->name('roadmonies.updatetypecapacities');
    Route::get('invoicepurchases/{id}/cetakpdf', [BackendInvoicePurchaseController::class, 'cetakPdfInvoice'])->name('invoicepurchases.cetakpdf');
    Route::post('joborders/storeexpense', [BackendJobOrderController::class, 'storeexpense'])->name('joborders.storeexpense');
    Route::post('invoicesalaries/findbypk', [Backend\InvoiceSalaryController::class, 'findbypk'])->name('invoicesalaries.findbypk');
    Route::post('invoicekasbons/findbypk', [BackendInvoiceKasbonController::class, 'findbypk'])->name('invoicekasbons.findbypk');
    Route::post('invoicecostumers/findbypk', [BackendInvoiceCostumerController::class, 'findbypk'])->name('invoicecostumers.findbypk');
    Route::post('invoiceldo/findbypk', [BackendInvoiceLdoController::class, 'findbypk'])->name('invoiceldo.findbypk');
    Route::get('invoicepurchases/{id}/showpayment', [BackendInvoicePurchaseController::class, 'showpayment'])->name('invoicepurchases.showpayment');
    Route::post('invoicekasbonemployees/findbypk', [BackendInvoiceKasbonEmployeeController::class, 'findbypk'])->name('invoicekasbonemployees.findbypk');
    Route::get('reportcustomerroadmoney/findbypk/{id}', [BackendReportCustomerRoadMoneyController::class, 'findbypk'])->name('reportcustomerroadmoney.findbypk');
    Route::get('operationalexpenses/findbypk/{id}', [BackendOperationalExpenseController::class, 'findbypk'])->name('operationalexpense.findbypk');
    Route::get('submission/findbypk/{id}', [BackendSubmissionController::class, 'findbypk'])->name('submission.findbypk');
    Route::post('completepurchaseorder/findbypk', [Backend\CompletePurchaseOrderController::class, 'findbypk'])->name('completepurchaseorder.findbypk');

    //Master Operationals
    Route::resource('costumers', BackendCostumerController::class)->except(['create', 'edit', 'show']);
    Route::resource('banks', BackendBankController::class);
    Route::resource('drivers', BackendDriverController::class);
    Route::resource('routes', BackendRouteController::class)->except(['create', 'edit', 'show']);
    Route::resource('cargos', BackendCargoController::class)->except(['create', 'edit', 'show']);
    Route::resource('transports', BackendTransportController::class)->except('show');
    Route::resource('roadmonies', BackendRoadMoneyController::class)->except('show');
    Route::resource('expenses', BackendExpenseController::class)->except(['create', 'edit', 'show']);
    Route::resource('anotherexpedition', BackendAnotherExpeditionController::class)->except(['create', 'edit']);
    Route::resource('typecapacities', BackendTypeCapacityController::class)->except(['create', 'edit', 'show']);
    //Master Services
    Route::resource('supplierspareparts', BackendSupplierSparepartController::class)->except(['create', 'edit', 'show']);
    Route::resource('spareparts', BackendSparepartController::class)->except('show');
    Route::resource('brands', BackendBrandController::class)->except(['create', 'edit', 'show']);
    Route::resource('categories', BackendCategoryController::class)->except(['create', 'edit', 'show']);

    //Master Accounting
    Route::resource('prefixes', BackendPrefixController::class)->except(['create', 'edit', 'show']);
    Route::resource('companies', BackendCompanyController::class)->except(['create', 'edit', 'show']);
    Route::resource('employeesmaster', BackendEmployeeMasterController::class)->except(['create', 'edit', 'show']);
    Route::resource('employees', BackendEmployeeController::class);
    Route::resource('employeessalary', BackendEmployeeSalaryController::class);
    Route::prefix('employeessalary')->name('employeessalary.')->group(function () {
      Route::get('/', [BackendEmployeeSalaryController::class, 'index'])->name('index');
      Route::get('{id}/edit/', [BackendEmployeeSalaryController::class, 'edit'])->name('edit');
      Route::put('{id}', [BackendEmployeeSalaryController::class, 'put'])->name('put');
      Route::get('{id}/{employee_master_id}/destroy/', [BackendEmployeeSalaryController::class, 'destroy'])->name('destroy');
      Route::get('{id}/fetchdata/', [BackendEmployeeSalaryController::class, 'fetchdata'])->name('fetchdata');
    });
    Route::resource('monthlymaster', BackendMonthlySalaryController::class);
    Route::prefix('monthlysalarydetail')->name('monthlysalarydetail.')->group(function () {
      Route::get('{id}', [BackendMonthlySalaryDetailController::class, 'index'])->name('index');
      Route::get('{id}/detail', [BackendMonthlySalaryDetailController::class, 'show'])->name('show');
      Route::put('{id}', [BackendMonthlySalaryDetailController::class, 'update'])->name('update');
      Route::delete('{id}', [BackendMonthlySalaryDetailController::class, 'destroy'])->name('delete');
    });
    Route::resource('kasbonemployees', BackendKasbonEmployeeController::class);
    Route::resource('invoicekasbonemployees', BackendInvoiceKasbonEmployeeController::class);


    //Purchase
    Route::resource('invoicepurchases', BackendInvoicePurchaseController::class);
    Route::resource('invoicereturpurchases', BackendInvoiceReturPurchaseController::class);
    Route::resource('stocks', BackendStockController::class)->only('index');

    //Settings
    Route::resource('settings', BackendSettingController::class);
    Route::resource('cooperation', BackendCooperationController::class);

    //Job Order
    Route::resource('joborders', BackendJobOrderController::class);
    Route::resource('submission', BackendSubmissionController::class);
    Route::resource('operationalexpenses', BackendOperationalExpenseController::class)->only(['store', 'update', 'destroy', 'index']);
    Route::resource('salaries', BackendSalaryController::class);
    Route::resource('recapitulation', BackendRecapitulationController::class);
    Route::resource('invoicesalaries', Backend\InvoiceSalaryController::class);
    Route::resource('invoicecostumers', BackendInvoiceCostumerController::class);
    Route::put('invoicecostumerstaxfee/{id}', [BackendInvoiceCostumerController::class, 'taxfee']);
    Route::resource('invoiceldo', BackendInvoiceLdoController::class);
    Route::resource('invoiceusageitems', BackendInvoiceUsageItemController::class);
    Route::resource('invoiceusageitemsoutside', BackendInvoiceUsageItemOutsideController::class);
    Route::resource('invoiceusageitems', BackendInvoiceUsageItemController::class);
    Route::resource('invoicekasbons', BackendInvoiceKasbonController::class);
    Route::resource('paymentldo', BackendPaymentLdoController::class);
    Route::resource('opnames', BackendOpnameController::class);

    Route::get('dashboard', BackendDashboardController::class);
    Route::resource('activitylog', BackendActivityLogController::class);

    //Report
    Route::resource('reportsparepart', BackendReportSparepartController::class);
    Route::resource('kasbon', BackendKasbonController::class);
    Route::get('reportcostumers', [BackendReportCostumerController::class, 'index'])->name('reportcostumers.index');
    Route::get('reportdrivers', [BackendReportDriverController::class, 'index'])->name('reportdrivers.index');
    Route::get('reportkasbondrivers', [BackendReportKasbonDriverController::class, 'index'])->name('reportkasbondrivers.index');
    Route::get('reporttransports', [BackendReportTransportController::class, 'index'])->name('reportransports.index');
    Route::get('reportjoborders', [BackendReportJoborderController::class, 'index'])->name('reportjoborders.index');
    Route::get('reportrecapjoborders', [BackendReportRecapJobOrderController::class, 'index'])->name('reportrecapjoborders.index');
    Route::get('reportrecapsalaries', [BackendReportRecapSalaryController::class, 'index'])->name('reportrecapsalaries.index');
    Route::get('reportsalarydrivers', [BackendReportSalaryController::class, 'index'])->name('reportsalarydrivers.index');
    Route::get('reportinvoicecostumers', [BackendReportInvoiceCostumer::class, 'index'])->name('reportinvoicecostumers.index');
    Route::get('reportinvoiceldo', [BackendReportInvoiceLdoController::class, 'index'])->name('reportinvoiceldo.index');
    Route::get('reportkasbonemployees', [BackendReportKasbonEmployeeController::class, 'index'])->name('reportkasbonemployees.index');
    Route::get('reportsalaryemployees', [BackendReportSalaryEmployeesController::class, 'index'])->name('reportsalaryemployees.index');
    Route::get('reportpurchaseorders', [BackendReportPurchaseOrderController::class, 'index'])->name('reportpurchaseorders.index');
    Route::get('reportrecappurchaseorders', [BackendReportRecapPurchaseOrderController::class, 'index'])->name('reportrecappurchaseorders.index');
    Route::get('reportreturpurchases', [BackendReportReturPurchaseController::class, 'index'])->name('reportreturpurchases.index');
    Route::get('reportrecapreturpurchases', [ReportRecapReturPurchaseController::class, 'index'])->name('reportrecapreturpurchases.index');
    Route::get('reportusageitems', [BackendReportUsageItemsController::class, 'index'])->name('reportusageitems.index');
    Route::get('reportrecapusageitems', [BackendReportRecapUsageItemsController::class, 'index'])->name('reportrecapusageitems.index');
    Route::get('reportusageitemoutside', [BackendReportUsageItemOutsideController::class, 'index'])->name('reportusageitemoutside.index');
    Route::get('reportrecapusageitemoutside', [BackendReportRecapUsageItemOutsideController::class, 'index'])->name('reportrecapusageitemoutside.index');
    Route::get('reportstocks', [BackendReportStockController::class, 'index'])->name('reportstocks.index');
    Route::get('reportcustomerroadmoney', [BackendReportCustomerRoadMoneyController::class, 'index'])->name('reportcustomerroadmoney.index');
    Route::get('reportldonetprofit', [BackendReportLdoNetProfitController::class, 'index'])->name('reportldonetprofit.index');
    Route::resource('mastercoa', BackendCoaController::class);
    Route::resource('journals', BackendJournalController::class);
    Route::resource('configcoa', BackendConfigCoaController::class);
    Route::resource('configledger', BackendConfigLedgerController::class);
    Route::resource('necarabalane', BackendNeracaBalanceController::class);
    Route::resource('ledger', BackendReportLedgerController::class);
    Route::resource('ledgeroperational', BackendReportLedgerOperationalController::class);
    Route::resource('ledgersparepart', BackendReportLedgerSparePartControllerlController::class);
    Route::resource('ledgeraccounting', BackendReportLedgerAccountingController::class);
    Route::resource('finance', BackendReportFinanceController::class);
    Route::resource('neraca', BackendReportNeracaBalanceController::class);
    Route::resource('profitloss', BackendReportProfitLossController::class);
    Route::resource('reportpiutanglunas', Backend\ReportPiutangLunasController::class);
    Route::resource('reportpiutangbelumlunas', Backend\ReportPiutangBelumLunasController::class);
    Route::resource('completepurchaseorder', Backend\CompletePurchaseOrderController::class);
    Route::resource('reportusageinsideoutside', Backend\ReportUsageItemInsideOutsideController::class);
  });
});
