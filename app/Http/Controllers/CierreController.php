<?php

namespace App\Http\Controllers;

use App\Cierre;
use App\Producto;
use App\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CierreController extends Controller
{
    public function index()
    {
        $cierres      = Cierre::with('user')->orderBy('fecha_cierre', 'desc')->get();
        $ultimoCierre = Cierre::orderBy('fecha_cierre', 'desc')->first();
        $mesCerrar    = $this->detectarMesCerrar($ultimoCierre);

        return view('cierres.index', compact('cierres', 'ultimoCierre', 'mesCerrar'));
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $ultimoCierre = Cierre::orderBy('fecha_cierre', 'desc')->first();
            $mesCerrar    = $this->detectarMesCerrar($ultimoCierre);

            if (!$mesCerrar) {
                return redirect()->route('cierres.index')
                    ->with('error', 'No hay transacciones registradas para cerrar.');
            }

            // No permitir cerrar el mes actual en curso
            if ($mesCerrar['fecha_cierre']->isCurrentMonth()) {
                return redirect()->route('cierres.index')
                    ->with('error', 'No se puede cerrar el mes actual mientras está en curso.');
            }

            Cierre::create([
                'user_id'      => Auth::id(),
                'fecha_cierre' => $mesCerrar['fecha_cierre']->toDateString(),
                'observacion'  => 'Cierre ' . $mesCerrar['label'],
            ]);

            // Calcular el saldo AL CIERRE sumando solo transacciones del período que se cierra
            // El período va desde el día siguiente al cierre anterior hasta el último día del mes que se cierra
            $fechaInicioPeriodo = $ultimoCierre
                ? Carbon::parse($ultimoCierre->fecha_cierre)->addDay()->startOfDay()->toDateTimeString()
                : null;
            $fechaFinPeriodo = $mesCerrar['fecha_cierre']->copy()->endOfDay()->toDateTimeString();

            $productos = Producto::where('tipoproducto', 1)->get();

            foreach ($productos as $producto) {

                // Partir del saldo inicial del período anterior
                $saldoAlCierre = $producto->existenciainicial ?? 0;

                // Sumar/restar solo los movimientos del período que se está cerrando
                $movimientos = DB::table('detail_transactions')
                    ->join('transactions', 'detail_transactions.transaction_id', '=', 'transactions.id')
                    ->join('conceptos', 'transactions.concepto_id', '=', 'conceptos.id')
                    ->where('detail_transactions.producto_id', $producto->id)
                    ->when($fechaInicioPeriodo, fn($q) => $q->where('transactions.created_at', '>=', $fechaInicioPeriodo))
                    ->where('transactions.created_at', '<=', $fechaFinPeriodo)
                    ->select('conceptos.transaccion_id', 'detail_transactions.cantidad')
                    ->get();

                foreach ($movimientos as $mov) {
                    if (in_array($mov->transaccion_id, [1, 3, 6]))     $saldoAlCierre -= $mov->cantidad;
                    elseif (in_array($mov->transaccion_id, [2, 4, 5])) $saldoAlCierre += $mov->cantidad;
                }

                // Costo al cierre = costopromedio de la última compra dentro del período
                // Si no hubo compras en el período, se mantiene el costoinicial anterior
                $ultimaCompra = DB::table('detail_transactions')
                    ->join('transactions', 'detail_transactions.transaction_id', '=', 'transactions.id')
                    ->join('conceptos', 'transactions.concepto_id', '=', 'conceptos.id')
                    ->where('detail_transactions.producto_id', $producto->id)
                    ->where('conceptos.transaccion_id', 2)
                    ->when($fechaInicioPeriodo, fn($q) => $q->where('transactions.created_at', '>=', $fechaInicioPeriodo))
                    ->where('transactions.created_at', '<=', $fechaFinPeriodo)
                    ->orderBy('transactions.created_at', 'desc')
                    ->select('detail_transactions.costopromedio')
                    ->first();

                $producto->existenciainicial = $saldoAlCierre;
                $producto->costoinicial      = $ultimaCompra ? $ultimaCompra->costopromedio : ($producto->costoinicial ?? 0);
                $producto->save();
            }

            DB::commit();

            return redirect()->route('cierres.index')
                ->with('success', 'Cierre de ' . $mesCerrar['label'] . ' ejecutado correctamente.');

        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->route('cierres.index')
                ->with('error', 'Error al ejecutar el cierre: ' . $th->getMessage());
        }
    }

    private function detectarMesCerrar($ultimoCierre)
    {
        if ($ultimoCierre) {
            // El siguiente mes después del último cierre
            $inicio = Carbon::parse($ultimoCierre->fecha_cierre)
                ->addDay()
                ->startOfMonth();
        } else {
            // Primera vez: buscar el mes de la transacción más antigua
            $primeraTransaccion = Transaction::orderBy('created_at', 'ASC')->first();

            if (!$primeraTransaccion) {
                return null;
            }

            $inicio = Carbon::parse($primeraTransaccion->created_at)->startOfMonth();
        }

        $fin = $inicio->copy()->endOfMonth();

        return [
            'inicio'       => $inicio,
            'fecha_cierre' => $fin,
            'label'        => $inicio->translatedFormat('F Y'), // ej: "Mayo 2026"
        ];
    }
}
