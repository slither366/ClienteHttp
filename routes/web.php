<?php
/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', ['as'=>'home',function() {
	return view('principal');
}]);

/*==========================================================
=            Grabar Depositos Tardes en la Nube            =
==========================================================*/
Route::get('/postDepositosOnCloud','Controller@registrarDepositos');

/*=================================================
=            Grabar Locales en la Nube            =
=================================================*/
Route::get('/postLocalesOnCloud','Controller@registrarLocales');

/*=======================================================
=            Grabar Jefes Zonales en la Nube            =
=======================================================*/
Route::get('/postJefesOnCloud','Controller@registrarJefeZonal');

/*=======================================================
=            Grabar Jefes x Local en la Nube            =
=======================================================*/
Route::get('/postJefesxLocalOnCloud','Controller@registrarJefesxlocal');


