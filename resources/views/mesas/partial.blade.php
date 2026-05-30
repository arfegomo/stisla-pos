@foreach($mesas as $mesa)
    @if($mesa->id != 1000)
    @php
        $value      = \Illuminate\Support\Facades\DB::table('temporaries')->where('mesa_id', $mesa->id)->exists();
        $total      = \Illuminate\Support\Facades\DB::table('temporaries')->where('mesa_id', $mesa->id)->selectRaw('SUM(preciounitario * cantidad) as total')->first();
        $consecutivo = \Illuminate\Support\Facades\DB::table('temporaries')->where('mesa_id', $mesa->id)->value('consecutivo_id');
    @endphp

    <div class="col-lg-2" style="padding-top: 20px">

        @if($value)

            <form method="POST" action="{{ route('facturacion.close') }}">
                @csrf
                <input type="hidden" name="mesa" value="{{ $mesa->id }}"/>
                <input type="hidden" name="consecutivo" value="{{ $consecutivo }}"/>

                <div class="col-lg-12 draggable ui-widget-content">
                    <div class="draggable ui-widget-content cursor-wait" id="{{ $mesa->id }}">
                        <div><i class="fa-solid fa-2x fa-sack-dollar"></i>
                            <strong><span style="padding-left:5px;text-transform:uppercase;font-size:22px">
                                ${{ number_format($total->total ?? 0, 0, ',', '.') }}
                            </span></strong>
                        </div>
                        <div>Cliente: <span style="text-transform:uppercase"><strong>{{ $mesa->responsable }}</strong></span></div>
                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-danger btn-lg active btn-block" role="button">
                                <b>Mesa: {{ $mesa->id }}</b><br>
                                <img alt="image" src="{{ asset('img/mesa.png') }}">
                            </button><hr>
                        </div>
                    </div>
                </div>
            </form>

        @else

            <div class="droppable ui-widget-content" id="{{ $mesa->id }}">
                <div><i class="fa-regular fa-2x fa-folder-open"></i></div>
                <div>Libre</div>
                <div class="col-lg-12">
                    <button type="button" data-id="{{ $mesa->id }}" id="openModal"
                            class="btn btn-linght btn-lg active btn-block" role="button">
                        <b>Mesa: {{ $mesa->id }}</b><br>
                        <img alt="image" src="{{ asset('img/mesa.png') }}">
                    </button>
                </div>
            </div>

        @endif

    </div>
    @endif
@endforeach
