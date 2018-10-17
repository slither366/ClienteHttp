<?php
/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', ['as'=>'home',function() {
	return view('principal');
}]);

Route::get('/prueba','Controller@obtenerAccessToken');

Route::get('/data','Controller@test');

Route::get('/pruebaquery','Controller@prueba');

Route::get('/estudiantes/agregar', ['as'=>'addEstudiante', 'uses' => 'Controller@agregarEstudiante']);

Route::post('/estudiantes/agregar', ['as'=>'addEstudiante', 'uses' => 'Controller@prueba']);