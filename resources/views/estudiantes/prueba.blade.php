@extends('layouts.master')

@section('contenido')
<h1>Pagina prueba</h1>

<table>
	<THEAD>
		<tr>
			<td>CODGRUPOCIA</td>
			<td>CODLOCAL</td>
			<td>ENTREGA</td>
			<td>PRODUCTO</td>
		</tr>			
	</THEAD>
	<tbody>
		@foreach($query as $obj)
		<tr>
			<td>{{ $obj->cod_grupo_cia }}</td>
			<td>{{ $obj->cod_local }}</td>
			<td>{{ $obj->num_entrega }}</td>
			<td>{{ $obj->cod_prod }}</td>		
		</tr>		
		@endforeach		
</tbody>
</table>

@endsection