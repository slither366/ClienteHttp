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

Route::get('/estudiantes/agregar', ['as'=>'addEstudiante', 'EstudiantesController@agregarEstudiante']);

Route::post('/estudiantes/agregar', ['as'=>'addEstudiante', 'EstudiantesController@crearEstudiante']);

Route::get('/data','Controller@test');