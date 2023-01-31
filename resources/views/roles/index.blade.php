@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Roles</h3>
        </div>
        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                        @if ($errors->any())
                    <div class="alert alert-dark alert-dimissible fade show" role="alert">
                        <span>
                            @foreach ($errors->all() as $error)
                                <li class="badge badge-danger">{{ $error }}</li>
                            @endforeach

                            <button type="button" class="close" data-dismiss="alert" arial-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </span>
                    </div>
                @endif

                @if(session()->has('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        <button type="button" class="close" data-dismiss="alert"></button>
                        {{ session()->get('message') }}
                    </div>
                @endif
                        @can('crear-rol')
                            <div align="right"><a href="{{ route('roles.create') }}" class="btn btn-secondary btn-lg active"><i class="fas fa-plus-circle fa-2x"></i></a></div>
                        @endcan

<hr>
<table width="100%" class="table table-striped mt-2">
<thead style='background-color: #6777ef;'>
            
   <tr>
   <th style='color:#fff;'>Rol</th>
   <th style='color:#fff;'>Usuarios</th>
   <th style='color:#fff;' colspan="2">Acciones</th>
   </tr>
</thead>
	<tr>
@foreach($roles as $rol)
	<td>{{ $rol->name }}</td>
	<td>@foreach($rol->users as $user)
		<i data-feather="check-square" class="align-text-bottom"></i> {{ $user->name }} <br>
		@endforeach
	</td>
    <td>
    <div class="btn-group" role="group">
    @can('editar-rol')
    <a class="btn btn-primary" href="{{ route('roles.edit',$rol->id) }}">
    <i class="fas fa-pencil-alt"></i></a> 
    @endcan 
    @can('borrar-rol')
	{!! Form::open(['route' => ['roles.destroy',$rol->id]]) !!}
		<input type="hidden" name="_method" value="DELETE">
        <!--<button class="btn btn-danger" onclick="fireSweetAlert()">-->
        <button class="btn btn-danger" onclick="return confirm('Eliminar ?')">
        <i class="fas fa-trash-alt"></i>
        </button>
	{!! Form::close() !!}
	@endcan        
	</div>
    </td>
	</tr>
@endforeach
	
</table>
<div class="pagination justify-content-end">
	{!! $roles->links() !!}
</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

