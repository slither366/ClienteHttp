<?php

namespace ClienteHTTP\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use \stdClass;
use \PDO;

use GuzzleHttp\Client;
use DB;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	/*==============================================================
	=            Función para iniciar Pertición de APIS            =
	==============================================================*/
	protected function realizarPeticion($metodo,$url,$parametros=[]){
		$cliente = new Client(['curl' => [CURLOPT_CAINFO => base_path('resources/certs/cacert.pem')]]);

		$respuesta = $cliente->request($metodo,$url,$parametros);

		return $respuesta->getBody()->getContents();
	}

	/*========================================================================
	=            Example Obtener llave con Datos de configuración            =
	========================================================================*/
	protected function obtenerAccessToken(){
		$clientId = config('api.client_id');
		$clientSecret = config('api.client_secret');
		$grantType = config('api.grant_type');

		$respuesta = json_decode(
			$this->realizarPeticion('POST','https://apilumen.juandmegon.com/oauth/access_token',
				['form_params'=>
				[
					'grant_type'=>$grantType,
					'client_id'=>$clientId,
					'client_secret'=>$clientSecret
				]
			])
		);

		$accessToken = $respuesta->access_token;

		return $accessToken;
	}

	/*========================================================
	=            Función Vaciar Tablas en la Nube            =
	========================================================*/
	protected function vaciarTabla($rutaApi){

		ini_set('max_execution_time', 0);

		/*=====  Deleteando Tabla  ======*/
		$respuesta = $this->realizarPeticion('DELETE',env('URL_LOCAL').'/'.$rutaApi, ['form_params'=>[]]);		

	}	

	/*========================================================
	=            Example Execute Procedure Oracle            =
	========================================================*/
	public function pruebaProcedureHttp(){

		$pdo = DB::getPdo();
		$p1 = '722';
		$p2 = '722102018PEN091631805-10-18 13:20:15';

		$stmt = $pdo->prepare("begin pkg_proy_agil.sp_upd_depotarde_up(:pcod, :pKey); end;");
		$stmt->execute(['pcod' => $p1,'pKey' => $p2,]);

		return 'Updated!';
	}	

	public function pruebaFunctionHttp(){
		// via Query Builder
		$query= DB::select("SELECT f_prueba('lenguaje') AS mfrc FROM dual");

		$uname=json_encode($query);
		$array=json_decode($uname);

		return $array;
	}		

	public function testOracle(){
		$users=DB::select("SELECT * FROM int_recep_prod_qs WHERE rownum=1");
		var_dump($users);
	}

	/*=======================================================
	=            Prueba Obtener Depositos de Oracle         =
	=======================================================*/
	public function getAllDepositosOracle(){

		$query=DB::select(DB::raw("
			SELECT tod.* 
			FROM(
			SELECT td.cod_local, td.mes_periodo, td.ano_periodo,/* td.dia_cierre,*/ 
			TO_CHAR(td.fecha_cierre_dia,'YYYY-mm-dd') fecha_cierre_dia, td.dia_op_banc, 
			TO_CHAR(td.fecha_op_bancaria,'YYYY-mm-dd hh24:mi:ss') fecha_op_bancaria, td.dif_min, 
			td.cant_dias, td.moneda, td.monto_deposito, td.num_operacion, 
			td.usuario, td.mon_tot_perdido, td.estado_cuadratura
			FROM TB_DEPOSITO_TARDE td
			WHERE COD_LOCAL = 'A00'
			AND CANT_DIAS <> '0'
			ORDER BY COD_LOCAL,MES_PERIODO,FECHA_CIERRE_DIA
			) tod
			"));

		return $query;
	}	

	/*=======================================================
	=            POST REGISTRAR DEPOSITOS TARDES            =
	========================================================*/
	public function registrarDepositosTardes(){
		ini_set('max_execution_time', 0);

		$query = DB::select(DB::raw("select pkg_proy_agil.f_depositos_tarde('N') from dual"));

		foreach ($query as $valor) {

			$pdo = DB::getPdo();
			$codLoca = "";
			$Key = "";

			$rptStado = $this->realizarPeticion('GET',env('URL_LOCAL').'/api/getLlave/'.$valor->llave_dif);
			//$rptStado = $this->realizarPeticion('GET','http://3.16.73.131:81/api/getLlave/'.$valor->llave_dif);

			if($rptStado == 'true'){

				/*---------- Si Deposito Tarde existe en la Nube se Actualiza el campo UP_CLOUD=S ----------*/
				if($valor->up_cloud<>'S'){
					$codLocal = $valor->cod_local;
					$Key = $valor->llave_dif;

					$stmt = $pdo->prepare("begin pkg_proy_agil.sp_upd_depotarde_up(:pcod, :pKey); end;");
					$stmt->execute(['pcod' => $codLocal,'pKey' => $Key,]);					
				}

			}else{

				/*---------- Si No existe Deposito Tarde se Registra en la Nube y actualizamos UP_CLOUD=S ----------*/
				$respuesta = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/DepositoTarde',
				//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',					
					['form_params'=>//$request->all()
						[	'cod_local'=>$valor->cod_local,
							/*'mes_periodo'=>$valor->mes_periodo,
							'ano_periodo'=>$valor->ano_periodo,
							'dia_cierre'=>$valor->dia_cierre,*/
							'fecha_cierre_dia'=>$valor->fecha_cierre_dia,
							//'fecha_cuadratura_cierre_dia'=>$valor->fecha_cuadratura_cierre_dia,
							//'dia_op_banc'=>$valor->dia_op_banc,
							'fecha_op_bancaria'=>$valor->fecha_op_bancaria,
							'status' => $valor->status,
							/*'dif_min'=>$valor->dif_min,
							'cant_dias'=>$valor->cant_dias,*/
							'moneda'=>$valor->moneda,
							'monto_deposito'=>$valor->monto_deposito,
							'num_operacion'=>$valor->num_operacion,
							'usuario'=>$valor->usuario,
							'mon_tot_perdido'=>$valor->mon_tot_perdido,
							'estado_cuadratura'=>$valor->estado_cuadratura,
							'llave_dif'=>$valor->llave_dif,
						]
					]
				);

				$codLocal = $valor->cod_local;
				$Key = $valor->llave_dif;

				$stmt = $pdo->prepare("begin pkg_proy_agil.sp_upd_depotarde_up(:pcod, :pKey); end;");
				$stmt->execute(['pcod' => $codLocal,'pKey' => $Key,]);				
			}

		}

		return "Se Actualizó las Tablas Satisfactoriamente";	
	}

	/*==============================================================================
	=            POST REVISAR DEPOSITOS EN ESTADO S NO REGISTRADOS                 =
	==============================================================================*/
	public function revisarDepositos(){

		ini_set('max_execution_time', 0);

		$query = DB::select(DB::raw("select pkg_proy_agil.f_depositos_tarde('S') from dual"));

		foreach ($query as $valor) {

			$pdo = DB::getPdo();
			$codLoca = "";
			$Key = "";

			$rptStado = $this->realizarPeticion('GET',env('URL_LOCAL').'/api/getLlave/'.$valor->llave_dif);
			//$rptStado = $this->realizarPeticion('GET','http://3.16.73.131:81/api/getLlave/'.$valor->llave_dif);

			if($rptStado == 'true'){

				if($valor->up_cloud<>'S'){
					$codLocal = $valor->cod_local;
					$Key = $valor->llave_dif;

					$stmt = $pdo->prepare("begin pkg_proy_agil.sp_upd_depotarde_up(:pcod, :pKey); end;");
					$stmt->execute(['pcod' => $codLocal,'pKey' => $Key,]);					
				}

			}else{

				$respuesta = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/DepositoTarde',
				//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',					
					['form_params'=>//$request->all()
						[	'cod_local'=>$valor->cod_local,
							/*'mes_periodo'=>$valor->mes_periodo,
							'ano_periodo'=>$valor->ano_periodo,
							'dia_cierre'=>$valor->dia_cierre,*/
							'fecha_cierre_dia'=>$valor->fecha_cierre_dia,
							//'fecha_cuadratura_cierre_dia'=>$valor->fecha_cuadratura_cierre_dia,
							//'dia_op_banc'=>$valor->dia_op_banc,
							'fecha_op_bancaria'=>$valor->fecha_op_bancaria,
							'status' => $valor->status,
							/*'dif_min'=>$valor->dif_min,
							'cant_dias'=>$valor->cant_dias,*/
							'moneda'=>$valor->moneda,
							'monto_deposito'=>$valor->monto_deposito,
							'num_operacion'=>$valor->num_operacion,
							'usuario'=>$valor->usuario,
							'mon_tot_perdido'=>$valor->mon_tot_perdido,
							'estado_cuadratura'=>$valor->estado_cuadratura,
							'llave_dif'=>$valor->llave_dif,
						]
					]
				);

				$codLocal = $valor->cod_local;
				$Key = $valor->llave_dif;

				$stmt = $pdo->prepare("begin pkg_proy_agil.sp_upd_depotarde_up(:pcod, :pKey); end;");
				$stmt->execute(['pcod' => $codLocal,'pKey' => $Key,]);				
			}

		}

		return "Se Actualizó las Tablas Satisfactoriamente";	
	}	

	/*================================================
	=            POST Registro de Locales            =
	================================================*/
	public function registrarLocales(){

		ini_set('max_execution_time', 0);
		
		/*=====  Deleteando la Tabla locales_dets  ======*/
		$this->vaciarTabla('api/local/todos');

		//getLocalExiste($cod_local)

		$query = DB::select(DB::raw("select pkg_proy_agil.F_LOCALES_DET from dual"));

		foreach ($query as $valor) {

			$rptStado = $this->realizarPeticion('GET',env('URL_LOCAL').'/api/local/verificaLocal/'.$valor->cod_local);

			if($rptStado == 'true'){

			}else{

				$respuesta = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/local',
						//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',
					[
						'form_params'=>
						[	
							'cod_local'=>$valor->cod_local,
							'cod_cia'=>$valor->cod_cia,
							'descripcion'=>$valor->desc_corta_local,
							'mail_local'=>$valor->mail_local,
							'cod_zona'=>$valor->cod_zona_vta,
						]						
					]
				);

			}

		}

		return "Se Actualizó la tabla de Locales en la Nube.";
	}

	/*======================================================
	=            POST Registro de Jefes Zonales            =
	======================================================*/
	public function registrarJefeZonal(){

		ini_set('max_execution_time', 0);

		/*=====  Deleteando la Tabla jefezonals  ======*/
		$this->vaciarTabla('api/jefezonal/todos');
		
		$query = DB::select(DB::raw("select pkg_proy_agil.f_datos_jefezonal from dual"));

		foreach ($query as $valor) {

			$rptStado = $this->realizarPeticion('GET',env('URL_LOCAL').'/api/jefezonal/verificaJefe/'.$valor->dni_jefezona);

			if($rptStado == 'true'){

			}else{

				$respuesta = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/jefezonal',
						//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',
					[
						'form_params'=>
						[
							'dni_jefezona'=>$valor->dni_jefezona,
							'nom_jefezona'=>$valor->nom_jefezona,
							'mail_jefezona'=>$valor->mail_jefezona,
							'dni_subgerente'=>$valor->dni_subgerente,
							'nom_subgerente'=>$valor->nom_subgerente,
							'mail_subgerente'=>$valor->mail_subgerente,
						]
					]
				);

			}

		}

		return "Se Actualizó la tabla de Jefes Zonales en la Nube.";
	}

	/*============================================================
	=            POST Grabar Jefes x Local          		     =
	============================================================*/
	public function registrarJefesxlocal(){

		ini_set('max_execution_time', 0);

		/*=====  Deleteando la Tabla locales_jefezonals  ======*/
		$this->vaciarTabla('api/jefexlocal/todos');

		$query = DB::select(DB::raw("select pkg_proy_agil.f_locales_jefezonal from dual"));

		foreach ($query as $valor) {

			$rptStado = $this->realizarPeticion('GET',env('URL_LOCAL').'/api/jefexlocal/verificaJefexlocal/'.$valor->cod_local);

			if($rptStado == 'true'){

			}else{

				$respuesta = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/jefexlocal',
						//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',
					[
						'form_params'=>
						[
							'cod_local'=>$valor->cod_local,
							'dni_jefe_zona'=>$valor->dni_jefezonal,
						]
					]
				);

			}

		}

		return "Se Actualizó la tabla de Jefes Zonales en la Nube.";
	}

	/*========================================================
	=            POST Grabar Depositos Pendientes            =
	========================================================*/
	public function postDepositosPendientes(){

		ini_set('max_execution_time', 0);

		/*=====  Deleteando la Tabla Deposito Pendiente  ======*/
		$this->vaciarTabla('api/DepositoPendiente/todos');

		/*=====  Insertando nuevos Datos en Deposito Pendiente Mysql  ======*/
		$query = DB::select(DB::raw("select pkg_proy_agil.f_depositos_pendiente from dual"));
		
		foreach ($query as $valor) {

			$respuesta2 = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/DepositoPendiente',
					//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',
				[
					'form_params'=>
					[
						'cod_local'=>$valor->cod_local,
						'dia_cierre'=>$valor->dia_cierre,
						'fecha_mes'=>$valor->fecha_mes,
					]
				]
			);

		}
		
		return "Se Actualizó Depositos Pendientes en la Nube.";
	}

	/*===========================================================================
	=            POST Registro de Transferencias Pendientes Cabecera            =
	===========================================================================*/
	public function registrarTransfPendCab(){

		ini_set('max_execution_time', 0);
		
		/*=====  Deleteando la Tabla Transferencias Cabecera  ======*/
		$this->vaciarTabla('api/transferenciasCab/todos');
										
		$query = DB::select(DB::raw("select pkg_proy_agil.F_CAB_TRANSFERENCIA_PEND from dual"));

		foreach ($query as $valor) {

			$respuesta = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/transferenciasCab',
					//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',
				[
					'form_params'=>
					[	
						'cod_local'=>$valor->cod_local,
						'num_nota_es'=>$valor->num_nota_es,
						'num_guia_rem'=>$valor->num_guia_rem,
						'cod_origen_nota_es'=>$valor->cod_origen_nota_es,
						'cod_destino_nota_es'=>$valor->cod_destino_nota_es,
						'fec_crea_nota_es_cab'=>$valor->fec_crea_nota_es_cab,
					]						
				]
			);

		}

		return "Se Actualizó la tabla Transferencias Pendientes Cabecera en la Nube.";
	}

	/*===========================================================================
	=            POST Registro de Transferencias Pendientes Detalle             =
	===========================================================================*/
	public function registrarTransfPendDet(){

		ini_set('max_execution_time', 0);
		
		/*=====  Deleteando la Tabla Transferencias Detalle  ======*/
		$this->vaciarTabla('api/transferenciasDet/todos');

		$query = DB::select(DB::raw("select pkg_proy_agil.F_DET_TRANSFERENCIA_PEND from dual"));

		foreach ($query as $valor) {

			$respuesta = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/transferenciasDet',
					//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',
				[
					'form_params'=>
					[	
						'cod_local'=>$valor->cod_local,
						'num_nota_es'=>$valor->num_nota_es,
						'sec_det_nota_es'=>$valor->sec_det_nota_es,
						'cod_prod'=>$valor->cod_prod,
						'cant_mov'=>$valor->cant_mov,
						'val_frac'=>$valor->val_frac,
						'fec_nota_es_det'=>$valor->fec_nota_es_det,
					]						
				]
			);

		}

		return "Se Actualizó la tabla Transferencias Pendientes Detalle en la Nube.";
	}

	/*================================================================
	=            POST Registro de Remesas Tardes Detalle             =
	=================================================================*/
	public function registrarRemesasTarde(){
		ini_set('max_execution_time', 0);

		$query = DB::select(DB::raw("select pkg_proy_agil.f_remesas_tarde('N') from dual"));

		foreach ($query as $valor) {

			$pdo = DB::getPdo();
			$codLoca = "";
			$Key = "";

			$rptStado = $this->realizarPeticion('GET',env('URL_LOCAL').'/api/remesasTardes/getLlave/'.$valor->llave_dif);
			//$rptStado = $this->realizarPeticion('GET','http://3.16.73.131:81/api/getLlave/'.$valor->llave_dif);

			if($rptStado == 'true'){

				/*---------- Si Deposito Tarde existe en la Nube se Actualiza el campo UP_CLOUD=S ----------*/
				if($valor->up_cloud<>'S'){
					$codLocal = $valor->cod_local;
					$Key = $valor->llave_dif;

					$stmt = $pdo->prepare("begin pkg_proy_agil.sp_upd_remesatarde_up(:pcod, :pKey); end;");
					$stmt->execute(['pcod' => $codLocal,'pKey' => $Key,]);					
				}

			}else{

				/*---------- Si No existe Deposito Tarde se Registra en la Nube y actualizamos UP_CLOUD=S ----------*/
				$respuesta = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/remesasTardes',
				//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',					
					['form_params'=>//$request->all()
						[	
							'cod_local'=>$valor->cod_local,
							'cod_remito'=>$valor->cod_remito,
							'fecha_creacion_sobre'=>$valor->fecha_creacion_sobre,
							'fecha_consignada'=>$valor->fecha_consignada,
							'fec_crea_remito'=>$valor->fec_crea_remito,
							'cant_dias'=>$valor->cant_dias,
							'dias_toca' => $valor->dias_toca,
							'dif_day'=>$valor->dif_day,
							'num_doc_ident_jefe_zona'=>$valor->num_doc_ident_jefe_zona,
							'monto'=>$valor->monto,
							'llave_dif'=>$valor->llave_dif,
						]
					]
				);

				$codLocal = $valor->cod_local;
				$Key = $valor->llave_dif;

				$stmt = $pdo->prepare("begin pkg_proy_agil.sp_upd_remesatarde_up(:pcod, :pKey); end;");
				$stmt->execute(['pcod' => $codLocal,'pKey' => $Key,]);
			}

		}

		return "Se Actualizó las Tablas Satisfactoriamente";	
	}

	/*================================================================
	=            POST Registro de Remesas Pendientes Detalle         =
	=================================================================*/
	public function registrarRemesasPendiente(){
		ini_set('max_execution_time', 0);
		
		/*=====  Deleteando la Tabla remesasPendientes  ======*/
		$this->vaciarTabla('api/remesasPendientes/todos');

		/*=====  Insertando nuevos Datos en Remesas Pendientes en Mysql  ======*/
		$query = DB::select(DB::raw("select pkg_proy_agil.F_REMESAS_PENDIENTE from dual"));
		
		foreach ($query as $valor) {

			$respuesta2 = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/remesasPendientes',
					//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',
				[
					'form_params'=>
					[
							'cod_local'=>$valor->cod_local,
							'fecha_creacion_sobre'=>$valor->fecha_creacion_sobre,
							'fecha_consignada'=>$valor->fecha_consignada,
							'cant_dias'=>$valor->cant_dias,
							'dias_toca' => $valor->dias_toca,
							'dif_day'=>$valor->dif_day,
							'num_doc_ident_jefe_zona'=>$valor->num_doc_ident_jefe_zona,
							'monto'=>$valor->monto,
					]
				]
			);

		}
		
		return "Se Actualizó Depositos Pendientes en la Nube.";
	}

	/*==============================================
	=            POST Registro de Usuarios         =
	==============================================*/
	public function registrarUsuariosZonales(){
		ini_set('max_execution_time', 0);

		/*=====  Insertando nuevos Datos en Remesas Pendientes en Mysql  ======*/
		$query = DB::select(DB::raw("select pkg_proy_agil.F_USUARIOS_ZONALES from dual"));
		
		foreach ($query as $valor) {

			$respuesta2 = $this->realizarPeticion('POST',env('URL_LOCAL').'/api/users',
					//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',
				[
					'form_params'=>
					[
							'name'=>$valor->name,
							'email'=>$valor->email,
							'tipo_usuario'=>$valor->tipo_usuario,
							'dni'=>$valor->dni,
							'password' => $valor->password,
							'password_confirmation'=>$valor->password_confirmation,
					]
				]
			);

		}
		
		return "Se Actualizaron los Usuarios en la Nube.";
	}


}
