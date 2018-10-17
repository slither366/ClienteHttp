<?php

namespace ClienteHTTP\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use \stdClass;

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

	public function testOracle(){
		$users=DB::select("SELECT * FROM int_recep_prod_qs WHERE rownum=1");
		var_dump($users);
	}

	public function agregarEstudiante(){
		return view('estudiantes.agregar');
	}

}
