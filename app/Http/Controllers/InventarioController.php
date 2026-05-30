<?php

namespace App\Http\Controllers;

use App\Cierre;
use App\Concepto;
use App\Producto;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InventarioController extends Controller
{
    
    private function fechaInicioPeriodo($ultimoCierre)
    {
        if (!$ultimoCierre) return null;
        // El período abierto empieza el día SIGUIENTE al último cierre
        return Carbon::parse($ultimoCierre->fecha_cierre)->addDay()->startOfDay()->toDateTimeString();
    }

    public function index(){

        $ultimoCierre  = Cierre::orderBy('fecha_cierre', 'desc')->first();
        $fechaCierre   = $this->fechaInicioPeriodo($ultimoCierre);

        $productos = Producto::where('tipoproducto',1)->get();

        foreach ($productos as $producto) {

            $transacciones = Producto::with(['transactions' => function($query) use ($fechaCierre) {
                if($fechaCierre){
                    $query->where('transactions.created_at', '>=', $fechaCierre);
                }
                $query->orderBy('transactions.created_at', 'ASC');
            }])->where('id', $producto->id)->get();

            // Arranca desde el saldo del último cierre
            $saldo = $producto->existenciainicial ?? 0;

            foreach($transacciones as $kardex){
                foreach($kardex['transactions'] as $item){
                    switch($item["concepto"]["transaccion_id"]){
                        case(1): $saldo = $saldo - $item["pivot"]["cantidad"]; break;
                        case(2): $saldo = $saldo + $item["pivot"]["cantidad"]; break;
                        case(3): $saldo = $saldo - $item["pivot"]["cantidad"]; break;
                        case(4): $saldo = $saldo + $item["pivot"]["cantidad"]; break;
                        case(5): $saldo = $saldo + $item["pivot"]["cantidad"]; break;
                        case(6): $saldo = $saldo - $item["pivot"]["cantidad"]; break;
                        default; break;
                    }
                }
            }

            $producto->existenciactual = $saldo;
            $producto->save();

        }

        $inventarios = Producto::all();

        return view('inventarios.index', compact('inventarios'));

    }

    public function kardex($producto){

            $ultimoCierre = Cierre::orderBy('fecha_cierre', 'desc')->first();
            $fechaCierre  = $this->fechaInicioPeriodo($ultimoCierre);

            // Cargar el producto con las transacciones YA filtradas por período
            $kardexs = Producto::with(['transactions' => function($query) use ($fechaCierre) {
                if($fechaCierre){
                    $query->where('transactions.created_at', '>=', $fechaCierre);
                }
                $query->orderBy('transactions.created_at', 'ASC');
            }])->where('id', $producto)->first();

            $saldo = $kardexs->existenciainicial ?? 0;

            // Calcular saldo acumulado sobre las transacciones filtradas
            foreach($kardexs->transactions as $item){
                $tid = $item->concepto->transaccion_id;
                $cantidad = $item->pivot->cantidad;
                if(in_array($tid, [1,3,6]))      $saldo -= $cantidad;
                elseif(in_array($tid, [2,4,5]))  $saldo += $cantidad;
            }

            $kardexs->existenciactual = $saldo;
            $kardexs->save();

            $articulo = Producto::find($producto);

            return view('inventarios.kardex', compact('producto','kardexs','articulo','ultimoCierre'));

    }

    public function updateInventario(){

        $ultimoCierre = Cierre::orderBy('fecha_cierre', 'desc')->first();
        $fechaCierre  = $this->fechaInicioPeriodo($ultimoCierre);

        $productos = Producto::where('tipoproducto',1)->get();

        foreach ($productos as $producto) {

            $transacciones = Producto::with(['transactions' => function($query) use ($fechaCierre) {
                if($fechaCierre){
                    $query->where('transactions.created_at', '>=', $fechaCierre);
                }
                $query->orderBy('transactions.created_at', 'ASC');
            }])->where('id', $producto->id)->get();

            // Arranca desde el saldo del último cierre
            $saldo = $producto->existenciainicial ?? 0;

            foreach($transacciones as $kardex){
                foreach($kardex['transactions'] as $item){
                    switch($item["concepto"]["transaccion_id"]){
                        case(1): $saldo = $saldo - $item["pivot"]["cantidad"]; break;
                        case(2): $saldo = $saldo + $item["pivot"]["cantidad"]; break;
                        case(3): $saldo = $saldo - $item["pivot"]["cantidad"]; break;
                        case(4): $saldo = $saldo + $item["pivot"]["cantidad"]; break;
                        case(5): $saldo = $saldo + $item["pivot"]["cantidad"]; break;
                        case(6): $saldo = $saldo - $item["pivot"]["cantidad"]; break;
                        default; break;
                    }
                }
            }

            $producto->existenciactual = $saldo;
            $producto->save();

        }

        return response()->json("Success");
    }

}
