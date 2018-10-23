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

Route::get('/pruebaquery','Controller@pruebaProcedureHttp');

Route::get('/estudiantes/agregar', ['as'=>'addEstudiante', 'uses' => 'Controller@agregarEstudiante']);

Route::post('/estudiantes/agregar', ['as'=>'addEstudiante', 'uses' => 'Controller@prueba']);

Route::get('/pruebaMfa','Controller@getAllDepositosOracle');

Route::get('/postDepositosMfa','Controller@postAllDepositos');