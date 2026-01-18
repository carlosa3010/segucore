@extends('layouts.admin')

@section('title', 'Panel: ' . $account->account_number)

@section('content')
<div x-data="{ activeTab: 'partitions' }"> <div class="bg-[#1e293b] border-b border-gray-700 p-6 mb-6 rounded-lg shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
            <span class="text-9xl font-bold font-mono">{{ $account->account_number }}</span>
        </div>
        
        <div class="flex justify-between items-start relative z-10">
            <div class="flex gap-6">
                <div class="bg-black/40 p-4 rounded text-center min-w-[120px] border border-gray-600 flex flex-col justify-center shadow-inner">
                    <span class="block text-[10px] text-gray-500 uppercase tracking-widest mb-1">ABONADO</span>
                    <span class="text-3xl font-mono font-bold text-[#C6F211] tracking-wider">{{ $account->account_number }}</span>
                    <div class="mt-2 flex justify-center">
                        @if($account->service_status === 'active')
                            <span class="animate-pulse h-2 w-2 rounded-full bg-green-500 inline-block mr-1"></span>
                            <span class="text-[10px] text-green-400 font-bold uppercase">Online</span>
                        @else
                            <span class="h-2 w-2 rounded-full bg-red-500 inline-block mr-1"></span>
                            <span class="text-[10px] text-red-400 font-bold uppercase">Offline</span>
                        @endif
                    </div>
                </div>

                <div>
                    <h1 class="text-2xl font-bold text-white tracking-wide">{{ $account->branch_name ?? 'Ubicación Principal' }}</h1>
                    <p class="text-gray-400 text-sm mt-1 flex items-center gap-2">
                        <span>📍</span> {{ $account->installation_address }}
                    </p>
                    <div class="flex items-center gap-4 mt-4 text-sm bg-gray-800/50 p-2 rounded border border-gray-700 w-fit">
                        <a href="{{ route('admin.customers.show', $account->customer_id) }}" class="text-blue-400 hover:text-white flex items-center gap-2 transition font-medium">
                            👤 <span class="underline">{{ $account->customer->full_name }}</span>
                        </a>
                        <span class="text-gray-600">|</span>
                        <span class="text-gray-300">📡 {{ $account->device_model ?? 'Modelo Genérico' }}</span>
                    </div>
                </div>
            </div>
            
            @if($account->permanent_notes)
                <div class="bg-red-900/10 border-l-4 border-red-500 p-4 max-w-md rounded-r shadow-lg backdrop-blur-sm">
                    <h4 class="text-red-400 font-bold text-xs uppercase mb-1 flex items-center gap-2">
                        ⚠️ Nota Operativa Fija
                    </h4>
                    <p class="text-white text-sm leading-relaxed">{{ $account->permanent_notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="flex border-b border-gray-700 mb-6 space-x-1 overflow-x-auto">
        <button @click="activeTab = 'partitions'" :class="activeTab === 'partitions' ? 'border-[#C6F211] text-[#C6F211] bg-[#C6F211]/10' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-800'" class="py-3 px-5 border-b-2 font-bold text-sm transition flex items-center gap-2 rounded-t">
            📂 Particiones <span class="text-[10px] bg-gray-700 text-white px-1.5 py-0.5 rounded-full">{{ $account->partitions->count() }}</span>
        </button>
        <button @click="activeTab = 'zones'" :class="activeTab === 'zones' ? 'border-[#C6F211] text-[#C6F211] bg-[#C6F211]/10' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-800'" class="py-3 px-5 border-b-2 font-bold text-sm transition flex items-center gap-2 rounded-t">
            🔢 Zonas <span class="text-[10px] bg-gray-700 text-white px-1.5 py-0.5 rounded-full">{{ $account->zones->count() }}</span>
        </button>
        <button @click="activeTab = 'users'" :class="activeTab === 'users' ? 'border-[#C6F211] text-[#C6F211] bg-[#C6F211]/10' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-800'" class="py-3 px-5 border-b-2 font-bold text-sm transition flex items-center gap-2 rounded-t">
            👥 Usuarios Panel
        </button>
        <button @click="activeTab = 'contacts'" :class="activeTab === 'contacts' ? 'border-[#C6F211] text-[#C6F211] bg-[#C6F211]/10' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-800'" class="py-3 px-5 border-b-2 font-bold text-sm transition flex items-center gap-2 rounded-t">
            📞 Lista Llamadas
        </button>
        <button @click="activeTab = 'schedule'" :class="activeTab === 'schedule' ? 'border-[#C6F211] text-[#C6F211] bg-[#C6F211]/10' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-800'" class="py-3 px-5 border-b-2 font-bold text-sm transition flex items-center gap-2 rounded-t">
            🕒 Horarios
        </button>
        <button @click="activeTab = 'notes'" :class="activeTab === 'notes' ? 'border-[#C6F211] text-[#C6F211] bg-[#C6F211]/10' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-800'" class="py-3 px-5 border-b-2 font-bold text-sm transition flex items-center gap-2 rounded-t">
            📝 Bitácora
        </button>
    </div>

    <div x-show="activeTab === 'partitions'" x-transition:enter.duration.300ms>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
            <h3 class="text-white font-bold text-lg mb-4 border-b border-gray-700 pb-2">Configuración de Áreas</h3>
            
            <form action="{{ route('admin.accounts.partitions.store', $account->id) }}" method="POST" class="mb-6 bg-gray-900/50 p-4 rounded border border-gray-700 flex flex-col md:flex-row gap-4 items-end">
                @csrf
                <div class="w-full md:w-32">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1 block">N° Área</label>
                    <input type="number" name="partition_number" class="form-input text-center font-mono font-bold" min="1" max="8" value="{{ ($account->partitions->max('partition_number') ?? 0) + 1 }}" required>
                </div>
                <div class="flex-1 w-full">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1 block">Nombre (Ej: Casa, Oficina)</label>
                    <input type="text" name="name" class="form-input" placeholder="Descripción del área" required>
                </div>
                <button type="submit" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 px-6 rounded text-sm h-[42px] shadow-lg">
                    + Crear Área
                </button>
            </form>

            <table class="w-full text-sm text-left text-gray-400">
                <thead class="bg-gray-800 text-xs uppercase text-gray-300">
                    <tr>
                        <th class="px-6 py-3">#</th>
                        <th class="px-6 py-3">Nombre del Área</th>
                        <th class="px-6 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($account->partitions->sortBy('partition_number') as $part)
                        <tr class="hover:bg-gray-700/30 transition">
                            <td class="px-6 py-4 font-mono text-white font-bold">{{ $part->partition_number }}</td>
                            <td class="px-6 py-4 text-white font-medium">{{ $part->name }}</td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-red-500 hover:text-red-300" title="Eliminar">🗑️</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">Sin particiones. Crea la Partición 1.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'zones'" x-transition:enter.duration.300ms style="display: none;">
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
            <h3 class="text-white font-bold text-lg mb-4 border-b border-gray-700 pb-2">Listado de Zonas</h3>
            
            <form action="{{ route('admin.accounts.zones.store', $account->id) }}" method="POST" class="mb-6 bg-gray-900/50 p-4 rounded border border-gray-700 grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @csrf
                <div class="md:col-span-1">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1 block">Zona #</label>
                    <input type="text" name="zone_number" class="form-input text-center font-mono font-bold" placeholder="001" required>
                </div>
                <div class="md:col-span-2">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1 block">Partición</label>
                    <select name="partition_id" class="form-input text-sm">
                        @foreach($account->partitions as $p)
                            <option value="{{ $p->id }}">{{ $p->partition_number }} - {{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-5">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1 block">Ubicación / Sensor</label>
                    <input type="text" name="name" class="form-input" placeholder="Ej: PIR Sala Principal" required>
                </div>
                <div class="md:col-span-2">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1 block">Tipo</label>
                    <select name="type" class="form-input text-sm">
                        <option value="Instantánea">Instantánea</option>
                        <option value="Retardada">Retardada</option>
                        <option value="24 Horas">24 Horas</option>
                        <option value="Fuego">Fuego</option>
                        <option value="Pánico">Pánico</option>
                        <option value="Médica">Médica</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded text-sm h-[42px]">
                        + Agregar
                    </button>
                </div>
            </form>

            <table class="w-full text-sm text-left text-gray-400">
                <thead class="bg-gray-800 text-xs uppercase text-gray-300">
                    <tr>
                        <th class="px-4 py-3 text-center">N°</th>
                        <th class="px-4 py-3">Partición</th>
                        <th class="px-4 py-3">Descripción</th>
                        <th class="px-4 py-3">Definición</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($account->zones->sortBy('zone_number') as $zone)
                        <tr class="hover:bg-gray-700/30 transition group">
                            <td class="px-4 py-3 font-mono text-white font-bold text-center border-r border-gray-700">{{ $zone->zone_number }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ $zone->partition ? $zone->partition->name : 'N/A' }}
                            </td>
                            <td class="px-4 py-3 text-white">{{ $zone->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs border border-gray-600 text-gray-300">{{ $zone->type }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('admin.zones.destroy', $zone->id) }}" method="POST" onsubmit="return confirm('¿Borrar?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 opacity-0 group-hover:opacity-100 transition">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Sin zonas configuradas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'users'" x-transition:enter.duration.300ms style="display: none;">
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
            <h3 class="text-white font-bold text-lg mb-4 border-b border-gray-700 pb-2">Usuarios de Teclado (Open/Close)</h3>
            
            <form action="{{ route('admin.accounts.users.store', $account->id) }}" method="POST" class="mb-6 bg-gray-900/50 p-4 rounded border border-gray-700 flex gap-4 items-end">
                @csrf
                <div class="w-24">
                    <label class="text-[10px] uppercase text-gray-500 mb-1 block">Slot #</label>
                    <input type="text" name="user_number" class="form-input text-center font-mono" placeholder="001" required>
                </div>
                <div class="flex-1">
                    <label class="text-[10px] uppercase text-gray-500 mb-1 block">Nombre Usuario</label>
                    <input type="text" name="name" class="form-input" placeholder="Ej: Gerente Pedro" required>
                </div>
                <div class="w-40">
                    <label class="text-[10px] uppercase text-gray-500 mb-1 block">Rol</label>
                    <select name="role" class="form-input text-sm">
                        <option value="user">Usuario Estándar</option>
                        <option value="master">Maestro</option>
                        <option value="duress">Coacción (Silencioso)</option>
                    </select>
                </div>
                <button type="submit" class="bg-purple-600 hover:bg-purple-500 text-white font-bold py-2 px-4 rounded text-sm h-[42px]">
                    + Agregar Usuario
                </button>
            </form>

            <table class="w-full text-sm text-left text-gray-400">
                <thead class="bg-gray-800 text-xs uppercase">
                    <tr><th class="px-4 py-3">Slot</th><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Rol</th><th class="px-4 py-3 text-right"></th></tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    {{-- Iterar sobre $account->panelUsers cuando crees la relación --}}
                    @if($account->panelUsers && $account->panelUsers->count() > 0)
                        @foreach($account->panelUsers as $user)
                            <tr>
                                <td class="px-4 py-3 font-mono text-white">{{ $user->user_number }}</td>
                                <td class="px-4 py-3 text-white">{{ $user->name }}</td>
                                <td class="px-4 py-3">{{ ucfirst($user->role) }}</td>
                                <td class="px-4 py-3 text-right"><button class="text-red-500">🗑️</button></td>
                            </tr>
                        @endforeach
                    @else
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Sin usuarios registrados.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'contacts'" x-transition:enter.duration.300ms style="display: none;">
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
            <h3 class="text-white font-bold text-lg mb-4 border-b border-gray-700 pb-2">Lista de Llamadas de Emergencia</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="space-y-3">
                    @forelse($account->customer->contacts as $index => $contact)
                        <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded border border-gray-600 hover:border-blue-500 transition cursor-pointer group">
                            <div class="flex items-center gap-4">
                                <div class="bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md">
                                    {{ $index + 1 }}
                                </div>
                                <div>
                                    <p class="text-white text-sm font-bold">{{ $contact->name }}</p>
                                    <p class="text-xs text-gray-400 uppercase tracking-wider">{{ $contact->relationship }}</p>
                                </div>
                            </div>
                            <p class="text-white font-mono text-sm bg-black/20 px-2 py-1 rounded border border-gray-700">
                                {{ $contact->phone }}
                            </p>
                        </div>
                    @empty
                        <div class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-700 rounded">
                            No hay contactos. Usa el formulario para agregar uno.
                        </div>
                    @endforelse
                </div>

                <div class="bg-gray-900/30 p-5 rounded border border-gray-700">
                    <h4 class="text-gray-300 font-bold text-sm mb-4">Nuevo Contacto</h4>
                    <form action="{{ route('admin.customers.contacts.store', $account->customer_id) }}" method="POST" class="space-y-4">
                        @csrf
                        <input type="hidden" name="redirect_to_account" value="{{ $account->id }}">
                        <div>
                            <label class="text-[10px] text-gray-500 uppercase">Nombre Completo</label>
                            <input type="text" name="name" class="form-input" required>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase">Parentesco</label>
                                <input type="text" name="relationship" class="form-input" placeholder="Ej: Esposo, Vecino" required>
                            </div>
                            <div>
                                <label class="text-[10px] text-gray-500 uppercase">Teléfono</label>
                                <input type="text" name="phone" class="form-input" required>
                            </div>
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 rounded text-sm">
                                Guardar Contacto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'schedule'" x-transition:enter.duration.300ms style="display: none;">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 text-center shadow-lg relative group">
                <div class="h-12 w-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl group-hover:bg-blue-600 group-hover:text-white transition">📅</div>
                <h3 class="text-white font-bold text-lg mb-2">Horario Semanal Estándar</h3>
                <p class="text-gray-500 text-sm mb-6">Define apertura y cierre habitual (Lunes a Domingo).</p>
                <button class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded font-bold transition w-full">
                    Configurar Semana
                </button>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-purple-500/30 p-6 shadow-lg relative">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 bg-purple-900/40 rounded-full flex items-center justify-center text-xl text-purple-300">🚀</div>
                    <div>
                        <h3 class="text-white font-bold text-lg">Horario Temporal</h3>
                        <p class="text-[10px] text-purple-400 font-bold uppercase tracking-wider">Prioridad Alta (Sobreescribe)</p>
                    </div>
                </div>
                
                <form action="{{ route('admin.accounts.schedules.temp.store', $account->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Motivo (Ej: Inventario)</label>
                        <input type="text" name="reason" class="form-input bg-gray-900 border-gray-600" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] text-gray-500 uppercase block mb-1">Apertura</label>
                            <input type="time" name="open_time" class="form-input bg-gray-900 border-gray-600">
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 uppercase block mb-1">Cierre</label>
                            <input type="time" name="close_time" class="form-input bg-gray-900 border-gray-600">
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Válido Hasta (Caducidad)</label>
                        <input type="date" name="valid_until" class="form-input bg-gray-900 border-gray-600" required>
                    </div>
                    <button type="submit" class="w-full bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded font-bold transition shadow-lg mt-2">
                        Crear Excepción Temporal
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'notes'" x-transition:enter.duration.300ms style="display: none;">
        <form action="{{ route('admin.accounts.notes.update', $account->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf
            @method('PUT')
            
            <div class="bg-[#1e293b] rounded-lg border border-red-900/30 p-5 shadow-lg relative">
                <div class="absolute top-0 left-0 w-1 h-full bg-red-500 rounded-l-lg"></div>
                <h3 class="text-red-400 font-bold mb-2 flex items-center gap-2">⚠️ Nota Permanente</h3>
                <textarea name="permanent_notes" class="form-input bg-gray-900/50 border-gray-700 text-white h-40 focus:border-red-500 transition resize-none">{{ $account->permanent_notes }}</textarea>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-yellow-900/30 p-5 shadow-lg relative">
                <div class="absolute top-0 left-0 w-1 h-full bg-yellow-500 rounded-l-lg"></div>
                <h3 class="text-yellow-400 font-bold mb-2 flex items-center gap-2">⏳ Nota Temporal</h3>
                <textarea name="temporary_notes" class="form-input bg-gray-900/50 border-gray-700 text-white h-24 focus:border-yellow-500 transition resize-none mb-3">{{ $account->temporary_notes }}</textarea>
                
                <div class="flex items-center gap-3">
                    <label class="text-xs text-gray-400 whitespace-nowrap">Válida hasta:</label>
                    <input type="datetime-local" name="temporary_notes_until" class="form-input bg-gray-900 text-xs" value="{{ $account->temporary_notes_until }}">
                </div>
            </div>

            <div class="md:col-span-2 text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded font-bold shadow-lg transition transform hover:scale-105">
                    💾 Guardar Notas Operativas
                </button>
            </div>
        </form>
    </div>

</div>
@endsection