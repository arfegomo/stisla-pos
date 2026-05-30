@extends('layouts.app')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
@endsection

@section('content')

    <section class="section">
        
        <section class="section">
        
            <div class="section-header">

                <div class="row">

                    <div class="col-lg-12">
                            
                        <h1 class="page__heading">Mesas</h1>
                            
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

                        {{-- Indicador de conexión en tiempo real --}}
                        <div class="d-flex align-items-center mb-3 mt-2">
                            <span id="sse-indicator" class="badge badge-secondary mr-3 p-2">
                                <i class="fas fa-circle"></i> Conectando...
                            </span>
                            <small class="text-muted">&nbsp; Actualización en tiempo real</small>
                        </div>

                        <div class="row" id="mesas-container">
                            @include('mesas.partial')
                        </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            

            <!-- Modal -->
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

            <div class="modal-dialog">
            
                <div class="modal-content">
            
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Responsable de la mesa</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                
                    <div class="modal-body">

                        <form action="{{ route('mesa.update-mesa') }}" method="POST">

                            @csrf
                            @method('PUT')
                    
                            <div class="col-lg-12">
                                
                                <div class="form-group">
                                    <label for="responsable">Nombre</label>
                                    <input type="text" name="responsable" class="form-control" id="subtotal" value="">
                                </div>

                                <div class="form-group">
                                    <label for="mesa">Mesa</label>
                                    <input type="text" name="mesa-id" id="mesa-id" class="form-control" readonly>
                                </div>
                            
                            </div>


                            <div class="modal-footer">
                            
                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" id="grabar" class="btn btn-primary">Grabar</button>

                            </div>

                        </form>

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
        <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
        <script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"></script>


        <script>

        $(document).ready(function(){
            //Llamar modal
            $(document).on('click','#openModal',function(e){
            idMesa = $(this).attr("data-id");
            $("#mesa-id").val(idMesa);
            $('#exampleModal').modal('show'); //abrir
            });    
            //Fin llamado modal

            $(".draggable").draggable({ 
                cursor: "move", cursorAt: { top: 56, left: 56 },
                start: function(event, ui) {
                    initialPosition = ui.position
                },
                stop: function(event, ui) {
                    var idDelDivDraggable = $(this).attr("id");
                } 
            });
            $(".droppable").droppable({
                drop: function(event, ui){
                    var idDelDivDroppable = $(this).attr("id");
                    var draggableId = ui.draggable.attr("id");
                    var $draggable = ui.draggable;
                    Swal.fire({
                    title: "¡Advertencia!",
                    text: `¿Confirma es traslado de la cuenta a la mesa # ${idDelDivDroppable}?`,
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Confirmar",
                    cancelButtonText: "Cancelar"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            srcTraslado = "{{ route('mesa.trasladar-mesa') }}";
                            $.ajax({
                                url: srcTraslado,
                                method: 'PUT',
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                data: { origen: ui.draggable.attr('id'), destino: idDelDivDroppable }, // Envía el valor del botón arrastrado
                                success: function(response) {
                                    if(response.data == 'success'){
                                        Swal.fire({
                                            title: "¡Mesa trasladada!",
                                            text: "",
                                            icon: "success",
                                            showConfirmButton: false,
                                            timer: 1500
                                        });
                                        setTimeout(() => {
                                            location.reload();
                                        }, 2000);
                                    }else{
                                        Swal.fire({
                                            title: "¡Error!",
                                            text: "¡Ops, algo salió mal!",
                                            icon: "error",
                                            showConfirmButton: false,
                                timer: 1500
                                        });
                                        setTimeout(() => {
                                            location.reload();
                                        }, 2000);
                                    }
                                },
                                error: function(xhr, status, error) {
                                    // Manejar errores
                                    console.error(error);
                                }
                            });
                        }else{
                            //location.reload();
                            $draggable.animate({
                                top:0, left:0
                            }, 500);
                        }
                    });
                    //alert(`trasladar mesa ${ui.draggable.attr('id')} a la mesa ${idDelDivDroppable}`);
                }
            });

        })

        </script>

        {{-- SSE: actualización en tiempo real del estado de las mesas --}}
        <script>
            var sseSource = null;

            function iniciarSSE() {
                if(sseSource) sseSource.close();

                sseSource = new EventSource('{{ route("mesas.stream") }}');

                sseSource.onopen = function() {
                    $('#sse-indicator')
                        .removeClass('badge-secondary badge-danger')
                        .addClass('badge-success')
                        .html('<i class="fas fa-circle"></i> En tiempo real');
                };

                sseSource.onmessage = function(e) {
                    // Actualizar el contenedor de mesas con el HTML fresco del servidor
                    $.ajax({
                        url: '{{ route("mesas.partial") }}',
                        type: 'GET',
                        success: function(html) {
                            $('#mesas-container').html(html);
                        }
                    });
                };

                sseSource.onerror = function() {
                    $('#sse-indicator')
                        .removeClass('badge-secondary badge-success')
                        .addClass('badge-danger')
                        .html('<i class="fas fa-circle"></i> Reconectando...');

                    // El navegador reconecta automáticamente, pero forzamos después de 5s
                    setTimeout(function() {
                        iniciarSSE();
                    }, 5000);
                };
            }

            $(document).ready(function() {
                iniciarSSE();
            });

            // Cerrar la conexión SSE al salir de la página
            window.addEventListener('beforeunload', function() {
                if(sseSource) sseSource.close();
            });
        </script>

    @endsection








