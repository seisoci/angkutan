<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Backend\UsersController as BackendUsersController;
use App\Http\Controllers\Backend\RolesController as BackendRolesController;
use App\Http\Controllers\Backend\MenusController as BackendMenusController;
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
use App\Http\Controllers\Backend\ServiceController as BackendServiceController;
use App\Http\Controllers\Backend\BrandController as BackendBrandController;
use App\Http\Controllers\Backend\CategoryController as BackendCategoryController;
use App\Http\Controllers\Backend\CashController as BackendCashController;
use App\Http\Controllers\Backend\BankController as BackendBankController;
use App\Http\Controllers\Backend\CompanyController as BackendCompanyController;
use App\Http\Controllers\Backend\TypeCapacityController as BackendTypeCapacityController;
use App\Http\Controllers\Backend\PrefixController as BackendPrefixController;
use App\Http\Controllers\Backend\StockController as BackendStockController;
use App\Http\Controllers\Backend\InvoicePurchaseController as BackendInvoicePurchaseController;
use App\Http\Controllers\Backend\SettingController as BackendSettingController;
use App\Http\Controllers\Backend\JobOrderController as BackendJobOrderController;
use App\Http\Controllers\Backend\OperationalExpenseController as BackendOperationalExpenseController;
use App\Http\Controllers\Backend\SalaryController as BackendSalaryController;
use App\Http\Controllers\Backend\RecapitulationController as BackendRecapitulationController;
use App\Http\Controllers\Backend\InvoiceSalaryController as BackendInvoiceSalaryController;
use App\Http\Controllers\Backend\InvoiceCostumerController as BackendInvoiceCostumerController;
use App\Http\Controllers\Backend\InvoiceLdoController as BackendInvoiceLdoController;
use App\Http\Controllers\Backend\PaymentLdoController as BackendPaymentLdoController;
use App\Http\Controllers\Backend\InvoiceUsageItemController as BackendInvoiceUsageItemController;
use App\Http\Controllers\Backend\InvoiceUsageItemOutsideController as BackendInvoiceUsageItemOutsideController;
use App\Http\Controllers\Backend\OpnameController as BackendOpnameController;
use App\Http\Controllers\Backend\InvoiceReturPurchaseController as BackendInvoiceReturPurchaseController;
use App\Http\Controllers\PagesController;
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

Route::get('backend', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('backend', [LoginController::class, 'login']);
Route::get('logout', [LoginController::class, 'logout'])->name('logout');
Route::get('/quick-search', [PagesController::class, 'quicksearch'])->name('quick-search');

Route::prefix('backend')->name('backend.')->middleware('auth:web')->group(function () {
  Route::group(['middleware' => ['role:super-admin|admin|employee']], function () {
    Route::post('resetpassword', [BackendUsersController::class,'resetpassword'])->name('users.resetpassword');
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
    Route::get('joborders/select2costumers', [BackendJobOrderController::class, 'select2costumers'])->name('joborders.select2costumers');
    Route::get('joborders/select2routefrom', [BackendJobOrderController::class, 'select2routefrom'])->name('joborders.select2routefrom');
    Route::get('joborders/select2routeto', [BackendJobOrderController::class, 'select2routeto'])->name('joborders.select2routeto');
    Route::get('joborders/select2cargos', [BackendJobOrderController::class, 'select2cargos'])->name('joborders.select2cargos');
    Route::get('anotherexpedition/select2', [BackendAnotherExpeditionController::class, 'select2'])->name('anotherexpedition.select2');
    Route::get('transports/select2', [BackendTransportController::class, 'select2'])->name('transports.select2');
    Route::get('transports/select2tonase', [BackendTransportController::class, 'select2tonase'])->name('transports.select2tonase');
    Route::get('transports/select2self', [BackendTransportController::class, 'select2self'])->name('transports.select2self');
    Route::get('typecapacities/select2', [BackendTypeCapacityController::class, 'select2'])->name('typecapacities.select2');
    Route::get('categories/select2', [BackendCategoryController::class, 'select2'])->name('categories.select2');
    Route::get('supplierspareparts/select2', [BackendSupplierSparepartController::class, 'select2'])->name('supplierspareparts.select2');
    Route::get('spareparts/select2', [BackendSparepartController::class, 'select2'])->name('spareparts.select2');
    Route::get('prefixes/select2', [BackendPrefixController::class, 'select2'])->name('prefixes.select2');
    Route::get('expenses/select2', [BackendExpenseController::class, 'select2'])->name('expenses.select2');
    Route::get('stocks/select2', [BackendStockController::class, 'select2'])->name('stocks.select2');

    //Datatables Details
    Route::get('invoicesalaries/datatabledetail/{id}', [BackendInvoiceSalaryController::class, 'datatabledetail'])->name('invoicesalaries.datatabledetail');
    Route::get('invoicecostumers/datatabledetail/{id}', [BackendInvoiceCostumerController::class, 'datatabledetail'])->name('invoicecostumers.datatabledetail');
    Route::get('invoiceldo/datatabledetail/{id}', [BackendInvoiceLdoController::class, 'datatabledetail'])->name('invoiceldo.datatabledetail');

    //Route Free
    Route::prefix('anotherexpedition')->name('anotherexpedition.')->group(function() {
      Route::get('{id}/create_driver/', [BackendAnotherExpeditionController::class, 'create_driver'])->name('create_driver');
      Route::get('{id}/create_transport/', [BackendAnotherExpeditionController::class, 'create_transport'])->name('create_transport');
      Route::get('{id}/datatable_transport/', [BackendAnotherExpeditionController::class, 'datatable_transport'])->name('datatable_transport');
      Route::get('{id}/datatable_driver/', [BackendAnotherExpeditionController::class, 'datatable_driver'])->name('datatable_driver');
    });
    Route::post('roadmonies/typecapacities', [BackendRoadMoneyController::class, 'typecapacities'])->name('roadmonies.typecapacities');
    Route::post('joborders/roadmoney', [BackendJobOrderController::class, 'roadmoney'])->name('joborders.roadmoney');
    Route::put('roadmonies/{id}/updatetypecapacities', [BackendRoadMoneyController::class, 'updatetypecapacities'])->name('roadmonies.updatetypecapacities');
    Route::get('invoicepurchases/{id}/cetakpdf', [BackendInvoicePurchaseController::class, 'cetakPdfInvoice'])->name('invoicepurchases.cetakpdf');
    Route::post('joborders/storeexpense', [BackendJobOrderController::class, 'storeexpense'])->name('joborders.storeexpense');
    Route::post('invoicesalaries/findbypk', [BackendInvoiceSalaryController::class, 'findbypk'])->name('invoicesalaries.findbypk');
    Route::post('invoicecostumers/findbypk', [BackendInvoiceCostumerController::class, 'findbypk'])->name('invoicecostumers.findbypk');
    Route::get('invoicepurchases/{id}/showpayment', [BackendInvoicePurchaseController::class, 'showpayment'])->name('invoicepurchases.showpayment');
    Route::get('recapitulation/excel', [BackendRecapitulationController::class, 'excel'])->name('recapitulation.excel');
    Route::get('recapitulation/print/{transport_id}/{driver_id}/{date_begin}/{date_end}', [BackendRecapitulationController::class, 'print']);

    //Master Operationals
    Route::resource('costumers', BackendCostumerController::class)->except(['create', 'edit', 'show']);
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
    Route::resource('services', BackendServiceController::class)->except(['create', 'edit', 'show']);
    Route::resource('brands', BackendBrandController::class)->except(['create', 'edit', 'show']);
    Route::resource('categories', BackendCategoryController::class)->except(['create', 'edit', 'show']);

    //Master Accounting
    Route::resource('prefixes', BackendPrefixController::class)->except(['create', 'edit', 'show']);
    Route::resource('cashes', BackendCashController::class)->except(['create', 'edit', 'show']);
    Route::resource('banks', BackendBankController::class)->except(['create', 'edit', 'show']);
    Route::resource('companies', BackendCompanyController::class)->except(['create', 'edit', 'show']);

    //Purchase
    Route::resource('invoicepurchases', BackendInvoicePurchaseController::class);
    Route::resource('invoicereturpurchases', BackendInvoiceReturPurchaseController::class);
    Route::resource('stocks', BackendStockController::class)->only('index');

    //Settings
    Route::resource('settings', BackendSettingController::class);

    //Job Order
    Route::resource('joborders', BackendJobOrderController::class);
    Route::resource('operationalexpenses', BackendOperationalExpenseController::class)->only(['store', 'destroy', 'index']);
    Route::resource('salaries', BackendSalaryController::class);
    Route::resource('recapitulation', BackendRecapitulationController::class);
    Route::resource('invoicesalaries', BackendInvoiceSalaryController::class);
    Route::resource('invoicecostumers', BackendInvoiceCostumerController::class);
    Route::resource('invoiceldo', BackendInvoiceLdoController::class);
    Route::resource('invoiceusageitems', BackendInvoiceUsageItemController::class);
    Route::resource('invoiceusageitemsoutside', BackendInvoiceUsageItemOutsideController::class);
    Route::resource('paymentldo', BackendPaymentLdoController::class);
    Route::resource('opnames', BackendOpnameController::class);
  });
});
