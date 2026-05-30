@extends('layouts.app')

@section('css')

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css" />

@endsection

@section('content')

<section class="section">
    
    <section class="section">
    
        <div class="section-header">

            <hr>
        </div>

        <div class="section-body">

            <div class="row mx-auto">

                <div class="col-lg-10 mx-auto">

                    <div class="card shadow-dark">

                        <div class="row bg-dark">
                            <div class="col-lg-6">
                        
                                <h4 class="page__heading text-white">Facturación @if($mesa != 1000) [ Mesa - {{ $mesa }} ] @endif</h4>                                
                            
                            </div> 
                            
                        </div>

                        <div class="card-header bg-secondary text-black">
                            Factura                            
                        </div>

                        <div class="card-body">
                            
                        <form id="transaccion">

                                <div class="form-group row">
                                    
                                        <div class="col-md-3">
                                            <label for="documento">Concepto de facturación</label>
                                                {!! Form::select('concetptos', $conceptos,null, ['class' => 'form-control', 'id' => 'concepto']) !!}
                                        </div>

                                        <div class="col-md-5">
                                            <label for="socio">Socio del negocio</label>
                                            {!! Form::text('socio',null,['class' => 'typeahead form-control', 'id' => 'socio']) !!}
                                        </div>

                                        <div class="col-lg-4">
                                            <label for="documento">CC/Nit</label>
                                            <input type="text" name="documentoID" class="form-control" id="documentoID" readonly="readonly">
                                        </div>
                                        
                                        <input type="hidden" name="consecutivo" class="form-control" id="consecutivo" value="{{ $consecutivo }}">
                                        <input type="hidden" name="transaccion_id" id="transaccion_id">

                                        {{ csrf_field() }}

                                    </div>
                                </div>                   

                        </div>

                        <div class="card shadow-dark">

                            <div class="card-header bg-secondary text-black">
                            
                                Productos
                            
                            </div>

                            <div class="card-body">

                                <div class="form-group row">

                                    <div class="col-md-4">
                                        <label for="socio">Producto</label>
                                        {!! Form::text('producto',null,['class' => 'typeahead form-control', 'id' => 'producto']) !!}
                                        {!! Form::hidden('productoID',null,['class' => 'typeahead form-control', 'id' => 'productoID']) !!}
                                    </div>

                                    <div class="col-md-3">
                                        <label for="precio">Precio</label>
                                        {!! Form::text('precioventa1',null,['class' => 'typeahead form-control', 'id' => 'precioventa1', 'readonly' => 'readonly']) !!}
                                    </div>

                                    <div class="col-md-1">
                                        <label for="impuesto">Impuesto</label>
                                        {!! Form::text('impuesto',null,['class' => 'typeahead form-control', 'id' => 'impuesto', 'readonly' => 'readonly']) !!}
                                        {!! Form::hidden('impuestoID',null,['class' => 'typeahead form-control', 'id' => 'impuestoID', 'readonly' => 'readonly']) !!}
                                    </div>

                                    <div class="col-md-1">
                                        <label for="descuento">Descuento</label>
                                        {!! Form::text('descuento',null,['class' => 'typeahead form-control', 'id' => 'descuento']) !!}
                                    </div>

                                    <div class="col-md-2">
                                        <label for="cantidad">Cantidad</label>
                                        {!! Form::text('cantidad',null,['class' => 'typeahead form-control', 'id' => 'cantidad']) !!}
                                    </div>
                                    
                                    <div class="col-md-1">
                                        <label for="cantidad">Agregar</label>
                                        <button type="button" name="grabar" id="grabar" class="btn btn-dark"><i class="fa-solid fa-cart-plus fa-lg"></i></button>
                                    </div>
                                </div>

                            </div>

                        </form>

                            <div class="card-body">

                                <div class="form-group row">

                                    <div class="col-md-8">
                                        <div class="box-body">
                                            <div class="table table-responsive">
                                            <table class="table table-hover table-bordered">
                                                <thead class="table-dark text-white-all">
                                                <tr>
                                                    <th>NOMBRE</th>
                                                    <th>CANT.</th>
                                                    <th>PRECIO</th>
                                                    <th>DESC.</th>
                                                    <th>TOTAL</th>
                                                    <!--<th>IMPUESTO</th>-->
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

                                    <div class="col-md-4">

                                        <div class="card-header bg-dark text-white row-1">
                                            Resumen
                                        </div>

                                        <div class="box-body">
                                            
                                            <div class="form-floating">
                                                <label for="resumenSubtotal">Subtotal</label>
                                                <input type="number" class="form-control text-center" style="font-size: 30px" id="resumenSubtotal" readonly>
                                            </div>

                                            <div class="form-floating">
                                                <label for="resumenDescuento">Descuento</label>
                                                <input type="number" class="form-control text-center" style="font-size: 30px" id="resumenDescuento" readonly>
                                            </div>

                                            <div class="form-floating">
                                                <label for="resumenImpuesto">Impuesto</label>
                                                <input type="number" class="form-control text-center" style="font-size: 30px" id="resumenImpuesto" readonly>
                                            </div>

                                            <div class="form-floating">
                                                <label for="resumenTotal">Total</label>
                                                <input type="number" class="form-control text-center" style="font-size: 30px" id="resumenTotal" aria-describedby="basic-addon1" readonly>
                                            </div>

                                            <div class="mt-3 text-center">
                                                <button type="button" data-id=`${consecutivo}` class="btn btn-success" id="openModal"><i class="fa-solid fa-hand-holding-dollar fa-3x"></i></button>
                                            </div>

                                        </div>
                                    </div>

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
                        <h1 class="modal-title fs-5" id="exampleModalLabel">Forma de pago</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                
                    <div class="modal-body">

                        <form id="pago" action="facturacion.store" method="POST">
                    
                            <div class="col-lg-12">
                                
                                <div class="form-group">
                                    <label for="subtotal">Subtotal</label>
                                    <input type="text" name="subtotal" class="form-control" id="subtotal" value="" readonly="readonly">
                                </div>

                                <div class="form-group">
                                    <label for="impuestos">Impuestos</label>
                                    <input type="text" name="impuestos" class="form-control" id="impuestos" value="" readonly="readonly">
                                </div>

                                <div class="form-group">
                                    <label for="total">Total</label>
                                    <input type="text" name="total" class="form-control" id="total" value="" readonly="readonly">
                                </div>

                                <div class="form-group" id="ocultar-valor">
                                    <label for="valor">Valor</label>
                                    <input type="text" name="valor" class="form-control" id="valor" autocomplete="off">
                                </div>

                                <div class="form-group" id="ocultar1" style="display: none">
                                    <label for="cambio">Cambio:</label>
                                    <input type="text" name="cambio" class="form-control" id="cambio" autocomplete="off" readonly="readonly">
                                </div>

                            
                            </div>

                            <div class="col-lg-12">
                                
                                <div class="form-group">
                                
                                    <label for="formadepago">Forma de Pago</label><br>
                                
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    
                                            <label class="btn btn-success active">
                                                <input type="radio" name="options" id="option1" autocomplete="off" checked value="1"> Efectivo
                                            </label>

                                            <label class="btn btn-success">
                                                <input type="radio" name="options" id="option2" autocomplete="off" value="2"> Tarjeta D/C
                                            </label>

                                            <label class="btn btn-success">
                                                <input type="radio" name="options" id="option4" autocomplete="off" value="4"> Nequi
                                            </label>

                                            <label class="btn btn-success">
                                                <input type="radio" name="options" id="option6" autocomplete="off" value="6"> Crédito
                                            </label>

                                            <label class="btn btn-success">
                                                <input type="radio" name="options" id="option5" autocomplete="off" value="5"> Incluir
                                            </label>
                                        
                                        </div>
                                </div>

                                <div id="divResult" style="display: none">
                    
                                    <div class="col-lg-12">
                                        <input type="checkbox" name="pago[]" id="optionc1" autocomplete="off" value="1"> Efectivo
                                    </div>

                                    <div class="col-lg-12">
                                        <input type="number" name="valor1" class="form-control" style="display: none" id="valor1">
                                    </div>
                                    
                                    <div class="col-lg-12">
                                        <input type="checkbox" name="pago[]" id="optionc2" autocomplete="off" value="2"> Tarjeta D/C
                                    </div>

                                    <div class="col-lg-12">
                                        <input type="number" name="valor2" class="form-control" style="display: none" id="valor2">
                                    </div>
                                    
                                    <div class="col-lg-12">
                                        <input type="checkbox" name="pago[]" id="optionc4" autocomplete="off" value="4"> Nequi
                                    </div>

                                    <div class="col-lg-12">
                                        <input type="number" name="valor4" class="form-control" style="display: none" id="valor4">
                                    </div>

                                </div>
                                
                            </div>

                            <div class="modal-footer" id="pague" style="display: none">
                            
                                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" id="pagar" class="btn btn-primary">Pagar</button>

                            </div>

                        </form>

                    </div>

            </div>

        </div>
    
    </section>

@endsection

@section('js')

<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

    //Concepto de facturación
    $("select[id=concepto]").change(function(){

        var concepto = $('select[id=concepto]').val();

        srcTransaccion = "{{ route('facturacion.getTipoTransaccion') }}"

        $.ajax({

            url: srcTransaccion,
            type: 'GET',
            dataType: "json",
            data: {
                
                concepto:concepto,

            },

            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
            
            success:function(data){

                if((data.transaccion == 1)||(data.transaccion == 5)){

                    transaccion = data.transaccion

                }else if((data.transaccion == 2)||(data.transaccion == 6)){

                    transaccion = data.transaccion

                    $('#precioventa1').attr("readonly", false);
                    $('#impuesto').attr("readonly", false);

                }else{

                    transaccion = data.transaccion

                }

            }

        });

    });

    //Autocompletar socios
    srcSocios = "{{ route('facturacion.searchSocio') }}"
    $( "#socio" ).autocomplete({
        source: function( request, response ) {
        
            $.ajax({
            url: srcSocios,
            type: 'GET',
            dataType: "json",
            data: {
                search: request.term
            },
        
            success: function( data ) {
                
                response($.map(data, function(item) {
                    
                                    return {
                    
                                        label: item.nombres + " " + item.apellidos,
                                        value: item.documento                                        

                                    };

                                }
                ));
                
            }
            });
        },
        
        select: function (event, ui) {
        
            $('#socio').val(ui.item.label);
            $('#documentoID').val(ui.item.value);
            $('#precioventa1').val();
            $('#impuestos').val();
            $('#producto').focus();
            
            //console.log(ui.item); 
            return false;
        }

    });
    //Fin Autocompletar socios

    //Autocompletar productos
    srcProductos = "{{ route('facturacion.searchProducto') }}";
    transaccion = 1;

    $( "#producto" ).autocomplete({
        source: function( request, response ) {
        
            $.ajax({
            url: srcProductos,
            type: 'GET',
            dataType: "json",
            data: {
                search: request.term, transaccion:transaccion
            },
        
            success: function( data ) {
                
                //console.log(data)
                
                response($.map(data, function(item) {
        
                                    return {
                    
                                        label: item.nombre,
                                        value: item.id,   
                                        precioventa1: item.precioventa1,                                     
                                        impuesto: item.impuesto.tasa,
                                        impuestoid: item.impuesto.id
                                        
                                    };

                                }
                ));
                
            }
            });
        },
        
        select: function (event, ui) {
        
            $('#producto').val(ui.item.label);
            $('#precioventa1').val(ui.item.precioventa1);
            $('#impuesto').val(ui.item.impuesto);
            $('#impuestoID').val(ui.item.impuestoid);
            $('#productoID').val(ui.item.value);
            $("#descuento").val(0);
            $("#cantidad").focus();
            //console.log(ui.item); 
            return false;
        }

    });
    //Fin Autocompletar productos

    //Load
    $(document).ready(function() {

    $("#socio").val("Cuantías Menores");

    $("#documentoID").val(222222222);

    $("#producto").focus();

    //Grabar items
    $("#grabar").click(function(){
            
            var precio = $("#precioventa1").val();
            var cantidad = $('#cantidad').val();
            var descuento = $("#descuento").val();
            var productoID = $("#productoID").val();
            var concepto = $("#concepto").val();
            var documento = $("#documentoID").val();
            var impuesto = $("#impuesto").val();
            var impuestoID = $("#impuestoID").val();
            var consecutivo = $("#consecutivo").val();
            var mesa = {{ $mesa }};
                //Autocompletar productos
                srcTemporal = "{{ route('facturacion.addTemporal') }}"

                $.ajax({
                        url: srcTemporal,
                        type: 'POST',
                        dataType: "json",
                        data: {
                            
                            precio:precio,
                            cantidad:cantidad,
                            descuento:descuento,
                            productoID:productoID,
                            concepto:concepto,
                            documento:documento,
                            impuesto:impuesto,
                            impuestoID:impuestoID,
                            consecutivo: consecutivo,
                            mesa: mesa

                        },
                        headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                        success:function(data){

                            if(data.transaccion == ""){

                                alertify.set('notifier','position', 'bottom-right');
                                alertify.error(`${data.message}`);
                                return;

                            }else{
                                
                                alertify.set('notifier','position', 'bottom-right');
                                alertify.success(`${data.message}`);                        

                            }

                            $('input[name="transaccion_id"]').val(data.transaccion);

                            $('#socio').attr("readonly", true);

                            $('#producto').val("").focus();

                            $('#precioventa1').val("");

                            $('#impuesto').val("");

                            $('#descuento').val("");

                            $('#cantidad').val("");

                            var valor = ''
                            var total = 0
                            var valorProducto = 0
                            var subTotal = 0
                            var impuestos = 0
                            var descuento = 0

                            data.productos.forEach(element => {

                                subTotal = subTotal + (element.baseunitario * element.cantidad) - ((element.baseunitario * element.cantidad) * (element.descuento/100))

                                impuestos = impuestos + (((element.baseunitario * element.cantidad)- ((element.baseunitario * element.cantidad)*(element.descuento/100))) * (element.impuesto/100)) 
                                
                                valorProducto = (element.preciounitario * element.cantidad) - ((element.preciounitario * element.cantidad) * (element.descuento/100))

                                descuento = descuento + ((element.preciounitario * element.cantidad) * (element.descuento/100))

                                total = total + valorProducto
                                
                                valor += `<tr>
                                        <td>${element.nombre}</td>
                                        <td>${element.cantidad}</td>
                                        <td>${element.preciounitario}</td>
                                        <td>${element.descuento}%</td>
                                        <td>${valorProducto.toFixed(2)}</td>
                                        <td>
                                            <form class="delete-form" data-route="/facturacion/${element.id}/${element.consecutivo_id}" >
                                                @method("delete") 
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>    
                                            </form>
                                        </td>
                                    </tr>`;


                            });
                            
                                $('#subtotal').val(Math.round(subTotal));                                  
                                $('#impuestos').val(Math.round(impuestos));
                                $('#total').val(total.toFixed(2));

                                $('#resumenTotal').val(Math.round(total));
                                $('#resumenDescuento').val(Math.round(descuento));
                                $('#resumenImpuesto').val(Math.round(impuestos));
                                $('#resumenSubtotal').val(Math.round(subTotal));

                                $("#tbodyProducto").html(valor);
                                
                                $("#valor").on("keyup change", function(e) {
                
                                    var valor = $("#valor").val();

                                    var cambio = (valor - $('#total').val());

                                    $("#ocultar1").css("display","");                            

                                    if(cambio >= 0){

                                        $("#pague").css("display","");
                                        $("#cambio").val(cambio);
                                    }else{

                                        $("#pague").css("display","none");
                                        $("#ocultar1").css("display","none");
                                    }

                                })
                        }
                    
                    });
                });         

            //Validamos que opción de pago fue seleecionada
            $("input[type=radio]").on( 'change', function() {
                
                if($(this).is(':checked')) {

                    if($(this).val() == 1){
                        
                        $("#efectivo").attr({'style': 'display:none'}).val("");
                        
                        $("#tarjetadebito").attr("style", "display:none").val("");

                        $("#bono").attr("style", "display:none").val("");

                        $('#optionc1').attr('checked',false);

                        $('#optionc2').attr('checked',false);

                        $('#optionc4').attr('checked',false);

                        $("#ocultar-valor").css("display","");

                        $("#divResult").attr("style", "display:none");

                        $("#pague").css("display","none");

                        $("#ocultar1").css("display","");

                        $("#valor").val(0);

                        $("#cambio").val(0);
                    
                    }else if($(this).val() == 2){
                        
                        $("#efectivo").attr({'style': 'display:none'}).val("");
                        
                        $("#tarjetadebito").attr("style", "display:none").val("");

                        $("#bono").attr("style", "display:none").val("");

                        $('#optionc1').attr('checked',false);

                        $('#optionc2').attr('checked',false);

                        $('#optionc4').attr('checked',false);

                        $("#ocultar1").css("display","none");

                        $("#ocultar-valor").css("display","none");

                        $("#pague").css("display","");

                        $("#divResult").attr("style", "display:none");

                        $("#valor").val(0);

                        $("#cambio").val(0);

                    }else if($(this).val() == 4){

                        $("#efectivo").attr({'style': 'display:none'}).val("");
                        
                        $("#tarjetadebito").attr("style", "display:none").val("");

                        $("#bono").attr("style", "display:none").val("");

                        $('#optionc1').attr('checked',false);

                        $('#optionc2').attr('checked',false);

                        $('#optionc4').attr('checked',false);
                        
                        $("#ocultar1").css("display","none");

                        $("#ocultar-valor").css("display","none");

                        $("#pague").css("display","");

                        $("#divResult").attr("style", "display:none");

                        $("#valor").val(0);

                        $("#cambio").val(0);

                    }else if($(this).val() == 5){
                        
                        $("#ocultar-valor").css("display","none");

                        $("#pague").css("display","none");

                        $("#divResult").attr("style", "display");

                        $("#ocultar1").css("display","none");

                        $("#ocultar-valor").css("display","none");

                        $("input[type=checkbox]").on( 'change', function() {
                            
                            if( $(this).is(':checked') ) {

                                if($(this).val() == 1){
                                    $("#valor1").attr("style", "display").select();
                                };

                                if($(this).val() == 2){
                                    $("#valor2").attr("style", "display").select();
                                };

                                if($(this).val() == 4){
                                    $("#valor4").attr("style", "display").select();
                                };
                            }

                            $("#valor1, #valor2, #valor4").on("keyup change", function(e) {

                                var efectivo = $("#valor1").val();
                                var tarjetadebito = $("#valor2").val();
                                var bono = $("#valor4").val();

                                totalIncluir = (Number(efectivo) + Number(tarjetadebito) + Number(bono));

                                if(totalIncluir == $("#total").val()){

                                    $("#pague").css("display","");                 
                                    
                                }else{

                                    $("#pague").css("display","none");                 
                                }
                                
                        })
                        
                        })

                    }else if($(this).val() == 6){

                        $("#efectivo").attr({'style': 'display:none'}).val(0);
                        
                        $("#tarjetadebito").attr("style", "display:none").val(0);

                        $("#bono").attr("style", "display:none").val(0);

                        $('#optionc1').attr('checked',false);

                        $('#optionc2').attr('checked',false);

                        $('#optionc4').attr('checked',false);
                        
                        $("#ocultar1").css("display","none");

                        $("#ocultar-valor").css("display","none");

                        $("#divResult").attr("style", "display:none");

                        $("#pague").css("display","");

                        $("#valor").val(0);

                        $("#cambio").val(0);
                    }

                }
            });
            //Fin Grabar items
    });
    //Fin Load

    //Pagar y grabar factura
    $("#pagar").click(function(){

        var $btn = $(this);
        if($btn.prop('disabled')) return;
        $btn.prop('disabled', true).text('Procesando...');

        var concepto = $("#concepto").val();
        var documento = $("#documentoID").val();
        var consecutivo = $("#consecutivo").val();
        var pago_id = $('input[name="options"]:checked').val();
        var total = $("#total").val();
        var transaccion_id = $("#transaccion_id").val();
        var mesa = {{ $mesa }};
        
        
        //capturo los id y valores de las multiples formas de pago
        const valoresCheck = [];

        $('input[name="pago[]"]:checked').each(function(){
            valoresCheck.push({"id":Number(this.value), "valor":Number($("#valor"+this.value).val())});
        });

        //console.log(valoresCheck);
 
        //Grabar tabla transactions (encabezado de la factura)
        srcRoute = "{{ route('facturacion.store') }}"

        $.ajax({
                    url: srcRoute,
                    type: 'POST',
                    dataType: "json",
                    data: {

                        concepto_id:concepto,
                        documento_id:documento,
                        consecutivo:consecutivo,
                        pago_id:pago_id,
                        valor:total,
                        transaccion_id:transaccion_id,
                        formas_pago:valoresCheck,
                        mesa:mesa

                    },

                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                    
                    success:function(data){

                        if(data.error){
                            $btn.prop('disabled', false).text('Pagar');
                            alertify.set('notifier','position', 'top-center');
                            alertify.error(data.message);
                            return;
                        }

                        $('#exampleModal').modal('hide');

                        var transactionId = data.transaction_id;

                        Swal.fire({
                            title: '¡Venta registrada!',
                            text: '¿Desea imprimir el ticket?',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#aaa',
                            confirmButtonText: 'Sí, imprimir',
                            cancelButtonText: 'No, continuar'
                        }).then(function(result) {

                            if(result.isConfirmed && transactionId){
                                $.ajax({
                                    url: '/ticket/imprimir/' + transactionId,
                                    type: 'POST',
                                    headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                                    success: function(res){
                                        if(res.error){
                                            Swal.fire('Error', res.message, 'error');
                                        }
                                    },
                                    error: function(xhr){
                                        Swal.fire('Error de impresión', xhr.responseJSON ? xhr.responseJSON.message : 'No se pudo conectar con la impresora.', 'error');
                                    }
                                });
                            }

                            $(location).attr('href', "{{ route('facturacion.index') }}");
                        });

                    }

        })


    });

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
                        alertify.error(`${data.message}`);                        

                        $('#producto').focus();

                        var valor = ''
                        var total = 0
                        var valorProducto = 0
                        var subTotal = 0
                        var impuestos = 0
                        var descuento = 0

                        data.productos.forEach(element => {

                            subTotal = subTotal + (element.baseunitario * element.cantidad) - ((element.baseunitario * element.cantidad) * (element.descuento/100))

                            impuestos = impuestos + (((element.baseunitario * element.cantidad)- ((element.baseunitario * element.cantidad)*(element.descuento/100))) * (element.impuesto/100)) 
                            
                            valorProducto = (element.preciounitario * element.cantidad) - ((element.preciounitario * element.cantidad) * (element.descuento/100))

                            descuento = descuento + ((element.preciounitario * element.cantidad) * (element.descuento/100))

                            total = total + valorProducto

                            valor += `<tr>
                                        <td>${element.nombre}</td>
                                        <td>${element.cantidad}</td>
                                        <td>${element.preciounitario}</td>
                                        <td>${element.descuento}%</td>
                                        <td>${valorProducto.toFixed(2)}</td>
                                        <td>
                                            <form class="delete-form" data-route="/facturacion/${element.id}/${element.consecutivo_id}" >
                                                @method("delete") 
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fa-solid fa-trash-can"></i></button>    
                                            </form>
                                        </td>
                                    </tr>`;

                         });
                        
                            $('#subtotal').val(Math.round(subTotal));                                  
                            $('#impuestos').val(Math.round(impuestos));
                            $('#total').val(total.toFixed(2));

                            $('#resumenTotal').val(Math.round(total));
                            $('#resumenDescuento').val(Math.round(descuento));
                            $('#resumenImpuesto').val(Math.round(impuestos));
                            $('#resumenSubtotal').val(Math.round(subTotal));

                            $("#tbodyProducto").html(valor);
                            
                            $("#valor").on("keyup change", function(e) {
            
                                var valor = $("#valor").val();

                                var cambio = (valor - total)

                                $("#ocultar1").css("display","");                            

                                if(cambio >= 0){

                                    $("#pague").css("display","");
                                    $("#cambio").val(cambio);
                                }else{

                                    $("#pague").css("display","none");
                                    $("#ocultar1").css("display","none");
                                }

                            })
                    
                  }
              });

    });
    //Fin Delete items

    //Llamar modal
    $(document).on('click','#openModal',function(e){
        
        $("#cambio").val("");
        
        $("#valor").val("");

        $("#pague").css("display","none");
        
        $("#ocultar1").css("display","none");

        $('#exampleModal').modal('show'); //abrir
    });    
    //Fin llamado modal

</script>

@endsection






