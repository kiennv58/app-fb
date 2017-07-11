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

Route::get('/', [
	'as' => 'admin.index',
	'uses' => 'HomeController@index'	
])->middleware('auth');

Route::get('/group', [
	'as' => 'admin.group.post',
	'uses' => 'HomeController@post'
]);

Auth::routes();

// Route::get('/home', 'HomeController@index')->name('home');

Route::get('/redirect', [
	'as' => 'redirect',
	'uses' => 'SocialAuthController@redirect'
]);
Route::get('facebook/callback', 'SocialAuthController@callback');

