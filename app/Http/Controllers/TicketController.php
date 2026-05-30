<?php

namespace App\Http\Controllers;

use App\Services\PrinterService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function imprimir(int $transactionId)
    {
        try {
            $service = new PrinterService();
            $service->imprimirTicket($transactionId);

            return response()->json(['message' => 'Ticket impreso correctamente.']);

        } catch (\Throwable $th) {
            return response()->json([
                'error'   => true,
                'message' => 'Error al imprimir: ' . $th->getMessage()
            ], 500);
        }
    }

    public function previsualizar(int $transactionId)
    {
        try {
            $service   = new PrinterService();
            $contenido = $service->generarTextoPlano($transactionId);

            return response(
                "<div style='font-family:sans-serif;max-width:500px;margin:20px auto;'>
                 <div style='background:#222;color:#0f0;padding:5px 10px;font-size:12px;border-radius:4px 4px 0 0;'>
                   PREVISUALIZACIÓN TICKET — Factura #{$transactionId}
                 </div>
                 <pre style='font-family:\"Courier New\",monospace;font-size:13px;background:#fff;padding:20px;margin:0;border:2px solid #222;'>"
                . htmlspecialchars($contenido)
                . "</pre></div>"
            );

        } catch (\Throwable $th) {
            return response('<p style="color:red">Error: ' . $th->getMessage() . '</p>');
        }
    }

    public function testEscPos(int $transactionId)
    {
        try {
            $service  = new PrinterService();
            $rutaFile = storage_path('app/ticket_test.bin');
            $service->imprimirAArchivo($transactionId, $rutaFile);
            $size = filesize($rutaFile);

            return response()->json([
                'ok'      => true,
                'mensaje' => "ESC/POS generado correctamente. Tamaño: {$size} bytes.",
                'archivo' => $rutaFile,
            ]);

        } catch (\Throwable $th) {
            return response()->json(['ok' => false, 'error' => $th->getMessage()], 500);
        }
    }
}
