<?php

return [
    // 'windows' para USB/compartida en Windows | 'network' para TCP/IP
    'type'  => env('PRINTER_TYPE', 'windows'),

    // Nombre exacto de la impresora en Windows (Panel de control → Dispositivos)
    'name'  => env('PRINTER_NAME', 'POS-80'),

    // Para conexión por red
    'ip'    => env('PRINTER_IP', '192.168.1.100'),
    'port'  => env('PRINTER_PORT', 9100),

    // Ancho en caracteres: 48 para 80mm | 32 para 58mm
    'width' => env('PRINTER_WIDTH', 48),
];
