<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api/v1'], function () use ($router) {

    //public routing
    $router->group(['prefix' => 'user'], function () use ($router) {
        $router->post('/login', 'UsersController@login');
        $router->post('/register', 'UsersController@store');
        $router->post('/logout', ['middleware' => 'auth', 'uses' => 'UsersController@logout']);
        $router->get('/', 'UsersController@index');
        $router->get('/user', ['middleware' => 'auth', 'uses' => 'UsersController@user']);
        $router->post('/user/{id}', ['middleware' => 'auth', 'uses' => 'UsersController@update']);
        $router->get('/books/search','BooksController@search');
        
        $router->get('/kategori', 'KategoriController@index');
        $router->get('/book', 'BooksController@index');
        $router->get('/koleksi/{id}', ['middleware' => 'auth', 'uses' => 'KoleksiController@getByUser']);
        $router->post('/koleksi', ['middleware' => 'auth', 'uses' => 'KoleksiController@store']);
        $router->delete('/koleksi/{id}', 'KoleksiController@delete');
        $router->get('/ulasan/{id}', ['middleware' => 'auth', 'uses' => 'UlasanController@getByUser']);
        $router->delete('/ulasan/{id}', ['middleware' => 'auth', 'uses' => 'UlasanController@delete']);
        $router->post('/ulasan', ['middleware' => 'auth', 'uses' => 'UlasanController@store']);
        $router->get('/pinjam/{id}', ['middleware' => 'auth', 'uses' => 'PeminjamanController@getByUser']);
        $router->post('/pinjam', ['middleware' => 'auth', 'uses' => 'PeminjamanController@store']);
    });

    $router->group(['prefix' => 'petugas'], function () use ($router) {
        $router->post('/login', 'UsersController@loginPetugas');
        $router->post('/register', 'UsersController@store');
        $router->get('/user', ['middleware' => 'auth', 'uses' => 'UsersController@user']);
        $router->post('/user/{id}', ['middleware' => 'auth', 'uses' => 'UsersController@update']);
        
        $router->post('/book', 'BooksController@store');
        // $router->post('/book/{id}', 'BooksController@getByID');
        $router->delete('/book/{id}', 'BooksController@delete');
        $router->post('/book/{id}', 'BooksController@update');

        $router->get('/book/{id}', 'BooksController@getByID');
        $router->get('/book', 'BooksController@index');

        $router->get('/kategori', 'KategoriController@index');
        $router->post('/kategori', 'KategoriController@store');
        $router->delete('/kategori/{id}', 'KategoriController@delete');
        $router->get('/pinjam', 'PeminjamanController@index');
        $router->post('/pinjam/{id}', 'PeminjamanController@update');
        
    });

    $router->group(['prefix' => 'admin'], function () use ($router) {
        $router->post('/login', 'UsersController@LoginAdmin');
        $router->post('/register', 'UsersController@storeAdmin');
        $router->post('/logout', ['middleware' => 'auth', 'uses' => 'UsersController@logout']);
        $router->get('/user', ['middleware' => 'auth', 'uses' => 'UsersController@user']);
        $router->post('/user/{id}', ['middleware' => 'auth', 'uses' => 'UsersController@updateadmin']);
        $router->get('/book', ['middleware' => 'auth', 'uses' =>'BooksController@index']);
        $router->get('/Alluser', ['middleware' => 'auth', 'uses' => 'UsersController@Alluser']);
        $router->get('/user/search',['middleware' => 'auth', 'uses' =>'UsersController@search']);
    });

});
