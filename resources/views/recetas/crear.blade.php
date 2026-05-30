@extends('layouts.app')

@section('css')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />

@endsection

@section('content')
    <section class="section">
        <div class="section-header">
            <h3 class="page__heading">Recetas</h3>
            
                @can('crear-receta')
                    <a href="{{ route('receta.createProduct') }}"><i class="fa-solid fa-circle-plus fa-2x"></i></a>
                @endcan
        </div>
        <div class="section-body">
            
            <div class="row">

                <div class="card col-6">

                        <div>
                            <label for="receta">Receta</label>
                            {!! Form::text('receta',null,['class' => 'typeahead form-control', 'id' => 'receta']) !!}
                            {!! Form::hidden('receta_id',null,['class' => 'typeahead form-control', 'id' => 'receta_id']) !!}
                        </div>

                        <div>
                            <label for="producto">Componente</label>
                            {!! Form::text('producto',null,['class' => 'typeahead form-control', 'id' => 'producto']) !!}
                            {!! Form::hidden('producto_id',null,['class' => 'typeahead form-control', 'id' => 'producto_id']) !!}
                        </div>

                        <div>
                            <label for="cantidad">Cantidad</label>
                            {!! Form::text('cantidad',null,['class' => 'typeahead form-control', 'id' => 'cantidad']) !!}
                        </div>

                        <div class="col-6">
                            <button type="button" name="grabar" id="grabar" class="btn btn-dark btn-lg btn-block"><i class="fa-solid fa-circle-plus fa-lg"></i></button>
                        </div>

                </div>

                <div class="card col-6">
                    <div class="box-body">
                        <div class="table table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="table-dark text-white-all">
                            <tr>
                                <th>NOMBRE</th>
                                <th>CANTIDAD</th>
                                <th>ACCION</th>
                            </tr>
                            </thead>
                            <tbody id="tbodyProducto">
                    
                            </tbody>
                        </table>
                        </div>
                        <!-- /.box-body -->
                    </div>
                </div>

            </div>

        </div>
        
    </section>
@endsection

@section('js')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>

<script>
//Autocompletar productos
    srcProductos = "{{ route('facturacion.searchComponentProduct') }}"
    $("#producto").autocomplete({
        source: function( request, response ) {
        
            $.ajax({
            url: srcProductos,
            type: 'GET',
            dataType: "json",
            data: {
                search: request.term
            },
        
            success: function( data ) {

                response($.map(data, function(item) {
                    return {
                        label: '[' + item.etiqueta + '] ' + item.nombre,
                        value: item.id,
                        etiqueta: item.etiqueta
                    };
                }));
                
            }
            });
        },
        
        select: function (event, ui) {
            $('#producto').val(ui.item.label);
            $('#producto_id').val(ui.item.value);
            return false;
        }

    });
//Fin Autocompletar productos

//Autocompletar recetas
srcRecetas = "{{ route('receta.searchReceta') }}"
   
    $("#receta").autocomplete({
        
        source: function( request, response ) {
        
            $.ajax({
            url: srcRecetas,
            type: 'GET',
            dataType: "json",
            data: {
                search: request.term
            },
        
            success: function(data) {
                
                console.log(data)
                
                response($.map(data, function(item) {
        
                                    return {
                    
                                        label: item.nombre,
                                        value: item.id,   
                                        
                                    };

                                }
                ));
                
            }

            });
        },
        
            select: function (event, ui) {
            
                $('#receta').val(ui.item.label);
                $('#receta_id').val(ui.item.value);

                console.log($('#receta_id').val());
                
                listReceta = "{{ route('receta.listReceta') }}";

                $.ajax({
                        url: listReceta,
                        type: 'GET',
                        dataType: "json",
                        data: {
                            receta:$("#receta_id").val()
                        },

                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        success:function(data){

                                //console.log(data.receta);        

                                var valor = ''

                                data.receta.productos.forEach(element => {
                                
                                valor += `<tr>
                                        <td>${element.nombre}</td>
                                        <td>${element.pivot.cantidad}</td>
                                        <td>
                                            <form class="delete-form" data-route="/receta/${element.pivot.receta_id}/${element.pivot.producto_id}" >
                                                @method("delete") 
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>    
                                            </form>
                                        </td>
                                    </tr>`;


                            });
                            

                                $("#tbodyProducto").html(valor);

                                
                        }
                    
                    });

                //console.log(ui.item); 
                return false;
            },

    });
//Fin Autocompletar recetas

//Graba receta
$("#grabar").click(function(){
            
            var producto = $("#producto_id").val();
            var receta = $('#receta_id').val();
            var cantidad = $("#cantidad").val();

                //Autocompletar productos
                srcReceta = "{{ route('receta.addReceta') }}"

                $.ajax({
                        url: srcReceta,
                        type: 'POST',
                        dataType: "json",
                        data: {
                            
                            producto:producto,
                            receta:receta,
                            cantidad:cantidad,

                        },

                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        success:function(data){

                                //console.log(data.receta);

                                alertify.set('notifier','position', 'bottom-right');
                                alertify.success(`${data.message}`);                        

                                var valor = ''

                                data.receta.productos.forEach(element => {
                                
                                valor += `<tr>
                                        <td>${element.nombre}</td>
                                        <td>${element.pivot.cantidad}</td>
                                        <td>
                                            <form class="delete-form" data-route="/receta/${element.pivot.receta_id}/${element.pivot.producto_id}" >
                                                @method("delete") 
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>    
                                            </form>
                                        </td>
                                    </tr>`;


                            });
                            

                                $("#tbodyProducto").html(valor);

                                
                        }
                    
                    });

 });         
 //Fin grabar


 //Delete items
 $(document).on('submit', '.delete-form', function(e) {

        e.preventDefault();

        $.ajax({
                type: 'post',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: $(this).data('route'),
                data: {
                    '_method': 'delete'
                },
                success: function (data) {

                        alertify.set('notifier','position', 'bottom-right');
                        alertify.success(`${data.message}`);                            

                        var valor = ''

                            data.receta.productos.forEach(element => {
                            
                            valor += `<tr>
                                    <td>${element.nombre}</td>
                                    <td>${element.pivot.cantidad}</td>
                                    <td>
                                        <form class="delete-form" data-route="/receta/${element.pivot.receta_id}/${element.pivot.producto_id}" >
                                            @method("delete") 
                                            <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>    
                                        </form>
                                    </td>
                                </tr>`;

                        });

                        $("#tbodyProducto").html(valor);
                    
                }
            });
});
//Fin Delete items

</script>
@endsection