@extends('layouts.app')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
@endsection

@section('content')

    <section class="section">
        
        <section class="section">
        
            <div class="section-header">
                <div class="row w-100">
                    <div class="col-lg-12">
                        <h1 class="page__heading">Kardex — {{ $articulo->nombre }}</h1>
                    </div>
                </div>
            </div>

            <div class="section-body">
                <div class="row mb-3">
                    <div class="col-lg-3 col-md-6 mb-2">
                        <div class="card shadow-sm border-left-primary h-100">
                            <div class="card-body py-2">
                                <div class="text-xs text-muted mb-1">Existencia actual</div>
                                <div class="h4 mb-0 font-weight-bold">{{ $articulo->existenciactual }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <div class="card shadow-sm border-left-success h-100">
                            <div class="card-body py-2">
                                <div class="text-xs text-muted mb-1">Costo actual</div>
                                <div class="h4 mb-0 font-weight-bold">{{ number_format($articulo->costoactual, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <div class="card shadow-sm border-left-info h-100">
                            <div class="card-body py-2">
                                <div class="text-xs text-muted mb-1">Existencia inicial</div>
                                <div class="h4 mb-0 font-weight-bold">{{ $articulo->existenciainicial }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <div class="card shadow-sm border-left-warning h-100">
                            <div class="card-body py-2">
                                <div class="text-xs text-muted mb-1">Costo inicial</div>
                                <div class="h4 mb-0 font-weight-bold">{{ number_format($articulo->costoinicial ?? 0, 2, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
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

                    @php($saldo = $kardexs->existenciainicial ?? 0)

                    @if($ultimoCierre)
                    @php($mesCerrado = \Carbon\Carbon::parse($ultimoCierre->fecha_cierre)->translatedFormat('F Y'))
                    @php($mesAbierto = \Carbon\Carbon::parse($ultimoCierre->fecha_cierre)->addDay()->translatedFormat('F Y'))
                    <div class="card border-left-info mb-3 shadow-sm">
                        <div class="card-body py-2">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-info"></i>
                                </div>
                                <div class="col">
                                    <div class="d-flex flex-wrap gap-4">
                                        <div>
                                            <div class="text-xs text-muted">Mes abierto</div>
                                            <div class="font-weight-bold text-info">{{ ucfirst($mesAbierto) }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-muted">Saldo inicial del período</div>
                                            <div class="font-weight-bold">{{ $saldo }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-muted">Costo inicial del período</div>
                                            <div class="font-weight-bold">{{ number_format($kardexs->costoinicial ?? 0, 2, ',', '.') }}</div>
                                        </div>
                                        <div>
                                            <div class="text-xs text-muted">Último mes cerrado</div>
                                            <div class="font-weight-bold text-success">{{ ucfirst($mesCerrado) }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

            <table id="kardex" class="table table-bordered shadow-lg mt-2" style="width:100%">
                <thead class="bg bg-dark">
                    <tr>
                        <th style='color:#fff;'>Fecha</th>
                        <th style='color:#fff;'>Concepto</th>
                        <th style='color:#fff;'>Consecutivo</th>
                        <th style='color:#fff;'>Transaccion</th>
                        <th style='color:#fff;'>Entradas</th>
                        <th style='color:#fff;'>Salidas</th>
                        <th style='color:#fff;'>Costo unitario</th>
                        <th style='color:#fff;'>Costo promedio</th>
                        <th style='color:#fff;'>Vlr. unitario</th>
                        <th style='color:#fff;'>Costo total</th>
                        <th style='color:#fff;'>Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($kardexs->transactions as $kardex)
                        @php($tid      = $kardex->concepto->transaccion_id)
                        @php($cantidad = $kardex->pivot->cantidad)
                        @php($saldo    = in_array($tid,[1,3,6]) ? $saldo - $cantidad : (in_array($tid,[2,4,5]) ? $saldo + $cantidad : $saldo))
                        <tr>
                            <td>{{ $kardex->created_at }}</td>
                            <td>{{ $kardex->concepto->nombre }}</td>
                            <td>{{ $kardex->consecutivo }}</td>
                            <td>{{ $kardex->id }}</td>
                            <td>{{ in_array($tid,[2,4,5]) ? $cantidad : '' }}</td>
                            <td>{{ in_array($tid,[1,3,6]) ? $cantidad : '' }}</td>
                            <td>{{ number_format($kardex->pivot->costoventa, 0, ',', '.') }}</td>
                            <td>{{ number_format($kardex->pivot->costopromedio, 0, ',', '.') }}</td>
                            <td>{{ number_format($kardex->pivot->preciounitario, 0, ',', '.') }}</td>
                            <td>{{ number_format($kardex->pivot->costoventa * $cantidad, 0, ',', '.') }}</td>
                            <td>{{ $saldo }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        
        </section>
    @endsection

    @section('js')
        
        <script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            $(document).ready( function () {
                $('#kardex').DataTable({
                    responsive:true,
                    language: {
                        "decimal": "",
                        "emptyTable": "No hay información",
                        "info": "Mostrando _START_ a _END_ de _TOTAL_ Entradas",
                        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
                        "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                        "infoPostFix": "",
                        "thousands": ",",
                        "lengthMenu": "Mostrar _MENU_ Entradas",
                        "loadingRecords": "Cargando...",
                        "processing": "Procesando...",
                        "search": "Buscar:",
                        "zeroRecords": "Sin resultados encontrados",
                        "paginate": {
                            "first": "Primero",
                            "last": "Ultimo",
                            "next": "Siguiente",
                            "previous": "Anterior"
                }
                },
                });
            });

    </script>

    @endsection








