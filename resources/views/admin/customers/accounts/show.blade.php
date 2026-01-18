@extends('layouts.admin')

@section('title', 'Panel: ' . $account->account_number)

@section('content')
<div x-data="{ activeTab: 'zones' }">

    <div class="bg-[#1e293b] border-b border-gray-700 p-6 mb-6 rounded-lg shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-10 pointer-events-none">
            <span class="text-9xl font-bold font-mono">{{ $account->account_number }}</span>
        </div>
        
        <div class="flex justify-between items-start relative z-10">
            <div class="flex gap-6">
                <div class="bg-black/40 p-4 rounded text-center min-w-[100px] border border-gray-600">
                    <span class="block text-xs text-gray-500 uppercase">ABONADO</span>
                    <span class="text-3xl font-mono font-bold text-[#C6F211]">{{ $account->account_number }}</span>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white">{{ $account->branch_name ?? 'Ubicación Principal' }}</h1>
                    <p class="text-gray-400 text-sm mt-1">{{ $account->installation_address }}</p>
                    <div class="flex items-center gap-4 mt-3 text-sm">
                        <a href="{{ route('admin.customers.show', $account->customer_id) }}" class="text-blue-400 hover:text-white flex items-center gap-1">
                            👤 {{ $account->customer->full_name }}
                        </a>
                        <span class="text-gray-600">|</span>
                        <span>📡 {{ $account->device_model ?? 'Modelo Desconocido' }}</span>
                    </div>
                </div>
            </div>
            
            @if($account->permanent_notes)
                <div class="bg-red-900/20 border-l-4 border-red-500 p-3 max-w-md">
                    <h4 class="text-red-400 font-bold text-xs uppercase mb-1">⚠️ Nota Operativa Fija</h4>
                    <p class="text-white text-sm">{{ $account->permanent_notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="flex border-b border-gray-700 mb-6 space-x-1 overflow-x-auto">
        <button @click="activeTab = 'zones'" :class="activeTab === 'zones' ? 'border-[#C6F211] text-[#C6F211]' : 'border-transparent text-gray-400 hover:text-white'" class="py-2 px-4 border-b-2 font-medium text-sm transition">
            🔢 Zonas (Sensores)
        </button>
        <button @click="activeTab = 'partitions'" :class="activeTab === 'partitions' ? 'border-[#C6F211] text-[#C6F211]' : 'border-transparent text-gray-400 hover:text-white'" class="py-2 px-4 border-b-2 font-medium text-sm transition">
            📂 Particiones
        </button>
        <button @click="activeTab = 'contacts'" :class="activeTab === 'contacts' ? 'border-[#C6F211] text-[#C6F211]' : 'border-transparent text-gray-400 hover:text-white'" class="py-2 px-4 border-b-2 font-medium text-sm transition">
            📞 Lista de Llamadas
        </button>
        <button @click="activeTab = 'notes'" :class="activeTab === 'notes' ? 'border-[#C6F211] text-[#C6F211]' : 'border-transparent text-gray-400 hover:text-white'" class="py-2 px-4 border-b-2 font-medium text-sm transition">
            📝 Notas & Bitácora
        </button>
        <button @click="activeTab = 'schedule'" :class="activeTab === 'schedule' ? 'border-[#C6F211] text-[#C6F211]' : 'border-transparent text-gray-400 hover:text-white'" class="py-2 px-4 border-b-2 font-medium text-sm transition">
            🕒 Horarios
        </button>
    </div>

    <div x-show="activeTab === 'zones'" class="space-y-6">
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
            <h3 class="text-white font-bold mb-4">Configuración de Zonas</h3>
            
            <form action="{{ route('admin.accounts.zones.store', $account->id) }}" method="POST" class="mb-6 bg-gray-900/50 p-4 rounded border border-gray-700 flex gap-4 items-end">
                @csrf
                <div><label class="text-[10px] uppercase text-gray-500">N°</label><input type="text" name="zone_number" class="form-input w-20 text-center"></div>
                <div class="flex-1"><label class="text-[10px] uppercase text-gray-500">Descripción</label><input type="text" name="name" class="form-input"></div>
                <div><label class="text-[10px] uppercase text-gray-500">Tipo</label>
                    <select name="type" class="form-input">
                        <option>Instantánea</option>
                        <option>Retardada</option>
                        <option>24 Horas</option>
                        <option>Fuego</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded font-bold text-sm h-[40px]">Agregar</button>
            </form>

            <table class="w-full text-sm text-left text-gray-400">
                <thead class="bg-gray-800 text-xs uppercase">
                    <tr><th class="px-4 py-2">#</th><th class="px-4 py-2">Nombre</th><th class="px-4 py-2">Tipo</th><th class="px-4 py-2 text-right"></th></tr>
                </thead>
                <tbody>
                    @foreach($account->zones as $zone)
                        <tr class="border-b border-gray-700">
                            <td class="px-4 py-2 font-mono text-white">{{ $zone->zone_number }}</td>
                            <td class="px-4 py-2">{{ $zone->name }}</td>
                            <td class="px-4 py-2">{{ $zone->type }}</td>
                            <td class="px-4 py-2 text-right text-red-500 cursor-pointer">🗑️</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'partitions'" style="display: none;">
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
            <h3 class="text-white font-bold mb-4">Particiones del Sistema</h3>
            <p class="text-xs text-gray-500 mb-4">Define las áreas independientes del sistema (Ej: Casa, Taller).</p>
            
            <table class="w-full text-sm text-left text-gray-400 mb-4">
                <thead class="bg-gray-800 text-xs uppercase">
                    <tr><th class="px-4 py-2">#</th><th class="px-4 py-2">Nombre Área</th></tr>
                </thead>
                <tbody>
                    @if($account->partitions)
                        @foreach($account->partitions as $part)
                            <tr class="border-b border-gray-700">
                                <td class="px-4 py-2 font-mono text-white">{{ $part->partition_number }}</td>
                                <td class="px-4 py-2 text-white">{{ $part->name }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

            {{-- <form action="{{ route('admin.accounts.partitions.store', $account->id) }}" method="POST" ... --}}
            <div class="p-4 bg-gray-900/30 text-center text-gray-500 text-xs rounded border border-dashed border-gray-700">
                Formulario de creación de particiones (Implementar ruta y controlador)
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'contacts'" style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
                <h3 class="text-white font-bold mb-4">Lista de Llamadas (Orden de Prioridad)</h3>
                <p class="text-xs text-gray-500 mb-4">Contactos del cliente disponibles para emergencia.</p>
                
                <div class="space-y-2">
                    @foreach($account->customer->contacts as $index => $contact)
                        <div class="flex items-center justify-between p-3 bg-gray-800 rounded border border-gray-600">
                            <div class="flex items-center gap-3">
                                <div class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-white text-sm font-bold">{{ $contact->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $contact->relationship }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-mono text-sm">{{ $contact->phone }}</p>
                                <button class="text-xs text-blue-400 underline">Editar</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'notes'" style="display: none;">
        <form action="#" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            @method('PUT')
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
                <h3 class="text-red-400 font-bold mb-2">Nota Permanente (Fija)</h3>
                <p class="text-xs text-gray-500 mb-2">Información crítica visible siempre (Ej: Perro peligroso, Clave palabra especial).</p>
                <textarea name="permanent_notes" class="form-input bg-gray-900 border-red-900/50 text-white h-32">{{ $account->permanent_notes }}</textarea>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
                <h3 class="text-yellow-400 font-bold mb-2">Nota Temporal</h3>
                <p class="text-xs text-gray-500 mb-2">Ej: Cliente de vacaciones hasta el Viernes.</p>
                <textarea name="temporary_notes" class="form-input bg-gray-900 border-yellow-900/50 text-white h-20">{{ $account->temporary_notes }}</textarea>
                
                <label class="block text-xs text-gray-500 mt-2 mb-1">Válida hasta:</label>
                <input type="datetime-local" name="temporary_notes_until" class="form-input" value="{{ $account->temporary_notes_until }}">
            </div>

            <div class="md:col-span-2 text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold">Guardar Notas</button>
            </div>
        </form>
    </div>

    <div x-show="activeTab === 'schedule'" style="display: none;">
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-10 text-center">
            <h3 class="text-gray-400 font-bold text-lg mb-2">Control de Horarios</h3>
            <p class="text-gray-500 mb-4">Define a qué hora debería abrir/cerrar este local para generar alertas de "Fallo de Cierre".</p>
            <button class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded">Configurar Calendario Semanal</button>
        </div>
    </div>

</div>
@endsection