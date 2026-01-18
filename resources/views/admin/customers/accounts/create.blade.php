@extends('layouts.admin')

@section('title', 'Nueva Cuenta de Alarma')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Registrar Nueva Cuenta de Monitoreo</h1>
            <a href="{{ route('admin.customers.index') }}" class="text-gray-400 hover:text-white">&larr; Cancelar</a>
        </div>

        <form action="{{ route('admin.accounts.store') }}" method="POST" class="bg-[#1e293b] p-8 rounded-lg border border-gray-700 shadow-xl">
            @csrf
            
            <div class="mb-6">
                <label class="block text-sm text-gray-400 mb-1">Cliente Titular *</label>
                @if(isset($customer))
                    <input type="hidden" name="customer_id" value="{{ $customer->id }}">
                    <input type="text" readonly class="form-input bg-gray-800 text-gray-300 cursor-not-allowed" 
                           value="{{ $customer->full_name }} - {{ $customer->national_id }}">
                @else
                    <select name="customer_id" class="form-input">
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->full_name }} ({{ $c->national_id }})</option>
                        @endforeach
                    </select>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Número de Abonado (Account #) *</label>
                    <input type="text" name="account_number" required class="form-input font-mono text-xl tracking-wider uppercase" placeholder="Ej: 1234">
                    <p class="text-xs text-gray-500 mt-1">Este código debe coincidir con el programado en el panel.</p>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Modelo del Equipo</label>
                    <input type="text" name="device_model" class="form-input" placeholder="Ej: DSC PowerSeries Neo, Paradox EVO">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nombre de Sucursal / Etiqueta</label>
                    <input type="text" name="branch_name" class="form-input" placeholder="Ej: Casa de Playa, Almacén Central">
                </div>
            </div>

            <h3 class="text-[#C6F211] font-bold uppercase text-xs mb-4 pt-4 border-t border-gray-700">Ubicación e Instalación</h3>
            
            <div class="mb-4">
                <label class="block text-sm text-gray-400 mb-1">Dirección Exacta de Instalación *</label>
                <input type="text" name="installation_address" required class="form-input" placeholder="Calle, Av, Nro Casa, Referencia...">
            </div>

            <div class="mb-6">
                <label class="block text-sm text-gray-400 mb-2">Geolocalización (Arrastra el pin)</label>
                <div id="map" class="h-64 w-full rounded border border-gray-600 z-0"></div>
                <div class="grid grid-cols-2 gap-4 mt-2">
                    <div>
                        <label class="text-xs text-gray-500">Latitud</label>
                        <input type="text" name="latitude" id="lat" class="form-input text-xs bg-gray-900 font-mono" readonly>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500">Longitud</label>
                        <input type="text" name="longitude" id="lng" class="form-input text-xs bg-gray-900 font-mono" readonly>
                    </div>
                </div>
            </div>

            <div class="flex justify-end mt-8">
                <button type="submit" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-3 px-8 rounded shadow-lg">
                    Crear Panel y Configurar
                </button>
            </div>
        </form>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Inicializar mapa en Barquisimeto (O donde estés)
        var map = L.map('map').setView([10.0677719, -69.3473503], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Icono personalizado (opcional, usa el default por ahora)
        var marker = L.marker([10.0677719, -69.3473503], {
            draggable: true
        }).addTo(map);

        // Actualizar inputs al mover
        function updateInputs(latlng) {
            document.getElementById('lat').value = latlng.lat.toFixed(7);
            document.getElementById('lng').value = latlng.lng.toFixed(7);
        }

        // Evento Drag
        marker.on('dragend', function(e) {
            updateInputs(marker.getLatLng());
        });

        // Evento Click en Mapa
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng);
        });

        // Inicializar inputs
        updateInputs(marker.getLatLng());
    </script>
@endsection