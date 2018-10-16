@extends('layouts.master')

@section('contenido')
<form action="{{ route('addEstudiante') }}" method="POST" role=fomr>
	{{ csrf_field() }}
	<legend>Ingrese el Id del Estudiante</legend>

	<div class="form-group">
		<label form="">Nombre</label>
		<input type="text" class="form-control" name="nombre" required>
	</div>

	<div class="form-group">
		<label form="">Direccion</label>
		<input type="text" class="form-control" name="direccion" required>
	</div>

	<div class="form-group">
		<label form="">Telefono</label>
		<input type="text" class="form-control" name="telefono" required>
	</div>

	<div class="form-group">
		<label form="">Carrera</label>
		<select name="carrera" id="inputCarrera" class="form-control" required="required">
			<option>Seleccionar una Carrera</option>
			<option value="ingeniería">Ingenieria</option>
			<option value="matemática">Matematica</option>
			<option value="física">Fisica</option>
		</select>
	</div>


	<button type="submit" class="btn btn-primary">Crear</button>
</form>
@endsection