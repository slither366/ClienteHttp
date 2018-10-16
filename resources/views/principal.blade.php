@extends('layouts.master')

@section('contenido')
<ul class="list-group">
	<li href="#" class="list-group-item">Obtener todos los Estudiantes</li>
	<li href="#" class="list-group-item">Obtener todos los Profesores</li>
	<li href="#" class="list-group-item">Obtener todos los Cursos</li>
</ul>
<ul class="list-group">
	<li class="list-group-item">Obtener un los Estudiantes</li>
	<li class="list-group-item">Obtener un los Estudiantes</li>
	<li class="list-group-item">Obtener un los Estudiantes</li>
</ul>

<ul class="list-group">
	<li href="{{ route('addEstudiante') }}" class="list-group-item">Agregar Nuevo Student</li>
</ul>		

@endsection