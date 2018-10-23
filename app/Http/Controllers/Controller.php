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

	protected function realizarPeticion($metodo,$url,$parametros=[]){
		$cliente = new Client(['curl' => [CURLOPT_CAINFO => base_path('resources/certs/cacert.pem')]]);

		$respuesta = $cliente->request($metodo,$url,$parametros);

		return $respuesta->getBody()->getContents();
	}

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

	public function crearEstudiante(Request $request){
		$accessToken = 'Bearer ' . $this->obtenerAccessToken();

		$respuesta = $this->realizarPeticion('POST','https://apilumen.juandmegon.com/estudiantes',
			[
				'headers'=>['Authorization'=>$accessToken],
				'form_params'=>$request->all()
			]

		);

		return redirect('/');
	}

	public function pruebaBucleHttp(Request $request){

		$query=DB::select(DB::raw("SELECT * FROM tb_alumno_http"));

		//$uname=json_encode($query);
		//$array=json_decode($uname);

		$accessToken = 'Bearer ' . $this->obtenerAccessToken();

		foreach ($query as $valor) {
			$respuesta = $this->realizarPeticion('POST','https://apilumen.juandmegon.com/estudiantes',
				[
					'headers'=>['Authorization'=>$accessToken],
					'form_params'=>//$request->all()
					[
						'nombre'=>$valor->nombre,
						'direccion'=>$valor->direccion,
						'telefono'=>$valor->telefono,
						'carrera'=>'matemática'
					]
				]
			);
		}

		return redirect('/');
	}

	public function pruebaProcedureHttp(){

		$pdo = DB::getPdo();
		$p1 = 'matematica';
		$p2 = 'filosofia';

		$stmt = $pdo->prepare("begin sp_prueba_http(:p1, :p2); end;");
		$stmt->bindParam(':p1', $p1, PDO::PARAM_INT);
		$stmt->bindParam(':p2', $p2, PDO::PARAM_INT);
		$stmt->execute();

		return redirect('/');
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

	public function agregarEstudiante(){
		return view('estudiantes.agregar');
	}

	public function getAllDepositosOracle(){

		//$query=DB::select(DB::raw("SELECT * FROM tb_alumno_http"));

		//$query= DB::select("
		$query=DB::select(DB::raw("
			SELECT tod.* 
			FROM(
			SELECT td.cod_local, td.mes_periodo, td.ano_periodo, td.dia_cierre, 
			       TO_CHAR(td.fecha_cierre_dia,'YYYY-mm-dd') fecha_cierre_dia, td.dia_op_banc, 
			       TO_CHAR(td.fecha_op_bancaria,'YYYY-mm-dd hh24:mi:ss') fecha_op_bancaria, td.dif_min, 
			       td.cant_dias, td.moneda, td.monto_deposito, td.num_operacion, 
			       td.usuario, td.mon_tot_perdido, td.estado_cuadratura
			FROM TB_DEP_BANK_PEND td
			WHERE COD_LOCAL = 'A00'
			AND CANT_DIAS <> '0'
			ORDER BY COD_LOCAL,MES_PERIODO,FECHA_CIERRE_DIA
			) tod
		"));

		//$uname=json_encode($query);
		//$array=json_decode($uname);

		return $query;

		//$accessToken = 'Bearer ' . $this->obtenerAccessToken();
/*
		foreach ($query as $valor) {

			$respuesta = $this->realizarPeticion('POST','http://127.0.0.1:8000/api/DepositoTarde',
				['form_params'=>//$request->all()
					[
						'cod_local'=>$valor->cod_local,
						'mes_periodo'=>$valor->mes_periodo,
						'ano_periodo'=>$valor->ano_periodo,
						'dia_cierre'=>$valor->dia_cierre,
						'fecha_cierre_dia'=>$valor->fecha_cierre_dia,
						'dia_op_banc'=>$valor->dia_op_banc,
						'fecha_op_bancaria'=>$valor->fecha_op_bancaria,
						'dif_min'=>$valor->dif_min,
						'cant_dias'=>$valor->cant_dias,
						'moneda'=>$valor->moneda
						'monto_deposito'=>$valor->monto_deposito,
						'num_operacion'=>$valor->num_operacion,
						'usuario'=>$valor->usuario,
						'mon_tot_perdido'=>$valor->mon_tot_perdido,
						'estado_cuadratura'=>$valor->estado_cuadratura,
					]
				]
			);
		}
*/
//		return redirect('/');
	}	

	public function postAllDepositos(){
		ini_set('max_execution_time', 0);

		$query=DB::select(DB::raw("
			SELECT tod.* 
			FROM(
			SELECT td.cod_local, td.mes_periodo, td.ano_periodo, td.dia_cierre, TO_CHAR(td.fecha_cierre_dia,'YYYY-mm-dd') fecha_cierre_dia,TO_CHAR(td.Fecha_Cuadratura_Cierre_Dia,'YYYY-mm-dd hh24:mi:ss') fecha_cuadratura_cierre_dia,
             	td.dia_op_banc,TO_CHAR(td.fecha_op_bancaria,'YYYY-mm-dd hh24:mi:ss') fecha_op_bancaria, td.dif_min, 
			    td.cant_dias, td.moneda, td.monto_deposito, td.num_operacion, td.usuario, td.mon_tot_perdido, 
             	td.estado_cuadratura, td.llave_dif
			FROM TB_DEP_BANK_PEND td
			WHERE COD_LOCAL = 'C51'
			AND CANT_DIAS <> '0'
			ORDER BY COD_LOCAL,MES_PERIODO,FECHA_CIERRE_DIA
			) tod
		"));

		foreach ($query as $valor) {

			$rptStado = $this->realizarPeticion('GET','http://127.0.0.1:8000/api/verPrueba/'.$valor->llave_dif);

			if($rptStado == 'true'){

			}else{

				$respuesta = $this->realizarPeticion('POST','http://127.0.0.1:8000/api/DepositoTarde',
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

			}

		}

		return "Se Actualizó las Tablas Satisfactoriamente";	
	}

}
