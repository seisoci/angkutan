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
    Route::prefix('menus')->name('menus.')->group(function() {
      Route::post('change_hierarchy', [BackendMenusController::class,'change_hierarchy'])->name('change_hierarchy');
      Route::post('autocomplete ', [BackendMenusController::class,'autocomplete '])->name('autocomplete');
    });
    Route::get('costumers/select2', [BackendCostumerController::class, 'select2'])->name('costumers.select2');
    Route::get('cargos/select2', [BackendCargoController::class, 'select2'])->name('cargos.select2');
    Route::get('routes/select2', [BackendRouteController::class, 'select2'])->name('routes.select2');
    Route::get('brands/select2', [BackendBrandController::class, 'select2'])->name('brands.select2');
    Route::get('categories/select2', [BackendCategoryController::class, 'select2'])->name('categories.select2');
    Route::get('supplierspareparts/select2', [BackendSupplierSparepartController::class, 'select2'])->name('supplierspareparts.select2');
    Route::prefix('anotherexpedition')->name('anotherexpedition.')->group(function() {
      Route::get('{id}/create_driver/', [BackendAnotherExpeditionController::class, 'create_driver'])->name('create_driver');
      Route::get('{id}/create_transport/', [BackendAnotherExpeditionController::class, 'create_transport'])->name('create_transport');
      Route::get('{id}/datatable_transport/', [BackendAnotherExpeditionController::class, 'datatable_transport'])->name('datatable_transport');
      Route::get('{id}/datatable_driver/', [BackendAnotherExpeditionController::class, 'datatable_driver'])->name('datatable_driver');
    });
    Route::resource('menus', BackendMenusController::class)->except(['create', 'show']);
    Route::resource('costumers', BackendCostumerController::class)->except(['create', 'edit', 'show']);
    Route::resource('drivers', BackendDriverController::class);
    Route::resource('routes', BackendRouteController::class)->except(['create', 'edit', 'show']);
    Route::resource('cargos', BackendCargoController::class)->except(['create', 'edit', 'show']);
    Route::resource('transports', BackendTransportController::class)->except('show');
    Route::resource('roadmonies', BackendRoadMoneyController::class)->except('show');
    Route::resource('expenses', BackendExpenseController::class)->except(['create', 'edit', 'show']);
    Route::resource('anotherexpedition', BackendAnotherExpeditionController::class)->except(['create', 'edit']);
    Route::resource('supplierspareparts', BackendSupplierSparepartController::class)->except(['create', 'edit', 'show']);
    Route::resource('spareparts', BackendSparepartController::class)->except('show');
    Route::resource('services', BackendServiceController::class)->except(['create', 'edit', 'show']);
    Route::resource('brands', BackendBrandController::class)->except(['create', 'edit', 'show']);
    Route::resource('categories', BackendCategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('cashes', BackendCashController::class)->except(['create', 'edit', 'show']);
    Route::resource('banks', BackendBankController::class)->except(['create', 'edit', 'show']);
    Route::resource('companies', BackendCompanyController::class)->except(['create', 'edit', 'show']);
  });
});
