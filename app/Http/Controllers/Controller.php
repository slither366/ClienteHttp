<?php

namespace ClienteHTTP\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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

	public function agregarEstudiante(){
		return view('estudiantes.agregar');
	}

	public function crearEstudiante(Request $request){
		//return $request->all();
		$accessToken = 'Bearer' . $this->obtenerAccessToken();

		$respuesta = $this->realizarPeticion('POST','https://apilumen.juandmegon.com/estudiantes',
				[
					'headers'=>['Authorization'=>$accessToken],
				 	'form_params'=>$request->all()
				]

		);

		return redirect('/');
	}

	public function test(){
		$users=DB::select('select * from int_recep_prod_qs where rownum=1');
		var_dump($users);
	}


}
