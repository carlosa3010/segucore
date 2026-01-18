@extends('layouts.admin')

@section('title', 'Panel: ' . $account->account_number)

@section('content')
    <div class="bg-[#1e293b] border-b border-gray-700 p-6 mb-6 rounded-lg shadow-lg">
        <div class="flex justify-between items-start">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded font-bold uppercase tracking-wider">SIA-DCS</span>
                    <h1 class="text-3xl font-mono font-bold text-white tracking-widest">{{ $account->account_number }}</h1>
                </div>
                <div class="text-gray-400 text-sm flex gap-4">
                    <a href="{{ route('admin.customers.show', $account->customer_id) }}" class="hover:text-blue-400 transition flex items-center gap-1">
                        👤 {{ $account->customer->full_name }}
                    </a>
                    <span>📍 {{ $account->branch_name ?? 'Ubicación Principal' }}</span>
                </div>
            </div>
            <div class="text-right">
                <span class="block text-[10px] uppercase text-gray-500 tracking-wider">Estado del Servicio</span>
                @if($account->service_status === 'active')
                    <span class="text-2xl font-bold text-green-400">● EN LÍNEA</span>
                @else
                    <span class="text-2xl font-bold text-red-400">● SUSPENDIDO</span>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        <div class="xl:col-span-2 space-y-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 shadow-lg overflow-hidden">
                <div class="p-4 border-b border-gray-700 flex justify-between items-center bg-gray-800/50">
                    <h3 class="font-bold text-white flex items-center gap-2">
                        <span class="text-[#C6F211]">🔢</span> Listado de Zonas
                    </h3>
                    <span class="text-xs text-gray-400">{{ $account->zones->count() }} Zonas configuradas</span>
                </div>

                <div class="p-4 bg-gray-900/30 border-b border-gray-700">
                    <form action="{{ route('admin.accounts.zones.store', $account->id) }}" method="POST" class="flex flex-col md:flex-row gap-2 items-end">
                        @csrf
                        <div class="w-24">
                            <label class="text-[10px] text-gray-500 uppercase">N° Zona</label>
                            <input type="text" name="zone_number" placeholder="001" class="form-input text-center font-mono font-bold" required>
                        </div>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 uppercase">Etiqueta / Nombre</label>
                            <input type="text" name="name" placeholder="Ej: Sensor Puerta Principal" class="form-input" required>
                        </div>
                        <div class="w-40">
                            <label class="text-[10px] text-gray-500 uppercase">Definición</label>
                            <select name="type" class="form-input text-sm">
                                <option value="Instantánea">Instantánea</option>
                                <option value="Retardada">Retardada</option>
                                <option value="Seguimiento">Seguimiento</option>
                                <option value="24 Horas">24 Horas (Pánico)</option>
                                <option value="Fuego">Fuego</option>
                                <option value="Médica">Médica</option>
                            </select>
                        </div>
                        <button type="submit" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 px-4 rounded text-sm h-[38px]">
                            + Agregar
                        </button>
                    </form>
                </div>

                <div class="max-h-[400px] overflow-y-auto">
                    <table class="w-full text-sm text-left text-gray-400">
                        <thead class="text-xs text-gray-500 uppercase bg-gray-800 sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-center w-20">#</th>
                                <th class="px-4 py-2">Nombre</th>
                                <th class="px-4 py-2">Tipo</th>
                                <th class="px-4 py-2 text-right">Acción</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @forelse($account->zones as $zone)
                                <tr class="hover:bg-gray-700/30">
                                    <td class="px-4 py-2 text-center font-mono text-white font-bold">{{ $zone->zone_number }}</td>
                                    <td class="px-4 py-2 text-white">{{ $zone->name }}</td>
                                    <td class="px-4 py-2">
                                        <span class="px-2 py-0.5 rounded text-xs border 
                                            {{ $zone->type == 'Fuego' ? 'border-red-500 text-red-400 bg-red-900/20' : 
                                               ($zone->type == '24 Horas' ? 'border-orange-500 text-orange-400 bg-orange-900/20' : 
                                               'border-gray-600 text-gray-300 bg-gray-800') }}">
                                            {{ $zone->type }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-right">
                                        <form action="{{ route('admin.zones.destroy', $zone->id) }}" method="POST" onsubmit="return confirm('¿Borrar zona?');">
                                            @csrf @method('DELETE')
                                            <button class="text-red-500 hover:text-white">🗑️</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-gray-500 italic">
                                        No hay zonas configuradas. Usa el formulario de arriba.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 shadow-lg p-5">
                <h3 class="font-bold text-white mb-4 flex items-center gap-2">
                    <span class="text-blue-400">🌍</span> Ubicación Geográfica
                </h3>
                
                <form action="{{ route('admin.accounts.update', $account->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf @method('PUT')
                    
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-500 uppercase">Dirección de Instalación</label>
                        <input type="text" name="installation_address" value="{{ $account->installation_address }}" class="form-input">
                    </div>
                    
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Latitud</label>
                        <input type="text" name="latitude" value="{{ $account->latitude }}" class="form-input font-mono" placeholder="10.00000">
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 uppercase">Longitud</label>
                        <input type="text" name="longitude" value="{{ $account->longitude }}" class="form-input font-mono" placeholder="-69.00000">
                    </div>

                    <div class="md:col-span-2 flex justify-end">
                        <button type="submit" class="text-blue-400 hover:text-white text-sm underline">
                            Actualizar Coordenadas
                        </button>
                    </div>
                </form>

                <div class="mt-4 h-48 bg-gray-800 rounded border border-gray-600 flex items-center justify-center text-gray-500">
                    @if($account->latitude && $account->longitude)
                        <iframe 
                            width="100%" 
                            height="100%" 
                            frameborder="0" 
                            style="border:0" 
                            src="https://www.google.com/maps/embed/v1/place?key=TU_API_KEY_AQUI&q={{ $account->latitude }},{{ $account->longitude }}&zoom=15" allowfullscreen>
                        </iframe>
                        @else
                        <span>Sin coordenadas GPS configuradas</span>
                    @endif
                </div>
            </div>

        </div>

        <div class="space-y-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 shadow-lg p-5">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-2">
                    Detalles del Equipo
                </h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block">Modelo Panel</label>
                        <span class="text-white">{{ $account->device_model ?? 'No especificado' }}</span>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block">Notas Técnicas</label>
                        <p class="text-sm text-gray-300 bg-black/20 p-2 rounded border border-gray-700">
                            {{ $account->notes ?? 'Sin notas adicionales.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 shadow-lg p-5 opacity-70">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4 border-b border-gray-700 pb-2 flex justify-between">
                    <span>Usuarios / Llaves</span>
                    <span class="text-[10px] bg-gray-700 px-1 rounded">Próximamente</span>
                </h3>
                <p class="text-xs text-gray-500">
                    Aquí se gestionarán los usuarios específicos que tienen código para armar/desarmar este panel.
                </p>
            </div>

        </div>

    </div>
@endsection