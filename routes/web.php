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
    return view('innobooks');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/suppliers/import', 'SupplierController@import')->name('suppliers.import');
Route::post('/suppliers/upload', 'SupplierController@upload')->name('suppliers.upload');
Route::get('/products/import', 'ProductController@import')->name('products.import');
Route::post('/products/upload', 'ProductController@upload')->name('products.upload');
Route::post('/received_payments/ajax-request', 'AjaxController@store');
Route::post('/creditnote/getinvoice', 'AjaxCNController@getInvoice');
Route::post('/creditnote/getamounts', 'AjaxCNController@getAmounts');
Route::post('/suppliercredit/getdocument', 'AjaxSCController@getDocument');
Route::post('/suppliercredit/getamounts', 'AjaxSCController@getAmounts');
Route::post('/inventory_qty_adjs/getquantities', 'AjaxInvQtyAdjController@getQuantities');

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
    'suppliercredit' => 'SupplierCreditController',
    'inventory_qty_adjs' => 'InventoryQtyAdjController',
    'line_items' => 'LineItemController',
]);

Route::resource('queries', 'QueryController');

Route::post('/queries/{query}/run', 'QueryController@run')->name('queries.run');
Route::post('/reports/{query}/screen', 'ReportController@screen')->name('reports.screen');
Route::post('/reports/{query}/pdf', 'ReportController@pdf')->name('reports.pdf');
Route::post('/reports/{query}/csv', 'ReportController@csv')->name('reports.csv');
Route::post('/reports/{query}/run', 'ReportController@run')->name('reports.run');
Route::post('/reports/trial_balance', 'ReportController@trialBalance')->name('reports.trial_balance');
Route::post('/reports/comprehensive_income', 'ReportController@comprehensiveIncome')->name('reports.comprehensive_income');
Route::post('/reports/run_comprehensive_income', 'ReportController@runComprehensiveIncome')->name('reports.run_comprehensive_income');
Route::get('/reports/financial_position', 'ReportController@financialPosition')->name('reports.financial_position');
Route::post('/reports/run_financial_position', 'ReportController@runFinancialPosition')->name('reports.run_financial_position');
Route::get('/reports/changes_in_equity', 'ReportController@changesInEquity')->name('reports.changes_in_equity');
Route::post('/reports/run_changes_in_equity', 'ReportController@runChangesInEquity')->name('reports.run_changes_in_equity');
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
