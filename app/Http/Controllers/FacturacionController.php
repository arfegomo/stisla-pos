<?php

namespace App\Http\Controllers;

use App\Concepto;
use App\Consecutivo;
use App\DetailTransaction;
use App\Empresa;
use App\FormaPago;
use App\Mesa;
use App\Producto;
use App\Receta;
use App\SocioNegocio;
use App\Temporary;
use App\Transaccion;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpParser\Node\Stmt\TryCatch;

class FacturacionController extends Controller
{
    public function __construct()
    {
        
        //$this->middleware('permission:ver-facturacion|crear-facturacion|editar-facturacion|borrar-facturacion',['only'=>['index','indexMesas']]);
        //$this->middleware('permission:crear-facturacion',['only'=>['create']]);
        //$this->middleware('permission:editar-facturacion',['only'=>['edit','update']]);

    }

    public function index(){

            $mesa = 1000;

            $temporaries = Temporary::where("mesa_id", $mesa)->count('mesa_id');

            $conceptos = Concepto::pluck("nombre", "id")->all();

            if($temporaries > 0){
                
                $consecutivo = Temporary::where("mesa_id", $mesa)->distinct('mesa_id')->value('consecutivo_id');

                $temporaries = DB::table('temporaries')
                        ->join("socio_negocios","socio_negocios.documento", "=", "temporaries.documento_id")
                        ->join("conceptos", "conceptos.id", "=", "temporaries.concepto_id")
                        ->join("transacciones", "transacciones.id", "=", "conceptos.transaccion_id")
                        ->distinct("temporaries.consecutivo_id")
                        ->where("temporaries.consecutivo_id", "=", $consecutivo)
                        ->select("temporaries.consecutivo_id","temporaries.concepto_id","socio_negocios.documento","socio_negocios.nombres","socio_negocios.apellidos","conceptos.transaccion_id")
                        ->get();

                return view("facturacion.close", compact("conceptos","consecutivo","temporaries","mesa"));

            }else{

                $consecutivo = Consecutivo::create();

                $consecutivo = $consecutivo->id;

                return view("facturacion.index", compact("conceptos","consecutivo","mesa"));
                
            }
            
    }

    public function indexMesas(Request $request){

            $conceptos = Concepto::pluck("nombre", "id")->all();

            $consecutivo = Consecutivo::create();

            $consecutivo = $consecutivo->id;

            $mesa = $request->get("mesa");

            return view("facturacion.index", compact("conceptos","consecutivo","mesa"));

    }

    public function transaccionesProceso(){

        //DB::enableQueryLog();

        $temporaries = Temporary::with('mesa')->get();/*DB::table('temporaries')
                    ->join("socio_negocios","socio_negocios.documento", "=", "temporaries.documento_id")
                    ->distinct("temporaries.consecutivo_id")
                    ->select("temporaries.consecutivo_id","temporaries.concepto_id","socio_negocios.documento","socio_negocios.nombres","socio_negocios.apellidos")
                    ->get();*/
        
        //dd(DB::getQueryLog());

        return view("facturacion.open", compact("temporaries"));

    }

    public function listItems(Request $request){

        $temporal = DB::table("temporaries")
                                ->join('productos','productos.id', '=', 'temporaries.producto_id')
                                ->where('consecutivo_id', '=', "{$request->get('consecutivo')}")
                                ->select("productos.nombre", "temporaries.preciounitario","temporaries.cantidad","temporaries.descuento","temporaries.id","temporaries.consecutivo_id","temporaries.baseunitario", "temporaries.impuesto")
                                ->get();

            return response()->json([
                  
                "productos" => $temporal
            
            ]);

    }

    public function close(Request $request){

        $conceptos = Concepto::pluck("nombre", "id")->all();

        $consecutivo = $request->get('consecutivo');

        $temporaries = DB::table('temporaries')
                        ->join("socio_negocios","socio_negocios.documento", "=", "temporaries.documento_id")
                        ->join("conceptos", "conceptos.id", "=", "temporaries.concepto_id")
                        ->join("transacciones", "transacciones.id", "=", "conceptos.transaccion_id")
                        ->distinct("temporaries.consecutivo_id")
                        ->where("temporaries.consecutivo_id", "=", $consecutivo)
                        ->select("temporaries.consecutivo_id","temporaries.concepto_id","socio_negocios.documento","socio_negocios.nombres","socio_negocios.apellidos","conceptos.transaccion_id")
                        ->get();

        //dd($conceptos);

        $mesa = $request->get("mesa");

        return view("facturacion.close", compact("conceptos","consecutivo","temporaries","mesa"));

    }

    public function searchSocio(Request $request){

        if($request->get('search')){

            try {
                
                $query = $request->get('search');

                $socios = SocioNegocio::select("nombres", "apellidos", "documento")
                            ->where('nombres', 'LIKE', "%{$query}%")
                            ->orWhere('apellidos', 'LIKE', "%{$query}%")
                            ->get();
                
                            return response()->json($socios);

            } catch (\Throwable $e) {
                
                return response()->json([

                    "error" => $e

                ]);
            }

        }
        
    }

    public function searchProducto(Request $request){

        if($request->get('search')){

            try {
                
                $query = $request->get('search');
                $transaccion = $request->get('transaccion');

                switch($transaccion){

                    case 1:

                        $productos = Producto::select("nombre", "id", "precioventa1", "impuesto_id")
                            ->with('impuesto')
                            ->where('nombre', 'LIKE', "%{$query}%")
                            //->where('tipoproducto', $tipoproducto)
                            ->where('facturable', 1)
                            ->get();

                            return response()->json($productos);

                    break;        

                    case 2:

                        $productos = Producto::select("nombre", "id", "costoactual AS precioventa1", "impuesto_id")
                            ->with('impuesto')
                            ->where('nombre', 'LIKE', "%{$query}%")
                            ->where('tipoproducto', 1)
                            //->where('facturable', 1)
                            ->get();

                            return response()->json($productos);

                    break;        

                    case 3:

                        $productos = Producto::select("nombre", "id", "costoactual AS precioventa1", "impuesto_id")
                            ->with('impuesto')
                            ->where('nombre', 'LIKE', "%{$query}%")
                            ->whereIn('tipoproducto', [1, 2])
                            ->get();

                            return response()->json($productos);

                    break;

                    case 4:

                        $productos = Producto::select("nombre", "id", "costoactual AS precioventa1", "impuesto_id")
                            ->with('impuesto')
                            ->where('nombre', 'LIKE', "%{$query}%")
                            ->whereIn('tipoproducto', [1, 2])
                            ->get();

                            return response()->json($productos);

                    break;        

                    case 5:

                        $productos = Producto::select("nombre", "id", "precioventa1", "impuesto_id")
                            ->with('impuesto')
                            ->where('nombre', 'LIKE', "%{$query}%")
                            //->where('tipoproducto', $tipoproducto)
                            ->where('facturable', 1)
                            ->get();

                            return response()->json($productos);

                    break;        

                    case 6:

                        $productos = Producto::select("nombre", "id", "costoactual AS precioventa1", "impuesto_id")
                            ->with('impuesto')
                            ->where('nombre', 'LIKE', "%{$query}%")
                            ->where('tipoproducto', 1)
                            //->where('facturable', 1)
                            ->get();

                            return response()->json($productos);

                    break;        
                
                }

            } catch (\Throwable $e) {
                
                return response()->json([
                    
                    "error" =>  $e
                
                ]);

            }

        }
        
    }

    public function searchServiceProduct(Request $request){

        if($request->get('search')){

            try {
                
                $query = $request->get('search');

                $productos = Producto::select("nombre", "id", "precioventa1", "impuesto_id")
                            ->with('impuesto')
                            ->where('nombre', 'LIKE', "%{$query}%")
                            ->where('tipoproducto', 2)
                            ->get();
                
                            return response()->json($productos);

            } catch (\Throwable $e) {
                
                return response()->json([
                    
                    "error" =>  $e
                
                ]);

            }

        }
        
    }

    public function searchComponentProduct(Request $request){

        if($request->get('search')){

            try {
                
                $query = $request->get('search');

                $productos = Producto::select("nombre", "id", "precioventa1", "impuesto_id", "tipoproducto")
                            ->with('impuesto')
                            ->where('nombre', 'LIKE', "%{$query}%")
                            ->whereIn('tipoproducto', [1, 2])
                            ->get()
                            ->map(function($p) {
                                $tieneReceta = DB::table('recetas_has_productos')
                                    ->where('receta_id', $p->id)->exists();
                                $p->etiqueta = $p->tipoproducto == 1
                                    ? 'Insumo'
                                    : ($tieneReceta ? 'Sub-receta' : 'Servicio');
                                return $p;
                            });
                
                            return response()->json($productos);

            } catch (\Throwable $e) {
                
                return response()->json([
                    
                    "error" =>  $e
                
                ]);

            }

        }
        
    }

    public function addTemporal(Request $request){

        try {

            $this->validate($request, [

                'impuesto' => ['required'],
                'concepto' => ['required'],
                'documento' => ['required'],
                'cantidad' => ['required'],
                'descuento' => ['required'],
                'impuestoID' => ['required'],
                'precio' => ['required'],
                'productoID' => ['required'],
                'consecutivo' => ['required'],
                'mesa' => ['required']

            ]);

            // Validar stock en ventas (transaccion_id=1)
            $idTipoTransaccion = Concepto::where("id", $request->get('concepto'))->value("transaccion_id");
            $productoValidar = Producto::find($request->get('productoID'));

            if($idTipoTransaccion == 1){

                if($productoValidar && $productoValidar->tipoproducto == 1){
                    // Insumo directo: validar su propio stock descontando lo ya en carrito
                    $cantidadEnCarrito = Temporary::where('consecutivo_id', $request->get('consecutivo'))
                        ->where('producto_id', $request->get('productoID'))
                        ->sum('cantidad');
                    $disponible = $productoValidar->existenciactual - $cantidadEnCarrito;
                    if($disponible < $request->get('cantidad')){
                        return response()->json([
                            "error"       => true,
                            "transaccion" => "",
                            "productos"   => [],
                            "message"     => "Stock insuficiente de '{$productoValidar->nombre}'. Disponible: {$disponible}, Solicitado: {$request->get('cantidad')}."
                        ]);
                    }

                } elseif($productoValidar && DB::table('recetas_has_productos')->where('receta_id', $productoValidar->id)->exists()){
                    // Producto con receta (cualquier tipo): expandir recursivamente y validar insumos hoja
                    $visitados  = [];
                    $insumos    = $this->expandirIngredientes(
                        $request->get('productoID'),
                        (float) $request->get('cantidad'),
                        $visitados
                    );

                    $errores = [];
                    foreach($insumos as $insumo){
                        $cantidadEnCarrito = Temporary::where('consecutivo_id', $request->get('consecutivo'))
                            ->where('producto_id', $insumo['producto_id'])
                            ->sum('cantidad');
                        $disponible = $insumo['existenciactual'] - $cantidadEnCarrito;
                        if($disponible < $insumo['cantidad']){
                            $errores[] = "{$insumo['nombre']} (Disponible: {$disponible}, Requerido: {$insumo['cantidad']})";
                        }
                    }
                    if(!empty($errores)){
                        return response()->json([
                            "error"       => true,
                            "transaccion" => "",
                            "productos"   => [],
                            "message"     => "Stock insuficiente para '{$productoValidar->nombre}': " . implode(', ', $errores) . "."
                        ]);
                    }
                }
            }

            $temporary = Temporary::create([

                'consecutivo_id' => $request->get('consecutivo'),
                'producto_id' => $request->get('productoID'),
                'impuesto_id' => $request->get('impuestoID'),
                'concepto_id' => $request->get('concepto'),
                'documento_id' => $request->get('documento'),
                'cantidad' => $request->get('cantidad'),
                'descuento' => $request->get('descuento'),
                'impuesto' => $request->get('impuesto'),
                'preciounitario' => $request->get('precio'),
                'baseunitario' => ($request->get('precio') / (($request->get('impuesto')/100)+1)),
                'mesa_id' => $request->get('mesa')

            ]);

            $temporal = DB::table("temporaries")
                                ->join('productos','productos.id', '=', 'temporaries.producto_id')
                                ->where('consecutivo_id', '=', "{$request->get('consecutivo')}")
                                ->select("productos.nombre", "temporaries.preciounitario","temporaries.cantidad","temporaries.descuento","temporaries.id","temporaries.consecutivo_id","temporaries.baseunitario", "temporaries.impuesto")
                                ->get();

            $idTipoTransaccion = Concepto::where("id", $request->get('concepto'))->value("transaccion_id");

            return response()->json([
                  
                "productos" => $temporal,

                "transaccion" => $idTipoTransaccion,

                "message" => "¡Producto agregado!"
            
            ]);

        
        } catch (\Throwable $th) {

            return response()->json([

                "error"      => true,
                "transaccion" => "",
                "productos"  => [],
                "message"    => "Error al agregar producto: " . $th->getMessage()

            ]);

        }

    }

    public function destroy($id, $consecutivo)
    {
        $facturacion = Temporary::find($id);

        try {
            
            $facturacion->delete();

            $temporal = DB::table("temporaries")
                                ->join('productos','productos.id', '=', 'temporaries.producto_id')
                                ->where('consecutivo_id', '=', "{$consecutivo}")
                                ->select("productos.nombre", "temporaries.preciounitario","temporaries.cantidad","temporaries.descuento","temporaries.id","temporaries.consecutivo_id","temporaries.baseunitario", "temporaries.impuesto")
                                ->get();


            return response()->json([
                    
                "productos" =>  $temporal,

                "message" => "¡Producto eliminado!"
            
            ]);

        } catch ( Exception $ex ) {
            
            return response()->json([
                    
                "error" =>  $ex
            
            ]);
        }
        
    }

    public function store(Request $request){

        //Capturo consecutivo de la tabla temporal
        $consecutivoTemporal = $request->get('consecutivo');

        //Capturo la fecha actual del sistema
        $date = Carbon::now();

        try {
            
            $this->validate($request, [

                'concepto_id' => ['required'],
                'documento_id' => ['required']

            ]);

            DB::beginTransaction();

            //Grabo el encabezado de la factura
            $transaccionPrincipal = null;
            $transactions = Transaction::create([
                
                'concepto_id' => $request->get('concepto_id'),
                'documento_id' => $request->get('documento_id'),
                'user_id' => Auth::id(),
                'fecha' => $date->format("Y-m-d"),
                'hora' => $date->format("H:i:s"),
                'estado' => "N",
                'consecutivo' => Transaccion::where("id", $request->get('transaccion_id'))->max("consecutivo"),
                'observacion' => $request->get('observacion')               
            
            ]);

            $transaccionPrincipal = $transactions->id;

            //Retorno los items del documento actual desde la tabla temporal
            $temporaries = Temporary::all()->where("consecutivo_id", "=", $consecutivoTemporal);

            $withreceta = 0;
            $withoutreceta = 0;

            //if the transacction is exits then
            switch($request->get('transaccion_id')){

                case 1://ventas

                    // Validar stock suficiente antes de escribir cualquier dato
                    foreach($temporaries as $temporary){

                        if(DB::table('recetas_has_productos')->where('receta_id', $temporary->producto_id)->exists()){

                            // Expandir recursivamente y validar todos los insumos hoja
                            $visitados = [];
                            $insumos   = $this->expandirIngredientes(
                                $temporary->producto_id,
                                (float) $temporary->cantidad,
                                $visitados
                            );

                            foreach($insumos as $insumo){
                                if($insumo['existenciactual'] < $insumo['cantidad']){
                                    throw new \Exception(
                                        "Stock insuficiente del ingrediente '{$insumo['nombre']}'. ".
                                        "Disponible: {$insumo['existenciactual']}, Requerido: {$insumo['cantidad']}."
                                    );
                                }
                            }

                        } else {

                            // Insumo sin receta: validar su propio stock (tipoproducto=1)
                            $productoStock = Producto::find($temporary->producto_id);
                            if($productoStock->tipoproducto == 1 && $productoStock->existenciactual < $temporary->cantidad){
                                throw new \Exception(
                                    "Stock insuficiente del producto '{$productoStock->nombre}'. ".
                                    "Disponible: {$productoStock->existenciactual}, Requerido: {$temporary->cantidad}."
                                );
                            }

                        }

                    }

                    //I update the inventory for each item
                    foreach($temporaries as $temporary){

                        //Validate if the product has recipe
                        if(DB::table('recetas_has_productos')->where('receta_id', $temporary->producto_id)->exists()){

                            $withreceta = $withreceta + 1;

                            // Costo de la receta calculado recursivamente (incluye sub-recetas)
                            $visitadosCosto = [];
                            $costoReceta    = $this->calcularCostoReceta(
                                $temporary->producto_id, 1.0, $visitadosCosto
                            );

                            DetailTransaction::create([

                                'transaction_id' => $transactions->id,
                                'producto_id'    => $temporary->producto_id,
                                'impuesto_id'    => $temporary->impuesto_id,
                                'cantidad'       => $temporary->cantidad,
                                'descuento'      => $temporary->descuento,
                                'impuesto'       => $temporary->impuesto,
                                'preciounitario' => $temporary->preciounitario,
                                'baseunitario'   => $temporary->baseunitario,
                                'costoventa'     => $costoReceta,
                                'costopromedio'  => $costoReceta,

                            ]);

                            // Actualizar inventario y costo del producto elaborado
                            $producto = Producto::find($temporary->producto_id);
                            $producto->existenciactual = $producto->existenciactual - $temporary->cantidad;
                            $producto->costoactual     = $costoReceta;
                            $producto->save();

                            // Descontar ingredientes expandiendo recursivamente sub-recetas
                            $visitados = [];
                            $insumos   = $this->expandirIngredientes(
                                $temporary->producto_id,
                                (float) $temporary->cantidad,
                                $visitados
                            );

                            foreach($insumos as $insumo){
                                $ingrediente = Producto::find($insumo['producto_id']);
                                if($ingrediente){
                                    $ingrediente->existenciactual = $ingrediente->existenciactual - $insumo['cantidad'];
                                    $ingrediente->save();
                                }
                            }

                        }
                        //fin
                        
                        //if the product has not component
                        else{

                            $withoutreceta = $withoutreceta + 1;
                            //grabo el detalle de la transacción
                            DetailTransaction::create([
                                
                                'transaction_id' => $transactions->id,
                                'producto_id' => $temporary->producto_id,
                                'impuesto_id' => $temporary->impuesto_id,
                                'cantidad' => $temporary->cantidad,
                                'descuento' => $temporary->descuento,
                                'impuesto' => $temporary->impuesto,
                                'preciounitario' => $temporary->preciounitario,
                                'baseunitario' => $temporary->baseunitario,
                                'costoventa' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                'costopromedio' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                            
                            ]);   

                            //I get the amount current
                            $amountCurrent = Producto::where("id", $temporary->producto_id)->value("existenciactual");
                            //I update the inventory
                            $producto = Producto::find($temporary->producto_id);
                            $producto->existenciactual = ($amountCurrent - $temporary->cantidad);
                            $producto->costoactual = Producto::where('id', $temporary->producto_id)->value('costoactual');
                            $producto->save();

                        }
                        //fin

                    }
                    //fin

                    //valido la forma de pago si es diferente de múltiples formas de pago
                    if($request->get("pago_id") != 5){

                        FormaPago::create([

                            'pago_id' => $request->get("pago_id"),
                            'transaction_id' => $transactions->id,
                            'valor' => $request->get("valor")
            
                        ]);
                        //de lo contrario si selecciono múltiples formas de pago -> las grabamos
                        }else{

                            foreach($request->get("formas_pago") as $data){

                                FormaPago::create([

                                    'pago_id' => $data["id"],
                                    'transaction_id' => $transactions->id,
                                    'valor' => $data["valor"]
                    
                                ]);

                            }

                    }
                    //fin            

                    //En la transacción hay algún producto que tiene receta ?
                    if($withreceta >= 1){
            
                        //Grabo el encabezado de la entrada del produto terminado
                        $transactions = Transaction::create([
                            
                            'concepto_id' => 98,
                            'documento_id' => $request->get('documento_id'),
                            'user_id' => Auth::id(),
                            'fecha' => $date->format("Y-m-d"),
                            'hora' => $date->format("H:i:s"),
                            'estado' => "N",
                            'consecutivo' => Transaccion::where("id", $request->get('transaccion_id'))->max("consecutivo"),
                            'observacion' => $request->get('observacion')               
                        
                        ]);       

                        //Grabo los detalles de la entrada del producto terminado
                        foreach($temporaries as $temporary){

                            //Validate if the product has recipe
                            if(DB::table('recetas_has_productos')->where('receta_id', $temporary->producto_id)->exists()){
                        
                                DetailTransaction::create([
                                        
                                    'transaction_id' => $transactions->id,
                                    'producto_id' => $temporary->producto_id,
                                    'impuesto_id' => 4,
                                    'cantidad' => $temporary->cantidad,
                                    'descuento' => 0,
                                    'impuesto' => 0,
                                    'preciounitario' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                    'baseunitario' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                    'costoventa' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                    'costopromedio' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                
                                ]);   

                            }
                            
                            //I get the amount current
                            $amountCurrent = Producto::where("id", $temporary->producto_id)->value("existenciactual");
                            //I update the inventory
                            $producto = Producto::find($temporary->producto_id);
                            $producto->existenciactual = ($amountCurrent + $temporary->cantidad);
                            //$producto->costoactual = Producto::where('id', $temporary->producto_id)->value('costoactual');
                            $producto->save();
                        }
                        
                        //Grabo el encabezado de la salida de la materia prima
                        $transactions = Transaction::create([
                            
                            'concepto_id' => 99,
                            'documento_id' => $request->get('documento_id'),
                            'user_id' => Auth::id(),
                            'fecha' => $date->format("Y-m-d"),
                            'hora' => $date->format("H:i:s"),
                            'estado' => "N",
                            'consecutivo' => Transaccion::where("id", $request->get('transaccion_id'))->max("consecutivo"),
                            'observacion' => $request->get('observacion')               
                        
                        ]); 
                        
                        // Concepto 99: solo insumos directos del nivel principal.
                        // Las sub-recetas generan sus propios ciclos 99→98→97 internamente.
                        foreach($temporaries as $temporary){

                            if(DB::table('recetas_has_productos')->where('receta_id', $temporary->producto_id)->exists()){

                                $insumosDirectos = $this->generarTransaccionesSubReceta(
                                    $temporary->producto_id,
                                    (float) $temporary->cantidad,
                                    $request->get('documento_id'),
                                    Auth::id(),
                                    $date->format("Y-m-d"),
                                    $date->format("H:i:s"),
                                    Transaccion::where("id", $request->get('transaccion_id'))->max("consecutivo"),
                                    $request->get('observacion') ?? ''
                                );

                                // Registrar solo los insumos directos en el concepto 99 principal
                                foreach($insumosDirectos as $insumo){
                                    $costoInsumo = Producto::where('id', $insumo['producto_id'])->value('costoactual');
                                    DetailTransaction::create([
                                        'transaction_id' => $transactions->id,
                                        'producto_id'    => $insumo['producto_id'],
                                        'impuesto_id'    => 4,
                                        'cantidad'       => $insumo['cantidad'],
                                        'descuento'      => 0,
                                        'impuesto'       => 0,
                                        'preciounitario' => $costoInsumo,
                                        'baseunitario'   => $costoInsumo,
                                        'costoventa'     => $costoInsumo,
                                        'costopromedio'  => $costoInsumo,
                                    ]);
                                }

                            }

                        }

                    }

                    //si se grabó el encabezado actualizo el consecutivo
                    if($transactions){

                        DB::table("transacciones")
                            ->where("id", $request->get('transaccion_id'))
                            ->update(['consecutivo' => Transaccion::where("id", $request->get('transaccion_id'))->max("consecutivo") + 1]);
                    
                    }
                    //fin

                    $mesa = Mesa::find($request->get('mesa'));

                    $mesa->responsable = null;

                    $mesa->save();

                break;

                case 2://Compras

                    //I update the inventory for each item
                    foreach($temporaries as $temporary){

                        $producto       = Producto::find($temporary->producto_id);
                        $stockActual    = $producto->existenciactual;
                        $costoActual    = $producto->costoactual;
                        $valorCompra    = ($temporary->preciounitario * $temporary->cantidad) * (1 - $temporary->descuento / 100);
                        $costoUnitario  = $valorCompra / $temporary->cantidad;
                        $nuevoDenominador = $stockActual + $temporary->cantidad;
                        $costoPromedio  = $nuevoDenominador > 0
                            ? round((($stockActual * $costoActual) + $valorCompra) / $nuevoDenominador, 2)
                            : round($costoUnitario, 2);

                        DetailTransaction::create([

                            'transaction_id' => $transactions->id,
                            'producto_id'    => $temporary->producto_id,
                            'impuesto_id'    => $temporary->impuesto_id,
                            'cantidad'       => $temporary->cantidad,
                            'descuento'      => $temporary->descuento,
                            'impuesto'       => $temporary->impuesto,
                            'preciounitario' => $temporary->preciounitario,
                            'baseunitario'   => $temporary->baseunitario,
                            'costoventa'     => $costoUnitario,
                            'costopromedio'  => $costoPromedio,

                        ]);

                        $producto->existenciactual = $stockActual + $temporary->cantidad;
                        $producto->costoactual     = $costoPromedio;
                        $producto->save();

                    }

                    //valido la forma de pago si es diferente de múltiples formas de pago
                    if($request->get("pago_id") != 5){

                        FormaPago::create([

                            'pago_id' => $request->get("pago_id"),
                            'transaction_id' => $transactions->id,
                            'valor' => $request->get("valor")
            
                        ]);
                        //de lo contrario si selecciono múltiples formas de pago -> las grabamos
                        }else{

                            foreach($request->get("formas_pago") as $data){

                                FormaPago::create([

                                    'pago_id' => $data["id"],
                                    'transaction_id' => $transactions->id,
                                    'valor' => $data["valor"]
                    
                                ]);

                            }

                    }
                    //fin            

                break;

                case 3://Salidas
                    
                        //I update the inventory for each item
                        foreach($temporaries as $temporary){

                            //grabo el detalle de la transacción
                            DetailTransaction::create([
                        
                                'transaction_id' => $transactions->id,
                                'producto_id' => $temporary->producto_id,
                                'impuesto_id' => $temporary->impuesto_id,
                                'cantidad' => $temporary->cantidad,
                                'descuento' => $temporary->descuento,
                                'impuesto' => $temporary->impuesto,
                                'preciounitario' => $temporary->preciounitario,
                                'baseunitario' => $temporary->baseunitario,
                                'costoventa' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                'costopromedio' => Producto::where('id', $temporary->producto_id)->value('costoactual'),                    
                        
                            ]);

    
                            //I get the amount current
                            $amountCurrent = Producto::where("id", $temporary->producto_id)->value("existenciactual");
                            //I update the inventory
                            $producto = Producto::find($temporary->producto_id);
                            $producto->existenciactual = ($amountCurrent - $temporary->cantidad);
                            $producto->save();
    
                        }
    
                break;

                case 4://Entradas
                    
                    //I update the inventory for each item
                    foreach($temporaries as $temporary){

                        //grabo el detalle de la transacción
                        DetailTransaction::create([
                    
                            'transaction_id' => $transactions->id,
                            'producto_id' => $temporary->producto_id,
                            'impuesto_id' => $temporary->impuesto_id,
                            'cantidad' => $temporary->cantidad,
                            'descuento' => $temporary->descuento,
                            'impuesto' => $temporary->impuesto,
                            'preciounitario' => $temporary->preciounitario,
                            'baseunitario' => $temporary->baseunitario,
                            'costoventa' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                            'costopromedio' => Producto::where('id', $temporary->producto_id)->value('costoactual'),                    
                    
                        ]);


                        //I get the amount current
                        $amountCurrent = Producto::where("id", $temporary->producto_id)->value("existenciactual");
                        //I update the inventory
                        $producto = Producto::find($temporary->producto_id);
                        $producto->existenciactual = ($amountCurrent + $temporary->cantidad);
                        $producto->save();

                    }

                break;

                case 5://Devoluciones ventas

                   //I update the inventory for each item
                   foreach($temporaries as $temporary){

                        //Validate if the product has recipe
                        if(DB::table('recetas_has_productos')->where('receta_id', $temporary->producto_id)->exists()){

                            $withreceta = $withreceta + 1;

                            // Costo recursivo de la receta
                            $visitadosCosto = [];
                            $costoRecetaDev = $this->calcularCostoReceta(
                                $temporary->producto_id, 1.0, $visitadosCosto
                            );

                            DetailTransaction::create([

                                'transaction_id' => $transactions->id,
                                'producto_id'    => $temporary->producto_id,
                                'impuesto_id'    => $temporary->impuesto_id,
                                'cantidad'       => $temporary->cantidad,
                                'descuento'      => $temporary->descuento,
                                'impuesto'       => $temporary->impuesto,
                                'preciounitario' => $temporary->preciounitario,
                                'baseunitario'   => $temporary->baseunitario,
                                'costoventa'     => $costoRecetaDev,
                                'costopromedio'  => $costoRecetaDev,

                            ]);

                            $amountCurrent = Producto::where("id", $temporary->producto_id)->value("existenciactual");
                            $producto = Producto::find($temporary->producto_id);
                            $producto->existenciactual = ($amountCurrent + $temporary->cantidad);
                            $producto->save();

                            // Devolver ingredientes al inventario expandiendo sub-recetas recursivamente
                            $visitados = [];
                            $insumos   = $this->expandirIngredientes(
                                $temporary->producto_id,
                                (float) $temporary->cantidad,
                                $visitados
                            );

                            foreach($insumos as $insumo){
                                $ingrediente = Producto::find($insumo['producto_id']);
                                if($ingrediente){
                                    $ingrediente->existenciactual = $ingrediente->existenciactual + $insumo['cantidad'];
                                    $ingrediente->save();
                                }
                            }

                        }
                        //fin
                        
                        //if the product has not component
                        else{

                                $withoutreceta = $withoutreceta + 1;
                                //grabo el detalle de la transacción
                                    DetailTransaction::create([
                                        
                                        'transaction_id' => $transactions->id,
                                        'producto_id' => $temporary->producto_id,
                                        'impuesto_id' => $temporary->impuesto_id,
                                        'cantidad' => $temporary->cantidad,
                                        'descuento' => $temporary->descuento,
                                        'impuesto' => $temporary->impuesto,
                                        'preciounitario' => $temporary->preciounitario,
                                        'baseunitario' => $temporary->baseunitario,
                                        'costoventa' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                        'costopromedio' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                    
                                    ]);   

                                //I get the amount current
                                $amountCurrent = Producto::where("id", $temporary->producto_id)->value("existenciactual");
                                //I update the inventory
                                $producto = Producto::find($temporary->producto_id);
                                $producto->existenciactual = ($amountCurrent + $temporary->cantidad);
                                $producto->costoactual = Producto::where('id', $temporary->producto_id)->value('costoactual');
                                $producto->save();

                        }
                        //fin

                    }
                    //fin

                    //valido la forma de pago si es diferente de múltiples formas de pago
                    if($request->get("pago_id") != 5){

                        FormaPago::create([

                            'pago_id' => $request->get("pago_id"),
                            'transaction_id' => $transactions->id,
                            'valor' => $request->get("valor")
            
                        ]);
                        //de lo contrario si selecciono múltiples formas de pago -> las grabamos
                        }else{

                            foreach($request->get("formas_pago") as $data){

                                FormaPago::create([

                                    'pago_id' => $data["id"],
                                    'transaction_id' => $transactions->id,
                                    'valor' => $data["valor"]
                    
                                ]);

                            }

                    }
                    //fin            

                    //En la transacción hay algún producto que tiene receta ?
                    if($withreceta >= 1){
            
                        //Grabo el encabezado de la salida del produto terminado
                        $transactions = Transaction::create([
                            
                            'concepto_id' => 97,
                            'documento_id' => $request->get('documento_id'),
                            'user_id' => Auth::id(),
                            'fecha' => $date->format("Y-m-d"),
                            'hora' => $date->format("H:i:s"),
                            'estado' => "N",
                            'consecutivo' => Transaccion::where("id", $request->get('transaccion_id'))->max("consecutivo"),
                            'observacion' => $request->get('observacion')               
                        
                        ]);       

                        //Grabo los detalles de la salida del producto terminado
                        foreach($temporaries as $temporary){

                            //Validate if the product has recipe
                            if(DB::table('recetas_has_productos')->where('receta_id', $temporary->producto_id)->exists()){
                        
                                DetailTransaction::create([
                                        
                                    'transaction_id' => $transactions->id,
                                    'producto_id' => $temporary->producto_id,
                                    'impuesto_id' => 4,
                                    'cantidad' => $temporary->cantidad,
                                    'descuento' => 0,
                                    'impuesto' => 0,
                                    'preciounitario' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                    'baseunitario' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                    'costoventa' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                    'costopromedio' => Producto::where('id', $temporary->producto_id)->value('costoactual'),
                                
                                ]);   

                            }
                            
                            //I get the amount current
                            $amountCurrent = Producto::where("id", $temporary->producto_id)->value("existenciactual");
                            //I update the inventory
                            $producto = Producto::find($temporary->producto_id);
                            $producto->existenciactual = ($amountCurrent - $temporary->cantidad);
                            //$producto->costoactual = Producto::where('id', $temporary->producto_id)->value('costoactual');
                            $producto->save();
                        }
                        
                        //Grabo el encabezado de la entrada de la materia prima
                        $transactions = Transaction::create([
                            
                            'concepto_id' => 96,
                            'documento_id' => $request->get('documento_id'),
                            'user_id' => Auth::id(),
                            'fecha' => $date->format("Y-m-d"),
                            'hora' => $date->format("H:i:s"),
                            'estado' => "N",
                            'consecutivo' => Transaccion::where("id", $request->get('transaccion_id'))->max("consecutivo"),
                            'observacion' => $request->get('observacion')               
                        
                        ]); 
                        
                        // Concepto 96: solo insumos directos del nivel principal.
                        // Sub-recetas generan sus ciclos 98→97→96 internamente.
                        foreach($temporaries as $temporary){

                            if(DB::table('recetas_has_productos')->where('receta_id', $temporary->producto_id)->exists()){

                                $insumosDirectos = $this->generarTransaccionesSubRecetaDevolucion(
                                    $temporary->producto_id,
                                    (float) $temporary->cantidad,
                                    $request->get('documento_id'),
                                    Auth::id(),
                                    $date->format("Y-m-d"),
                                    $date->format("H:i:s"),
                                    Transaccion::where("id", $request->get('transaccion_id'))->max("consecutivo"),
                                    $request->get('observacion') ?? ''
                                );

                                foreach($insumosDirectos as $insumo){
                                    $costoInsumo = Producto::where('id', $insumo['producto_id'])->value('costoactual');
                                    DetailTransaction::create([
                                        'transaction_id' => $transactions->id,
                                        'producto_id'    => $insumo['producto_id'],
                                        'impuesto_id'    => 4,
                                        'cantidad'       => $insumo['cantidad'],
                                        'descuento'      => 0,
                                        'impuesto'       => 0,
                                        'preciounitario' => $costoInsumo,
                                        'baseunitario'   => $costoInsumo,
                                        'costoventa'     => $costoInsumo,
                                        'costopromedio'  => $costoInsumo,
                                    ]);
                                }

                            }

                        }

                    }

                    //si se grabó el encabezado actualizo el consecutivo
                    if($transactions){

                        DB::table("transacciones")
                            ->where("id", $request->get('transaccion_id'))
                            ->update(['consecutivo' => Transaccion::where("id", $request->get('transaccion_id'))->max("consecutivo") + 1]);
                    
                    }

                break;

                case 6://Devoluciones compras

                    //I update the inventory for each item
                    foreach($temporaries as $temporary){

                        $producto       = Producto::find($temporary->producto_id);
                        $stockActual    = $producto->existenciactual;
                        $costoActual    = $producto->costoactual;
                        $valorCompra    = ($temporary->preciounitario * $temporary->cantidad) * (1 - $temporary->descuento / 100);
                        $costoUnitario  = $valorCompra / $temporary->cantidad;
                        $nuevoDenominador = $stockActual - $temporary->cantidad;
                        $costoPromedio  = $nuevoDenominador > 0
                            ? round((($stockActual * $costoActual) - $valorCompra) / $nuevoDenominador, 2)
                            : $costoActual;

                        DetailTransaction::create([

                            'transaction_id' => $transactions->id,
                            'producto_id'    => $temporary->producto_id,
                            'impuesto_id'    => $temporary->impuesto_id,
                            'cantidad'       => $temporary->cantidad,
                            'descuento'      => $temporary->descuento,
                            'impuesto'       => $temporary->impuesto,
                            'preciounitario' => $temporary->preciounitario,
                            'baseunitario'   => $temporary->baseunitario,
                            'costoventa'     => $costoUnitario,
                            'costopromedio'  => $costoPromedio,

                        ]);

                        $producto->existenciactual = $stockActual - $temporary->cantidad;
                        $producto->costoactual     = $costoPromedio;
                        $producto->save();

                    }

                    //valido la forma de pago si es diferente de múltiples formas de pago
                    if($request->get("pago_id") != 5){

                        FormaPago::create([

                            'pago_id' => $request->get("pago_id"),
                            'transaction_id' => $transactions->id,
                            'valor' => $request->get("valor")
            
                        ]);
                        //de lo contrario si selecciono múltiples formas de pago -> las grabamos
                        }else{

                            foreach($request->get("formas_pago") as $data){

                                FormaPago::create([

                                    'pago_id' => $data["id"],
                                    'transaction_id' => $transactions->id,
                                    'valor' => $data["valor"]
                    
                                ]);

                            }

                    }
                    //fin            

                break;

            }

            //eliminamos de la tabla temporal la transacción
            $temporary = Temporary::where("consecutivo_id", $consecutivoTemporal);
            
            $temporary->delete();

            DB::commit();

            return response()->json([

                "message"        => "¡Transacción grabada con éxito!",
                "transaction_id" => $transaccionPrincipal,

            ]);

        } catch (\Throwable $th) {

            DB::rollBack();

            \Log::error('FacturacionController@store: ' . $th->getMessage(), [
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);

            return response()->json([

                "error"   => $th->getMessage(),
                "message" => "¡Error en la transacción: " . $th->getMessage() . "!"

            ]);
        }

    }

    public function historial(){

        $ventas = DB::table('transactions')
            ->join('conceptos', 'conceptos.id', '=', 'transactions.concepto_id')
            ->join('transacciones', 'transacciones.id', '=', 'conceptos.transaccion_id')
            ->join('socio_negocios', 'socio_negocios.documento', '=', 'transactions.documento_id')
            ->join('users', 'users.id', '=', 'transactions.user_id')
            ->leftJoin('forma_pagos', 'forma_pagos.transaction_id', '=', 'transactions.id')
            ->whereIn('conceptos.transaccion_id', [1, 2, 3, 4, 5, 6])
            ->groupBy('transactions.id', 'transactions.fecha', 'transactions.hora',
                      'transactions.consecutivo', 'socio_negocios.nombres',
                      'socio_negocios.apellidos', 'users.name', 'transacciones.nombre',
                      'conceptos.nombre')
            ->orderBy('transactions.id', 'desc')
            ->select(
                'transactions.id',
                'transactions.fecha',
                'transactions.hora',
                'transactions.consecutivo',
                'transacciones.nombre AS tipo',
                'conceptos.nombre AS concepto',
                DB::raw("CONCAT(socio_negocios.nombres, ' ', socio_negocios.apellidos) AS cliente"),
                'users.name AS cajero',
                DB::raw('SUM(forma_pagos.valor) AS total')
            )
            ->get();

        return view('facturacion.historial', compact('ventas'));

    }

    /**
     * Expande recursivamente una receta hasta llegar solo a insumos (tipoproducto=1).
     * Si un ingrediente es un servicio (tipoproducto=2) con su propia receta,
     * se expande en sus sub-ingredientes multiplicando la cantidad.
     * Retorna: [['producto_id' => X, 'cantidad' => Y, 'nombre' => Z], ...]
     */
    private function generarTransaccionesSubRecetaDevolucion(
        int    $recetaId,
        float  $cantidad,
        string $docId,
        int    $userId,
        string $fecha,
        string $hora,
        int    $consecutivo,
        string $observacion
    ): array {
        $ingredientes = DB::table('recetas_has_productos')
            ->join('productos', 'productos.id', '=', 'recetas_has_productos.producto_id')
            ->where('recetas_has_productos.receta_id', $recetaId)
            ->select('productos.id', 'productos.nombre', 'productos.costoactual',
                     'recetas_has_productos.cantidad')
            ->get();

        $insumosDirectos = [];

        foreach ($ingredientes as $ing) {
            $cantTotal      = $ing->cantidad * $cantidad;
            $tieneSubReceta = DB::table('recetas_has_productos')
                ->where('receta_id', $ing->id)->exists();

            if ($tieneSubReceta) {
                $visitadosCosto = [];
                $costoUnit      = $this->calcularCostoReceta($ing->id, 1.0, $visitadosCosto);

                // Concepto 98 — Entrada PT: Arepa "des-consumida" (regresa)
                $tx98 = Transaction::create([
                    'concepto_id'  => 98, 'documento_id' => $docId, 'user_id' => $userId,
                    'fecha' => $fecha, 'hora' => $hora, 'estado' => 'N',
                    'consecutivo'  => $consecutivo,
                    'observacion'  => 'Dev. sub-receta Entrada PT: ' . $ing->nombre,
                ]);
                DetailTransaction::create([
                    'transaction_id' => $tx98->id, 'producto_id' => $ing->id,
                    'impuesto_id' => 4, 'cantidad' => $cantTotal,
                    'descuento' => 0, 'impuesto' => 0,
                    'preciounitario' => $costoUnit, 'baseunitario' => $costoUnit,
                    'costoventa' => $costoUnit, 'costopromedio' => $costoUnit,
                ]);

                // Concepto 97 — Salida PT: Arepa "des-producida"
                $tx97 = Transaction::create([
                    'concepto_id'  => 97, 'documento_id' => $docId, 'user_id' => $userId,
                    'fecha' => $fecha, 'hora' => $hora, 'estado' => 'N',
                    'consecutivo'  => $consecutivo,
                    'observacion'  => 'Dev. sub-receta Salida PT: ' . $ing->nombre,
                ]);
                DetailTransaction::create([
                    'transaction_id' => $tx97->id, 'producto_id' => $ing->id,
                    'impuesto_id' => 4, 'cantidad' => $cantTotal,
                    'descuento' => 0, 'impuesto' => 0,
                    'preciounitario' => $costoUnit, 'baseunitario' => $costoUnit,
                    'costoventa' => $costoUnit, 'costopromedio' => $costoUnit,
                ]);

                // Concepto 96 — Entrada MP: ingredientes de la sub-receta regresan
                $subInsumos = $this->generarTransaccionesSubRecetaDevolucion(
                    $ing->id, $cantTotal, $docId, $userId, $fecha, $hora, $consecutivo, $observacion
                );

                if (!empty($subInsumos)) {
                    $tx96 = Transaction::create([
                        'concepto_id'  => 96, 'documento_id' => $docId, 'user_id' => $userId,
                        'fecha' => $fecha, 'hora' => $hora, 'estado' => 'N',
                        'consecutivo'  => $consecutivo,
                        'observacion'  => 'Dev. sub-receta MP: ' . $ing->nombre,
                    ]);
                    foreach ($subInsumos as $si) {
                        $costo = Producto::where('id', $si['producto_id'])->value('costoactual');
                        DetailTransaction::create([
                            'transaction_id' => $tx96->id, 'producto_id' => $si['producto_id'],
                            'impuesto_id' => 4, 'cantidad' => $si['cantidad'],
                            'descuento' => 0, 'impuesto' => 0,
                            'preciounitario' => $costo, 'baseunitario' => $costo,
                            'costoventa' => $costo, 'costopromedio' => $costo,
                        ]);
                    }
                }

            } else {
                $insumosDirectos[] = ['producto_id' => $ing->id, 'cantidad' => $cantTotal];
            }
        }

        return $insumosDirectos;
    }

    private function generarTransaccionesSubReceta(
        int    $recetaId,
        float  $cantidad,
        string $docId,
        int    $userId,
        string $fecha,
        string $hora,
        int    $consecutivo,
        string $observacion
    ): array {
        $ingredientes = DB::table('recetas_has_productos')
            ->join('productos', 'productos.id', '=', 'recetas_has_productos.producto_id')
            ->where('recetas_has_productos.receta_id', $recetaId)
            ->select('productos.id', 'productos.nombre', 'productos.costoactual',
                     'recetas_has_productos.cantidad')
            ->get();

        $insumosDirectos = [];

        foreach ($ingredientes as $ing) {
            $cantTotal      = $ing->cantidad * $cantidad;
            $tieneSubReceta = DB::table('recetas_has_productos')
                ->where('receta_id', $ing->id)->exists();

            if ($tieneSubReceta) {
                // Sub-receta: generar ciclo 99→98→97 recursivamente

                $subInsumos = $this->generarTransaccionesSubReceta(
                    $ing->id, $cantTotal, $docId, $userId, $fecha, $hora, $consecutivo, $observacion
                );

                // Concepto 99 — Salida MP para producir la sub-receta
                if (!empty($subInsumos)) {
                    $tx99 = Transaction::create([
                        'concepto_id'  => 99, 'documento_id' => $docId, 'user_id' => $userId,
                        'fecha'        => $fecha, 'hora' => $hora, 'estado' => 'N',
                        'consecutivo'  => $consecutivo,
                        'observacion'  => 'Sub-prod MP: ' . $ing->nombre,
                    ]);
                    foreach ($subInsumos as $si) {
                        $costo = Producto::where('id', $si['producto_id'])->value('costoactual');
                        DetailTransaction::create([
                            'transaction_id' => $tx99->id, 'producto_id' => $si['producto_id'],
                            'impuesto_id' => 4, 'cantidad' => $si['cantidad'],
                            'descuento' => 0, 'impuesto' => 0,
                            'preciounitario' => $costo, 'baseunitario' => $costo,
                            'costoventa' => $costo, 'costopromedio' => $costo,
                        ]);
                    }
                }

                // Concepto 98 — Entrada PT: sub-receta producida
                $visitadosCosto = [];
                $costoUnit      = $this->calcularCostoReceta($ing->id, 1.0, $visitadosCosto);
                $tx98 = Transaction::create([
                    'concepto_id'  => 98, 'documento_id' => $docId, 'user_id' => $userId,
                    'fecha'        => $fecha, 'hora' => $hora, 'estado' => 'N',
                    'consecutivo'  => $consecutivo,
                    'observacion'  => 'Sub-prod Entrada PT: ' . $ing->nombre,
                ]);
                DetailTransaction::create([
                    'transaction_id' => $tx98->id, 'producto_id' => $ing->id,
                    'impuesto_id' => 4, 'cantidad' => $cantTotal,
                    'descuento' => 0, 'impuesto' => 0,
                    'preciounitario' => $costoUnit, 'baseunitario' => $costoUnit,
                    'costoventa' => $costoUnit, 'costopromedio' => $costoUnit,
                ]);

                // Concepto 97 — Salida PT: sub-receta consumida en la receta padre
                $tx97 = Transaction::create([
                    'concepto_id'  => 97, 'documento_id' => $docId, 'user_id' => $userId,
                    'fecha'        => $fecha, 'hora' => $hora, 'estado' => 'N',
                    'consecutivo'  => $consecutivo,
                    'observacion'  => 'Sub-prod Salida PT: ' . $ing->nombre,
                ]);
                DetailTransaction::create([
                    'transaction_id' => $tx97->id, 'producto_id' => $ing->id,
                    'impuesto_id' => 4, 'cantidad' => $cantTotal,
                    'descuento' => 0, 'impuesto' => 0,
                    'preciounitario' => $costoUnit, 'baseunitario' => $costoUnit,
                    'costoventa' => $costoUnit, 'costopromedio' => $costoUnit,
                ]);

            } else {
                // Insumo hoja: devolver al nivel padre para que lo incluya en su concepto 99
                $insumosDirectos[] = ['producto_id' => $ing->id, 'cantidad' => $cantTotal];
            }
        }

        return $insumosDirectos;
    }

    private function calcularCostoReceta(int $recetaId, float $factor = 1.0, array &$visitados = []): float
    {
        if (in_array($recetaId, $visitados)) return 0;
        $visitados[] = $recetaId;

        $ingredientes = DB::table('recetas_has_productos')
            ->join('productos', 'productos.id', '=', 'recetas_has_productos.producto_id')
            ->where('recetas_has_productos.receta_id', $recetaId)
            ->select('productos.id', 'productos.costoactual', 'recetas_has_productos.cantidad')
            ->get();

        $costo = 0;
        foreach($ingredientes as $ing) {
            $cantTotal      = $ing->cantidad * $factor;
            $tieneSubReceta = DB::table('recetas_has_productos')
                ->where('receta_id', $ing->id)->exists();

            if($tieneSubReceta) {
                // Calcular costo unitario de la sub-receta y actualizar su costoactual
                $visitadosSub   = [];
                $costoUnitario  = $this->calcularCostoReceta($ing->id, 1.0, $visitadosSub);
                Producto::where('id', $ing->id)->update(['costoactual' => round($costoUnitario, 2)]);
                $costo += $costoUnitario * $cantTotal;
            } else {
                $costo += $ing->costoactual * $cantTotal;
            }
        }
        return $costo;
    }

    private function expandirIngredientes(int $recetaId, float $cantidadFactor, array &$visitados = []): array
    {
        if (in_array($recetaId, $visitados)) return []; // evitar ciclos infinitos
        $visitados[] = $recetaId;

        $ingredientes = DB::table('recetas_has_productos')
            ->join('productos', 'productos.id', '=', 'recetas_has_productos.producto_id')
            ->where('recetas_has_productos.receta_id', $recetaId)
            ->select('productos.id', 'productos.nombre', 'productos.tipoproducto',
                     'productos.existenciactual', 'recetas_has_productos.cantidad')
            ->get();

        $resultado = [];

        foreach ($ingredientes as $ing) {
            $cantTotal = $ing->cantidad * $cantidadFactor;

            // Es sub-receta si tiene sus propios ingredientes en recetas_has_productos
            $esSubReceta = DB::table('recetas_has_productos')
                ->where('receta_id', $ing->id)
                ->exists();

            if ($esSubReceta) {
                // Expandir sub-receta recursivamente
                $sub = $this->expandirIngredientes($ing->id, $cantTotal, $visitados);
                $resultado = array_merge($resultado, $sub);
            } else {
                // Insumo hoja — agregar o acumular
                $key = $ing->id;
                if (isset($resultado[$key])) {
                    $resultado[$key]['cantidad'] += $cantTotal;
                } else {
                    $resultado[$key] = [
                        'producto_id'      => $ing->id,
                        'nombre'           => $ing->nombre,
                        'cantidad'         => $cantTotal,
                        'existenciactual'  => $ing->existenciactual,
                    ];
                }
            }
        }

        return $resultado;
    }

    public function mesas(){

        $mesas = Mesa::all();

        return view("mesas.index", compact("mesas"));

    }

}
