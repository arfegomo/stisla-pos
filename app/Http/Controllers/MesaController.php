<?php

namespace App\Http\Controllers;

use App\Concepto;
use App\Consecutivo;
use App\Mesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MesaController extends Controller
{
    public function stream(): StreamedResponse
    {
        return response()->stream(function() {

            $ultimoHash = '';

            while(true) {

                if(connection_aborted()) break;

                // Estado actual de todas las mesas
                $estado = DB::table('mesas')
                    ->where('mesas.id', '!=', 1000)
                    ->leftJoin(DB::raw('(SELECT mesa_id, SUM(preciounitario * cantidad) as total, COUNT(*) as items, MAX(consecutivo_id) as consecutivo_id FROM temporaries GROUP BY mesa_id) as t'), 't.mesa_id', '=', 'mesas.id')
                    ->select('mesas.id', 'mesas.responsable', DB::raw('COALESCE(t.total,0) as total'), DB::raw('COALESCE(t.items,0) as items'), 't.consecutivo_id')
                    ->orderBy('mesas.id')
                    ->get();

                $hash = md5($estado->toJson());

                if($hash !== $ultimoHash) {
                    $ultimoHash = $hash;
                    echo "data: " . $estado->toJson() . "\n\n";
                    ob_flush();
                    flush();
                }

                sleep(2);
            }

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    public function partial(): \Illuminate\View\View
    {
        $mesas = Mesa::all();
        return view('mesas.partial', compact('mesas'));
    }

    public function updateMesa(Request $request)
    {
        $this->validate($request,[
            
            'responsable' => 'required|max:255',
        
        ]);

         $mesa = Mesa::find($request->get('mesa-id'));  
         
         $mesa->responsable = $request->get('responsable');
        
         $mesa->save();

         $conceptos = Concepto::pluck("nombre", "id")->all();

        $consecutivo = Consecutivo::create();

        $consecutivo = $consecutivo->id;

        $mesa = $request->get('mesa-id');

        return view("facturacion.index", compact("conceptos","consecutivo","mesa"));

    }

    public function trasladarMesa(Request $request)
    {
        try {
            $trasladarMesa = DB::table('temporaries')
                            ->where('mesa_id', $request->get('origen'))
                            ->update([
                                'mesa_id' => $request->get('destino')
                            ]);

            if($trasladarMesa){
                $mesa = Mesa::find($request->get('origen'));
                $nombreResponsable = $mesa->responsable;
                $mesa->responsable = '';
                $mesa->save();

                $mesa = Mesa::find($request->get('destino'));
                $mesa->responsable = $nombreResponsable;
                $mesa->save();
            }

            return response()->json([
                'data' => 'success'
            ]);

        } catch (\Throwable $th) {
            Log::debug($th);
            return response()->json([
                'data' => 'error'
            ]);
            
        }
    }
}
