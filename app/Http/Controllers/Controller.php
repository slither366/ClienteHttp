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
	=            GET Obtener Depositos de Oracle            =
	=======================================================*/
	public function getAllDepositosOracle(){

		$query=DB::select(DB::raw("
			SELECT tod.* 
			FROM(
			SELECT td.cod_local, td.mes_periodo, td.ano_periodo, td.dia_cierre, 
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

	/*================================================
	=            POST REGISTRAR DEPOSITOS            =
	================================================*/
	public function registrarDepositos(){
		ini_set('max_execution_time', 0);

		$query = DB::select(DB::raw("select pkg_proy_agil.f_depositos_tarde from dual"));

		/*$query=DB::select(DB::raw("
			SELECT tod.* 
			FROM(
			SELECT td.cod_local, td.mes_periodo, td.ano_periodo, td.dia_cierre, TO_CHAR(td.fecha_cierre_dia,'YYYY-mm-dd') fecha_cierre_dia,TO_CHAR(td.Fecha_Cuadratura_Cierre_Dia,'YYYY-mm-dd hh24:mi:ss') fecha_cuadratura_cierre_dia,
			td.dia_op_banc,TO_CHAR(td.fecha_op_bancaria,'YYYY-mm-dd hh24:mi:ss') fecha_op_bancaria, td.dif_min, 
			td.cant_dias, td.moneda, td.monto_deposito, td.num_operacion, td.usuario, td.mon_tot_perdido, 
			td.estado_cuadratura, td.llave_dif
			FROM TB_DEP_BANK_PEND td
			WHERE 1=1--COD_LOCAL = 'A00'
			ORDER BY COD_LOCAL,MES_PERIODO,FECHA_CIERRE_DIA
			) tod
			"));*/

		foreach ($query as $valor) {

			$pdo = DB::getPdo();
			$codLoca = "";
			$Key = "";

			$rptStado = $this->realizarPeticion('GET','http://127.0.0.1:8001/api/getLlave/'.$valor->llave_dif);
			//$rptStado = $this->realizarPeticion('GET','http://3.16.73.131:81/api/getLlave/'.$valor->llave_dif);

			if($rptStado == 'true'){

				$codLocal = $valor->cod_local;
				$Key = $valor->llave_dif;

				$stmt = $pdo->prepare("begin pkg_proy_agil.sp_upd_depotarde_up(:pcod, :pKey); end;");
				$stmt->execute(['pcod' => $codLocal,'pKey' => $Key,]);

			}else{

				$respuesta = $this->realizarPeticion('POST','http://127.0.0.1:8001/api/DepositoTarde',
				//$respuesta = $this->realizarPeticion('POST','http://3.16.73.131:81/api/DepositoTarde',					
					['form_params'=>//$request->all()
						[	'cod_local'=>$valor->cod_local,
							'mes_periodo'=>$valor->mes_periodo,
							'ano_periodo'=>$valor->ano_periodo,
							'dia_cierre'=>$valor->dia_cierre,
							'fecha_cierre_dia'=>$valor->fecha_cierre_dia,
							'fecha_cuadratura_cierre_dia'=>$valor->fecha_cuadratura_cierre_dia,
							'dia_op_banc'=>$valor->dia_op_banc,
							'fecha_op_bancaria'=>$valor->fecha_op_bancaria,
							'dif_min'=>$valor->dif_min,
							'cant_dias'=>$valor->cant_dias,
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
		
		//getLocalExiste($cod_local)

		$query = DB::select(DB::raw("select pkg_proy_agil.F_LOCALES_DET from dual"));

		foreach ($query as $valor) {

			$rptStado = $this->realizarPeticion('GET','http://127.0.0.1:8001/api/local/verificaLocal/'.$valor->cod_local);

			if($rptStado == 'true'){

			}else{

				$respuesta = $this->realizarPeticion('POST','http://127.0.0.1:8001/api/local',
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
		
		$query = DB::select(DB::raw("select pkg_proy_agil.f_datos_jefezonal from dual"));

		foreach ($query as $valor) {

			$rptStado = $this->realizarPeticion('GET','http://127.0.0.1:8001/api/jefezonal/verificaJefe/'.$valor->dni_jefezona);

			if($rptStado == 'true'){

			}else{

				$respuesta = $this->realizarPeticion('POST','http://127.0.0.1:8001/api/jefezonal',
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
		
		$query = DB::select(DB::raw("select pkg_proy_agil.f_locales_jefezonal from dual"));

		foreach ($query as $valor) {

			$rptStado = $this->realizarPeticion('GET','http://127.0.0.1:8001/api/jefexlocal/verificaJefexlocal/'.$valor->cod_local);

			if($rptStado == 'true'){

			}else{

				$respuesta = $this->realizarPeticion('POST','http://127.0.0.1:8001/api/jefexlocal',
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

}
