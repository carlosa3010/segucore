@extends('layouts.admin')

@section('title', 'Editar Cuenta: ' . $account->account_number)

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Editar Cuenta de Monitoreo</h1>
        <a href="{{ route('admin.accounts.show', $account->id) }}" class="text-gray-400 hover:text-white transition">
            &larr; Volver al Panel
        </a>
    </div>

    <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 shadow-lg">
        <form action="{{ route('admin.accounts.update', $account->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="md:col-span-2 bg-gray-900/50 p-4 rounded border border-gray-700">
                    <h3 class="text-gray-300 font-bold mb-4 text-sm uppercase">Identificación (No modificable)</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs text-gray-500 uppercase block mb-1">Número de Abonado</label>
                            <input type="text" value="{{ $account->account_number }}" class="form-input bg-gray-800 text-gray-400 cursor-not-allowed" disabled>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase block mb-1">Cliente Asociado</label>
                            <input type="text" value="{{ $account->customer->full_name ?? 'Sin Cliente' }}" class="form-input bg-gray-800 text-gray-400 cursor-not-allowed" disabled>
                        </div>
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500 uppercase block mb-1 font-bold">Nombre de Sucursal / Ubicación</label>
                    <input type="text" name="branch_name" value="{{ old('branch_name', $account->branch_name) }}" class="form-input text-lg font-bold text-[#C6F211]" placeholder="Ej: Casa Principal, Almacén Norte">
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs text-gray-500 uppercase block mb-1 font-bold">Dirección de Instalación</label>
                    <textarea name="installation_address" rows="2" class="form-input" required>{{ old('installation_address', $account->installation_address) }}</textarea>
                </div>

                <div>
                    <label class="text-xs text-gray-500 uppercase block mb-1 font-bold">Modelo del Panel/Dispositivo</label>
                    <input type="text" name="device_model" value="{{ old('device_model', $account->device_model) }}" class="form-input" placeholder="Ej: DSC PowerSeries, Hikvision AX">
                </div>
                
                <div>
                     <label class="text-xs text-gray-500 uppercase block mb-1 font-bold">Estado del Servicio</label>
                     <select name="service_status" class="form-input">
                         <option value="active" {{ $account->service_status == 'active' ? 'selected' : '' }}>🟢 Activo</option>
                         <option value="suspended" {{ $account->service_status == 'suspended' ? 'selected' : '' }}>🔴 Suspendido</option>
                         <option value="inactive" {{ $account->service_status == 'inactive' ? 'selected' : '' }}>⚫ Inactivo/Retirado</option>
                     </select>
                </div>

                <div class="md:col-span-2 mt-4 pt-4 border-t border-gray-700">
                    <label class="block text-sm text-gray-400 mb-2 font-bold">Geolocalización (Arrastra el pin para actualizar)</label>
                    <div id="map" class="h-64 w-full rounded border border-gray-600 z-0"></div>
                    
                    <div class="grid grid-cols-2 gap-4 mt-3">
                        <div>
                            <label class="text-xs text-gray-500 uppercase block mb-1 font-bold">Latitud</label>
                            <input type="text" name="latitude" id="lat" value="{{ old('latitude', $account->latitude) }}" class="form-input bg-gray-900 font-mono text-xs" readonly>
                        </div>
                        <div>
                            <label class="text-xs text-gray-500 uppercase block mb-1 font-bold">Longitud</label>
                            <input type="text" name="longitude" id="lng" value="{{ old('longitude', $account->longitude) }}" class="form-input bg-gray-900 font-mono text-xs" readonly>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4 border-t border-gray-700 pt-6">
                <a href="{{ route('admin.accounts.show', $account->id) }}" class="text-gray-400 hover:text-white transition">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded shadow-lg transition transform hover:scale-105">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Coordenadas iniciales: Usar las de la cuenta o por defecto Barquisimeto
        var initialLat = {{ $account->latitude ?? 10.0677719 }};
        var initialLng = {{ $account->longitude ?? -69.3473503 }};

        // Inicializar mapa
        var map = L.map('map').setView([initialLat, initialLng], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Crear marcador arrastrable
        var marker = L.marker([initialLat, initialLng], {
            draggable: true
        }).addTo(map);

        // Función para actualizar inputs
        function updateInputs(latlng) {
            document.getElementById('lat').value = latlng.lat.toFixed(7);
            document.getElementById('lng').value = latlng.lng.toFixed(7);
        }

        // Si no hay coordenadas guardadas, actualizamos los inputs con el default
        if (!document.getElementById('lat').value) {
            updateInputs(marker.getLatLng());
        }

        // Evento al soltar el marcador
        marker.on('dragend', function(e) {
            updateInputs(marker.getLatLng());
        });

        // Evento al hacer click en el mapa (mueve el marcador)
        map.on('click', function(e) {
            marker.setLatLng(e.latlng);
            updateInputs(e.latlng);
        });
    });
</script>
@endsection