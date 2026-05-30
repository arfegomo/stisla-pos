@extends('layouts.app')

@section('content')

<section class="section">
    <section class="section">

        <div class="section-header">
            <div class="row w-100">
                <div class="col-lg-12">
                    <h1 class="page__heading">Cierre de Mes</h1>
                </div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-lg-6">

                    {{-- Alertas --}}
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    {{-- Tarjeta de ejecución --}}
                    <div class="card">
                        <div class="card-header"><h4>Ejecutar cierre de mes</h4></div>
                        <div class="card-body">

                            @if($ultimoCierre)
                                <div class="alert alert-info">
                                    Último cierre: <strong>{{ \Carbon\Carbon::parse($ultimoCierre->fecha_cierre)->translatedFormat('F Y') }}</strong>
                                    — por {{ $ultimoCierre->user->name ?? 'N/A' }}
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    No hay cierres registrados. Se cerrará desde el primer mes con transacciones.
                                </div>
                            @endif

                            @if($mesCerrar)

                                @if($mesCerrar['fecha_cierre']->isCurrentMonth())
                                    <div class="alert alert-danger">
                                        El mes a cerrar es el mes actual (<strong>{{ $mesCerrar['label'] }}</strong>).
                                        No se puede cerrar un mes que aún está en curso.
                                    </div>
                                @else
                                    <div class="alert alert-success">
                                        <strong>Mes a cerrar:</strong> {{ $mesCerrar['label'] }}<br>
                                        <small>Fecha de corte: {{ $mesCerrar['fecha_cierre']->format('d/m/Y') }}</small>
                                    </div>

                                    <form action="{{ route('cierres.store') }}" method="POST"
                                          onsubmit="return confirm('¿Confirma el cierre de {{ $mesCerrar['label'] }}? Esta acción actualizará el saldo inicial de todos los productos.')">
                                        @csrf
                                        <button type="submit" class="btn btn-primary">
                                            Ejecutar cierre de {{ $mesCerrar['label'] }}
                                        </button>
                                    </form>
                                @endif

                            @else
                                <div class="alert alert-warning">
                                    No hay transacciones registradas en el sistema.
                                </div>
                            @endif

                        </div>
                    </div>

                </div>

                <div class="col-lg-6">

                    {{-- Historial de cierres --}}
                    <div class="card">
                        <div class="card-header"><h4>Historial de cierres</h4></div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>Mes cerrado</th>
                                        <th>Fecha de corte</th>
                                        <th>Usuario</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($cierres as $cierre)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($cierre->fecha_cierre)->translatedFormat('F Y') }}</td>
                                            <td>{{ \Carbon\Carbon::parse($cierre->fecha_cierre)->format('d/m/Y') }}</td>
                                            <td>{{ $cierre->user->name ?? 'N/A' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">Sin cierres registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>
</section>

@endsection
