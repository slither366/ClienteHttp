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
Route::get('/postDepositosTardesOnCloud','Controller@registrarDepositosTardes');

Route::get('/revisaDepositos','Controller@revisarDepositos');

/*==============================================================
=            Grabar Depositos Pendientes en la Nube            =
==============================================================*/
Route::get('/postDepositosPendientesOnCloud','Controller@postDepositosPendientes');

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

/*============================================================================
=            Grabar Transferencias Pendientes Cabecera en la Nube            =
============================================================================*/
Route::get('/postTransferenciasCabOnCloud','Controller@registrarTransfPendCab');

/*===========================================================================
=            Grabar Transferencias Pendientes Detalle en la Nube            =
===========================================================================*/
Route::get('/postTransferenciasDetOnCloud','Controller@registrarTransfPendDet');

/*============================================================
=            Grabar Remesas Tardes en la Nube                =
============================================================*/
Route::get('/postRemesasTardesOnCloud','Controller@registrarRemesasTarde');

/*============================================================
=            Grabar Remesas Tardes en la Nube                =
============================================================*/
Route::get('/postRemesasPendientesOnCloud','Controller@registrarRemesasPendiente');

/*============================================================
=        Grabar Usuarios Jefes Zonales en la Nube            =
============================================================*/
Route::get('/postUserJefesZonalesOnCloud','Controller@registrarUsuariosZonales');
