@extends('layouts.app')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
@endsection

@section('content')

<section class="section">
    <section class="section">

        <div class="section-header">
            <div class="row w-100">
                <div class="col-lg-12">
                    <h1 class="page__heading">Historial de Transacciones</h1>
                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <table id="tablaHistorial" class="table table-bordered table-hover shadow-sm mt-2" style="width:100%">
                                <thead style="background-color:#fff; color:#333; border-bottom:2px solid #6777ef;">
                                    <tr>
                                        <th>#</th>
                                        <th>Consecutivo</th>
                                        <th>Tipo</th>
                                        <th>Concepto</th>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Cliente</th>
                                        <th>Cajero</th>
                                        <th class="text-right">Total</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ventas as $venta)
                                    <tr>
                                        <td>{{ $venta->id }}</td>
                                        <td><strong>{{ str_pad($venta->consecutivo, 8, '0', STR_PAD_LEFT) }}</strong></td>
                                        <td>{{ $venta->tipo }}</td>
                                        <td>{{ $venta->concepto }}</td>
                                        <td>{{ $venta->fecha }}</td>
                                        <td>{{ $venta->hora }}</td>
                                        <td>{{ $venta->cliente }}</td>
                                        <td>{{ $venta->cajero }}</td>
                                        <td class="text-right">{{ number_format($venta->total ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('ticket.previsualizar', $venta->id) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-info"
                                               title="Previsualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-primary btn-reimprimir"
                                                    data-id="{{ $venta->id }}"
                                                    title="Reimprimir">
                                                <i class="fas fa-print"></i>
                                            </button>
                                        </td>
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
</section>

@endsection

@section('js')
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function(){
        $('#tablaHistorial').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            language: {
                search: 'Buscar:',
                lengthMenu: 'Mostrar _MENU_ registros',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ facturas',
                infoEmpty: 'Sin resultados',
                zeroRecords: 'No se encontraron facturas',
                paginate: { first:'Primero', last:'Último', next:'Siguiente', previous:'Anterior' }
            }
        });
    });
</script>
<script>
    $(document).on('click', '.btn-reimprimir', function(){
        var id  = $(this).data('id');
        var btn = $(this);

        Swal.fire({
            title: '¿Reimprimir factura?',
            text: 'Se enviará una copia a la impresora.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, imprimir',
            cancelButtonText: 'Cancelar'
        }).then(function(result){
            if(result.isConfirmed){
                btn.prop('disabled', true);
                $.ajax({
                    url: '/ticket/imprimir/' + id,
                    type: 'POST',
                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    success: function(res){
                        btn.prop('disabled', false);
                        if(res.error){
                            Swal.fire('Error', res.message, 'error');
                        } else {
                            Swal.fire('Listo', 'Ticket enviado a la impresora.', 'success');
                        }
                    },
                    error: function(xhr){
                        btn.prop('disabled', false);
                        Swal.fire('Error', xhr.responseJSON ? xhr.responseJSON.message : 'No se pudo conectar con la impresora.', 'error');
                    }
                });
            }
        });
    });
</script>
@endsection
