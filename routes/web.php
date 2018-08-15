<?php

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

Route::get('/{id}/ftp', 'FtpBrowserController@ftpContent')->name('office_ftp_content');
Route::get('/{id}/ftp/credentials/admin', 'FtpBrowserController@setFTPCredentialsAdmin')->name('office_ftp_connection_admin');
Route::get('/{id}/ftp/credentials', 'FtpBrowserController@setFTPCredentials')->name('office_ftp_connection');
Route::get('/{id}/ftp/manager', 'FtpBrowserController@ftpManager')->name('office_ftp_manager');
Route::post('/{id}/ftp_store', 'FtpBrowserController@storeFTPCredentials')->name('office_ftp_store');
Route::post('/{id}/ftp_store_admin', 'FtpBrowserController@storeFTPCredentialsAdmin')->name('office_ftp_store_admin');
Route::post('/ajax_get_folder_content', 'FtpBrowserController@ajaxGetFolderContent')->name('ajax_get_folder_content');