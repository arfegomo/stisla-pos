@extends('layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Crear producto</h3>
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

                        {!! Form::open(array('route' => 'productos.store','method'=>'POST')) !!}
                        
                        <div class="form-group row">
                            <label for="nombre" class="col-md-4 col-form-label text-md-right">{{ __('Nombre') }}</label>

                            <div class="col-md-6">
                                {!! Form::text('nombre',null,array(
                                    'class'=>'form-control',
                                    'required'=>'required',
                                    'placeholder'=>'Nombre'
                                )) !!}
                                @error('nombre')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                            <div class="form-group row">
                                <label for="categoria_id" class="col-md-4 col-form-label text-md-right">{{ __('Categoria') }}</label>
                                    <div class="col-md-6">
                                        {!! Form::select('categoria_id', $categorias,null, ['class' => 'form-control']) !!}
                                    </div>
                            </div>

                        <div class="form-group row">
                            <label for="precioventa1" class="col-md-4 col-form-label text-md-right">{{ __('Precio de venta # 1') }}</label>

                            <div class="col-md-6">
                                {!! Form::text('precioventa1',null,array(
                                    'class'=>'form-control',
                                    'placeholder'=>'Precio de venta # 1'
                                )) !!}

                                @error('precioventa1')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="precioventa2" class="col-md-4 col-form-label text-md-right">{{ __('Precio de venta # 2') }}</label>

                            <div class="col-md-6">
                                {!! Form::text('precioventa2',null,array(
                                    'class'=>'form-control',
                                    'placeholder'=>'Precio de venta # 2'
                                )) !!}

                                @error('precioventa2')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>
                         
                        <div class="form-group row">
                            <label for="faturable" class="col-md-4 col-form-label text-md-right">{{ __('Facturable') }}</label>

                            <div class="col-md-6">
                                {!! Form::select('facturable',["1" => "Si", "2" => "No"], null, array(
                                    'class'=>'form-control',
                                )) !!}

                                @error('facturable')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="habilita" class="col-md-4 col-form-label text-md-right">{{ __('Habilitar') }}</label>

                            <div class="col-md-6">
                                {!! Form::select('habilita',["1" => "Si", "2" => "No"], null, array(
                                    'class'=>'form-control',
                                )) !!}

                                @error('habilita')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="impuesto_id" class="col-md-4 col-form-label text-md-right">{{ __('Impuesto') }}</label>
                                <div class="col-md-6">
                                    {!! Form::select('impuesto_id', $impuestos,null, ['class' => 'form-control']) !!}
                                </div>
                        </div>

                        <div class="form-group row">
                            <label for="stockminimo" class="col-md-4 col-form-label text-md-right">{{ __('Stock mínimo') }}</label>

                            <div class="col-md-6">
                                {!! Form::text('stockminimo',null,array(
                                    'class'=>'form-control',
                                    'placeholder'=>'Stock mínimo'
                                )) !!}

                                @error('stockminimo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="stockmaximo" class="col-md-4 col-form-label text-md-right">{{ __('Stock máximo') }}</label>

                            <div class="col-md-6">
                                {!! Form::text('stockmaximo',null,array(
                                    'class'=>'form-control',
                                    'placeholder'=>'Stock máximo'
                                )) !!}

                                @error('stockminimo')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="tipoproducto" class="col-md-4 col-form-label text-md-right">{{ __('Tipo de producto') }}</label>

                            <div class="col-md-6">
                                {!! Form::select('tipoproducto',["0" => "-- Elija tipo de producto --", "1" => "Inventario", "2" => "Servicios"], null, array(
                                    'class'=>'form-control',
                                )) !!}

                                @error('habilita')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    {{ __('Grabar') }}
                                </button>
                            </div>
                        </div>
                    </form>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </section>
@endsection