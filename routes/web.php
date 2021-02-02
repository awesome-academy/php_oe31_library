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

Route::get('/', 'Auth\LoginController@showLoginForm')->name('login');

Auth::routes();

Route::group(['middleware' => 'language'], function () {
    Route::group(['middleware' => 'auth'], function () {
        Route::group(['middleware' => 'admin'], function () {

            Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'as' => 'admin.'], function () {
                Route::get('dashboard', 'HomeController@index')->name('dashboard');
                Route::get('publishers/export', 'PublisherController@export')->name('publishers.export');
                Route::resources([
                    'roles' => 'RoleController',
                    'publishers' => 'PublisherController',
                    'users' => 'UserController',
                    'authors' => 'AuthorController',
                    'categories' => 'CategoryController',
                    'books' => 'BookController',
                ]);
                Route::get('search-user', 'UserController@search')->name('search-user');
                Route::post('api-store-category', 'CategoryController@apiStore')->name('api-store-category');
                Route::get('search-book', 'BookController@search')->name('search-book');
                Route::get('category-popup', 'BookController@catePopup')->name('category-popup');
                Route::get('request', 'RequestController@index')->name('request');
                Route::get('request-detail/{request}', 'RequestController@show')->name('request-detail');
                Route::get('accept/{request}', 'RequestController@accept')->name('accept');
                Route::get('reject/{request}', 'RequestController@reject')->name('reject');
                Route::get('undo/{request}', 'RequestController@undo')->name('undo');
                Route::get('borrowed-book/{request}', 'RequestController@borrowedBook')->name('borrowed-book');
                Route::get('return-book/{request}', 'RequestController@returnBook')->name('return-book');
                Route::get('book-delete', 'BookController@listDeleteBook')->name('book-delete');
                Route::delete('hard-delete/{book}', 'BookController@hardDelete')->name('hard-delete');
                Route::get('book-restore/{book}', 'BookController@restoreBook')->name('book-restore');
            });
        });
        Route::resource('comments', 'CommentController');
        Route::get('react/{book}', 'ReactionController@react')->name('react');
        Route::get('request', 'RequestController@index')->name('request');
        Route::get('request-detail/{request}', 'RequestController@show')->name('request-detail');
        Route::get('vote', 'ReactionController@vote')->name('vote');
        Route::post('request', 'RequestController@request')->name('request');
        Route::get('notify', 'NotificationController@index')->name('notify');
        Route::get('notify-for-user', 'NotificationController@apiGetUser')->name('notify-for-user');
    });

    Route::get('/', 'BookController@index')->name('home');
    Route::get('category-book/{categoryId}', 'BookController@getCategory')->name('category-book');
    Route::get('detail/{book}', 'BookController@getDetailBook')->name('detail');
    Route::get('change-language/{language}', 'ReactionController@changeLanguage')->name('change-language');
});

Route::get('/', 'BookController@index')->name('home');
Route::get('category-book/{categoryId}', 'BookController@getCategory')->name('category-book');
Route::get('detail/{book}', 'BookController@getDetailBook')->name('detail');
Route::get('cart', 'RequestController@cart')->name('cart');
Route::get('add-cart/{book}', 'RequestController@addToCart')->name('add-cart');
Route::get('remove-cart/{book}', 'RequestController@removeCart')->name('remove-cart');
Route::get('search-client-book', 'BookController@search')->name('search-client-book');
