<?php

namespace App\Services;

use App\Empresa;
use App\FormaPago;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PrinterService
{
    private int   $width;
    private string $line;
    private string $thinLine;

    public function __construct()
    {
        $this->width    = (int) config('printer.width', 48);
        $this->line     = str_repeat('=', $this->width);
        $this->thinLine = str_repeat('-', $this->width);
    }

    public function imprimirTicket(int $transactionId): void
    {
        $connector = $this->getConnector();
        $printer   = new Printer($connector);

        try {
            $transaction = Transaction::with(['concepto', 'socio', 'user'])
                ->findOrFail($transactionId);

            $detalles   = $this->queryDetalles($transactionId);
            $formasPago = FormaPago::where('transaction_id', $transactionId)->get();
            $empresa    = Empresa::first();

            $this->imprimirCabecera($printer, $empresa, $transaction);
            $this->imprimirDetalles($printer, $detalles);
            $this->imprimirTotales($printer, $detalles, $formasPago);
            $this->imprimirPie($printer);

            $printer->cut();

        } finally {
            $printer->close();
        }
    }

    private function imprimirCabecera(Printer $printer, $empresa, $transaction): void
    {
        $printer->initialize();
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setEmphasis(true);
        $printer->setTextSize(2, 2);
        $printer->text($this->truncar($empresa->nombre ?? 'MI NEGOCIO', 24) . "\n");
        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text($this->truncar($empresa->direccion ?? '', $this->width) . "\n");
        $printer->text('Tel: ' . ($empresa->telefono ?? '') . '  NIT: ' . ($empresa->nit ?? '') . "\n");
        $printer->text($this->line . "\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->setEmphasis(true);
        $printer->text(strtoupper($transaction->concepto->nombre ?? 'TRANSACCION') . "\n");
        $printer->setEmphasis(false);
        $printer->text('Fecha: ' . $transaction->fecha . '  Hora: ' . $transaction->hora . "\n");
        $printer->text('Factura #: ' . str_pad($transaction->consecutivo, 8, '0', STR_PAD_LEFT) . "\n");

        if ($transaction->socio) {
            $cliente = trim(($transaction->socio->nombres ?? '') . ' ' . ($transaction->socio->apellidos ?? ''));
            $printer->text('Cliente: ' . $this->truncar($cliente, $this->width - 9) . "\n");
            $printer->text('Nit:     ' . ($transaction->socio->documento ?? '') . "\n");
        }

        $printer->text('Cajero:  ' . $this->truncar($transaction->user->name ?? 'N/A', $this->width - 9) . "\n");
        $printer->text($this->line . "\n");
    }

    private function imprimirDetalles(Printer $printer, $detalles): void
    {
        $cNom    = 22;
        $cCant   = 4;
        $cPrecio = 10;
        $cTotal  = 10;

        $printer->setEmphasis(true);
        $printer->text(
            str_pad('PRODUCTO', $cNom)
            . str_pad('CANT', $cCant, ' ', STR_PAD_LEFT) . ' '
            . str_pad('PRECIO', $cPrecio, ' ', STR_PAD_LEFT) . ' '
            . str_pad('TOTAL', $cTotal, ' ', STR_PAD_LEFT) . "\n"
        );
        $printer->setEmphasis(false);
        $printer->text($this->thinLine . "\n");

        foreach ($detalles as $item) {
            $precio    = $item->preciounitario;
            $itemTotal = ($precio * $item->cantidad) - (($precio * $item->cantidad) * ($item->descuento / 100));
            $nombre    = $this->limpiarTexto($this->truncar($item->nombre, $cNom));

            $printer->text(
                str_pad($nombre, $cNom)
                . str_pad(number_format($item->cantidad, 0), $cCant, ' ', STR_PAD_LEFT) . ' '
                . str_pad(number_format($precio, 0, ',', '.'), $cPrecio, ' ', STR_PAD_LEFT) . ' '
                . str_pad(number_format($itemTotal, 0, ',', '.'), $cTotal, ' ', STR_PAD_LEFT) . "\n"
            );
        }

        $printer->text($this->thinLine . "\n");
    }

    private function imprimirTotales(Printer $printer, $detalles, $formasPago): void
    {
        [$subtotal, $total, $impuestosPorTipo] = $this->calcularTotales($detalles);

        $pad = $this->width - 12;
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->text(str_pad('SUBTOTAL:', $pad) . str_pad(number_format($subtotal, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) . "\n");

        foreach ($impuestosPorTipo as $label => $valor) {
            $printer->text(str_pad($label . ':', $pad) . str_pad(number_format($valor, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) . "\n");
        }

        $printer->setEmphasis(true);
        $printer->text(str_pad('TOTAL:', $pad) . str_pad(number_format($total, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) . "\n");
        $printer->setEmphasis(false);

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text($this->line . "\n");

        $pagoNombres = [1 => 'Efectivo', 2 => 'Tarjeta D/C', 4 => 'Nequi', 6 => 'Credito'];
        foreach ($formasPago as $pago) {
            $nombre = $pagoNombres[$pago->pago_id] ?? 'Otro';
            $printer->text('PAGO ' . strtoupper($nombre) . ': ' . number_format($pago->valor, 0, ',', '.') . "\n");
        }
    }

    private function imprimirPie(Printer $printer): void
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text($this->line . "\n");
        $printer->setEmphasis(true);
        $printer->text("¡GRACIAS POR SU COMPRA!\n");
        $printer->setEmphasis(false);
        $printer->text("Vuelva pronto\n");
        $printer->text($this->line . "\n");
        $printer->feed(3);
    }

    public function imprimirAArchivo(int $transactionId, string $rutaArchivo): void
    {
        $connector = new FilePrintConnector($rutaArchivo);
        $printer   = new Printer($connector);

        try {
            $transaction = Transaction::with(['concepto', 'socio', 'user'])->findOrFail($transactionId);
            $detalles    = $this->queryDetalles($transactionId);
            $formasPago  = FormaPago::where('transaction_id', $transactionId)->get();
            $empresa     = Empresa::first();

            $this->imprimirCabecera($printer, $empresa, $transaction);
            $this->imprimirDetalles($printer, $detalles);
            $this->imprimirTotales($printer, $detalles, $formasPago);
            $this->imprimirPie($printer);
            $printer->cut();
        } finally {
            $printer->close();
        }
    }

    public function generarTextoPlano(int $transactionId): string
    {
        $transaction = \App\Transaction::with(['concepto', 'socio', 'user'])
            ->findOrFail($transactionId);

        $detalles   = $this->queryDetalles($transactionId);
        $formasPago = \App\FormaPago::where('transaction_id', $transactionId)->get();
        $empresa    = \App\Empresa::first();

        $w    = $this->width;
        $sep  = str_repeat('=', $w);
        $thin = str_repeat('-', $w);
        $out  = '';

        // Cabecera empresa
        $out .= $this->centrar($empresa->nombre ?? 'MI NEGOCIO', $w) . "\n";
        $out .= $this->centrar($empresa->direccion ?? '', $w) . "\n";
        $out .= $this->centrar('Tel: ' . ($empresa->telefono ?? '') . '  NIT: ' . ($empresa->nit ?? ''), $w) . "\n";
        $out .= $sep . "\n";

        // Datos factura
        $out .= strtoupper($transaction->concepto->nombre ?? 'TRANSACCION') . "\n";
        $out .= 'Fecha: ' . $transaction->fecha . '  Hora: ' . $transaction->hora . "\n";
        $out .= 'Factura #: ' . str_pad($transaction->consecutivo, 8, '0', STR_PAD_LEFT) . "\n";
        if ($transaction->socio) {
            $cliente = trim(($transaction->socio->nombres ?? '') . ' ' . ($transaction->socio->apellidos ?? ''));
            $out .= 'Cliente: ' . $this->truncar($cliente, $w - 9) . "\n";
            $out .= 'Nit:     ' . ($transaction->socio->documento ?? '') . "\n";
        }
        $out .= 'Cajero:  ' . $this->truncar($transaction->user->name ?? 'N/A', $w - 9) . "\n";
        $out .= $sep . "\n";

        // Columnas
        $cNom    = 22;
        $cCant   = 4;
        $cPrecio = 10;
        $cTotal  = 10;

        $out .= str_pad('PRODUCTO', $cNom)
              . str_pad('CANT', $cCant, ' ', STR_PAD_LEFT) . ' '
              . str_pad('PRECIO', $cPrecio, ' ', STR_PAD_LEFT) . ' '
              . str_pad('TOTAL', $cTotal, ' ', STR_PAD_LEFT) . "\n";
        $out .= $thin . "\n";

        foreach ($detalles as $item) {
            $precio    = $item->preciounitario;
            $itemTotal = ($precio * $item->cantidad) - (($precio * $item->cantidad) * ($item->descuento / 100));
            $nombre    = $this->limpiarTexto($this->truncar($item->nombre, $cNom));

            $out .= str_pad($nombre, $cNom)
                  . str_pad(number_format($item->cantidad, 0), $cCant, ' ', STR_PAD_LEFT) . ' '
                  . str_pad(number_format($precio, 0, ',', '.'), $cPrecio, ' ', STR_PAD_LEFT) . ' '
                  . str_pad(number_format($itemTotal, 0, ',', '.'), $cTotal, ' ', STR_PAD_LEFT) . "\n";
        }

        $out .= $thin . "\n";

        // Totales con impuestos por tipo
        [$subtotal, $total, $impuestosPorTipo] = $this->calcularTotales($detalles);
        $pad = $w - 12;

        $out .= str_pad('SUBTOTAL:', $pad) . str_pad(number_format($subtotal, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) . "\n";
        foreach ($impuestosPorTipo as $label => $valor) {
            $out .= str_pad($label . ':', $pad) . str_pad(number_format($valor, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) . "\n";
        }
        $out .= str_pad('TOTAL:', $pad) . str_pad(number_format($total, 0, ',', '.'), 12, ' ', STR_PAD_LEFT) . "\n";
        $out .= $sep . "\n";

        // Formas de pago
        $pagoNombres = [1 => 'Efectivo', 2 => 'Tarjeta D/C', 4 => 'Nequi', 6 => 'Credito'];
        foreach ($formasPago as $pago) {
            $nombre = $pagoNombres[$pago->pago_id] ?? 'Otro';
            $out .= 'PAGO ' . strtoupper($nombre) . ': ' . number_format($pago->valor, 0, ',', '.') . "\n";
        }

        // Pie
        $out .= $sep . "\n";
        $out .= $this->centrar('¡GRACIAS POR SU COMPRA!', $w) . "\n";
        $out .= $this->centrar('Vuelva pronto', $w) . "\n";
        $out .= $sep . "\n";

        return $out;
    }

    private function queryDetalles(int $transactionId)
    {
        return DB::table('detail_transactions')
            ->join('productos', 'productos.id', '=', 'detail_transactions.producto_id')
            ->join('impuestos', 'impuestos.id', '=', 'detail_transactions.impuesto_id')
            ->where('detail_transactions.transaction_id', $transactionId)
            ->select(
                'productos.nombre',
                'detail_transactions.cantidad',
                'detail_transactions.preciounitario',
                'detail_transactions.descuento',
                'detail_transactions.impuesto',
                'detail_transactions.baseunitario',
                'impuestos.nombre as impuesto_nombre',
                'impuestos.tasa as impuesto_tasa'
            )
            ->get();
    }

    private function calcularTotales($detalles): array
    {
        $subtotal         = 0;
        $total            = 0;
        $impuestosPorTipo = [];

        foreach ($detalles as $item) {
            $base      = $item->baseunitario * $item->cantidad;
            $baseDesc  = $base - ($base * ($item->descuento / 100));
            $itemTotal = ($item->preciounitario * $item->cantidad) - (($item->preciounitario * $item->cantidad) * ($item->descuento / 100));
            $impVal    = $baseDesc * ($item->impuesto / 100);

            $subtotal += $baseDesc;
            $total    += $itemTotal;

            if ($item->impuesto > 0) {
                $label = $item->impuesto_nombre . ' ' . $item->impuesto_tasa . '%';
                $impuestosPorTipo[$label] = ($impuestosPorTipo[$label] ?? 0) + $impVal;
            }
        }

        return [$subtotal, $total, $impuestosPorTipo];
    }

    private function limpiarTexto(string $texto): string
    {
        $mapa = [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u',
            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U',
            'ñ'=>'n','Ñ'=>'N','ü'=>'u','Ü'=>'U',
        ];
        return strtr($texto, $mapa);
    }

    private function centrar(string $texto, int $ancho): string
    {
        $len = mb_strlen($texto);
        if ($len >= $ancho) return $texto;
        $pad = (int)(($ancho - $len) / 2);
        return str_repeat(' ', $pad) . $texto;
    }

    private function getConnector()
    {
        if (config('printer.type') === 'network') {
            return new NetworkPrintConnector(config('printer.ip'), (int) config('printer.port'));
        }

        return new WindowsPrintConnector(config('printer.name'));
    }

    private function truncar(string $texto, int $max): string
    {
        return mb_strlen($texto) > $max ? mb_substr($texto, 0, $max - 1) . '.' : $texto;
    }
}
