<?php

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
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::resources([
    'companies' => 'CompanyController',
    'applications' => 'ApplicationController',
    'current_company' => 'CurrentCompanyController',
    'company_users' => 'CompanyUserController',
    'abilities' => 'AbilityController',
    'roles' => 'RoleController',
    'accounts' => 'AccountController',
    'documents' => 'DocumentController',
    'subsidiary_ledgers' => 'SubsidiaryLedgerController',
    'report_line_items' => 'ReportLineItemController',
    'journal_entries' => 'JournalEntryController',
    'postings' => 'PostingController',
]);

// Admin
Route::get('/admin', function(){
    return view('admin.index');
});
Route::resource('/admin/users', 'AdminUsersController');

// BOwner
// Homepage
Route::get('/bowner', function(){
    return view('bowner.index');
});
// Human Resource route
Route::resource('/bowner/humans', 'BownerHumansController');
// Salary route
Route::post('/bowner/salaries', 'BownerSalariesController@index');
Route::post('/bowner/salaries/store', [
    'uses' => 'BownerSalariesController@store',
    'as' => 'salaries.store' ]);
Route::resource('/bowner/salaries', 'BownerSalariesController', ['except'=>['store']]);
// Leave route
Route::resource('/bowner/leaves', 'LeavesController');
// Chart route
Route::get('bowner/chart', [
    'uses' => 'ChartController@index',
    'as' =>'chart.index'
]);
// Customer route
Route::resource('bowner/customer', 'CustomerController', ['except'=>['store']]);
// Order route
Route::get('order_delete/{order_id}',[
    'uses' => 'OrderController@destroy',
    'as' => 'orders.destroy',
]);
Route::get('order_complete/{order_id}',[
    'uses' => 'OrderController@complete',
    'as' =>'orders.complete',
]);
Route::get('order_submit/{order_id}', [
    'uses' => 'OrderController@submit',
    'as' => 'orders.submit',
]);
Route::get('order_deliver/{order_id}', [
    'uses' => 'OrderController@deliver',
    'as' => 'orders.deliver',
]);

// Manager
Route::get('manager', function(){
    return view('bowner.index');
});
// Production route
Route::resource('bowner/production', 'ProductionController');
Route::get('bowner/complete_production/{o_qty}/{p_id}/{p_qty}',[
    'uses' => 'ProductionController@complete',
    'as' =>'production.complete',
]);
// Inventories route
Route::resource('bowner/inventories', 'InventoryController');
Route::get('bowner/inventories/product/edit/{product_id}', [
    'uses' => 'InventoryController@editProduct',
    'as' => 'inventories.product.edit'
]);
Route::get('bowner/inventories/material/edit/{material_id}', [
    'uses' => 'InventoryController@editMaterial',
    'as' => 'inventories.material.edit'
]);
Route::patch('bowner/inventories/product/update/{product_id}', [
    'uses' => 'InventoryController@updateProduct',
    'as' => 'inventories.product.update'
]);
Route::patch('bowner/inventories/material/update/{material_id}', [
    'uses' => 'InventoryController@updateMaterial',
    'as' => 'inventories.material.update'
]);
// Purchasing route
Route::resource('bowner/inventories/material/purchase', 'MaterialPurchaseController', ['except'=>'destroy']);
Route::get('bowner/inventories/material/purchase/delete/{purchase_id}', [
    'uses' => 'MaterialPurchaseController@destroy',
    'as' => 'purchase.destroy'
]);
Route::get('bowner/inventories/material/purchase/complete/{purchase_id}', [
    'uses' => 'MaterialPurchaseController@complete',
    'as' => 'purchase.complete'
]);
// Supplier route
Route::resource('bowner/supplier', 'SupplierController');

// Employee
// Order route
Route::resource('orders', 'OrderController', ['except'=>['destroy']]);
// Customer creation route
Route::get('customer/create', 'CustomerController@create');
Route::post('customer', 'CustomerController@store')->name('customer');
