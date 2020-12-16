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

Route::get('/suppliers/import', 'SupplierController@import')->name('suppliers.import');
Route::post('/suppliers/upload', 'SupplierController@upload')->name('suppliers.upload');
Route::get('/products/import', 'ProductController@import')->name('products.import');
Route::post('/products/upload', 'ProductController@upload')->name('products.upload');
Route::post('/received_payments/ajax-request', 'AjaxController@store');
Route::post('/creditnote/getinvoice', 'CreditNoteController@getInvoice');
Route::post('/creditnote/getamounts', 'CreditNoteController@getAmounts');

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
    'suppliers' => 'SupplierController',
    'products' => 'ProductController',
    'bills' => 'BillController',
    'customers' => 'CustomerController',
    'invoices' => 'InvoiceController',
    'received_payments' => 'ReceivedPaymentController',
    'sales_receipts' => 'SalesReceiptController',
    'received_payments' => 'ReceivedPaymentController',
    'creditnote' => 'CreditNoteController',
]);

Route::resource('queries', 'QueryController');

Route::post('queries/{query}/run', 'QueryController@run')->name('queries.run');
Route::post('reports/{query}/screen', 'ReportController@screen')->name('reports.screen');
Route::post('reports/{query}/pdf', 'ReportController@pdf')->name('reports.pdf');
Route::post('reports/{query}/csv', 'ReportController@csv')->name('reports.csv');
Route::post('reports/{query}/run', 'ReportController@run')->name('reports.run');
Route::post('reports/trial_balance', 'ReportController@trialBalance')->name('reports.trial_balance');
Route::get('/reports', 'ReportController@index')->name('reports.index');

Route::get('/search', 'SearchController@index')->name('search');

Route::get('/notifications', 'NotificationController@index')->name('notifications.index');
Route::delete('/notifications/{notification}', 'NotificationController@destroy')->name('notifications.destroy');

Route::group(['prefix' => 'messages'], function () {
    Route::get('/', ['as' => 'messages', 'uses' => 'MessagesController@index']);
    Route::get('create', ['as' => 'messages.create', 'uses' => 'MessagesController@create']);
    Route::post('/', ['as' => 'messages.store', 'uses' => 'MessagesController@store']);
    Route::get('{id}', ['as' => 'messages.show', 'uses' => 'MessagesController@show']);
    Route::put('{id}', ['as' => 'messages.update', 'uses' => 'MessagesController@update']);
    Route::delete('{id}', ['as' => 'messages.destroy', 'uses' => 'MessagesController@destroy']);
});
