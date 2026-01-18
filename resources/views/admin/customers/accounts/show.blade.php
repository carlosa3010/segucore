@extends('layouts.admin')

@section('title', 'Panel: ' . $account->account_number)

@section('content')
<div x-data="{ 
    activeTab: localStorage.getItem('activeTab') || 'partitions',
    showWeeklyModal: false,
    setTab(tab) {
        this.activeTab = tab;
        localStorage.setItem('activeTab', tab);
    }
}">

    <div class="bg-[#1e293b] border-b border-gray-700 p-6 mb-6 rounded-lg shadow-lg relative overflow-hidden">
        <div class="absolute top-0 right-0 p-4 opacity-5 pointer-events-none">
            <span class="text-9xl font-bold font-mono">{{ $account->account_number }}</span>
        </div>
        
        <div class="flex justify-between items-start relative z-10">
            <div class="flex gap-6">
                <div class="bg-black/40 p-4 rounded text-center min-w-[120px] border border-gray-600 shadow-inner">
                    <span class="block text-[10px] text-gray-500 uppercase tracking-widest mb-1">ABONADO</span>
                    <span class="text-3xl font-mono font-bold text-[#C6F211] tracking-wider">{{ $account->account_number }}</span>
                    <div class="mt-2 text-center">
                        @if($account->service_status === 'active')
                            <span class="text-[10px] text-green-400 font-bold uppercase flex justify-center items-center gap-1">
                                <span class="animate-pulse h-2 w-2 rounded-full bg-green-500"></span> Online
                            </span>
                        @else
                            <span class="text-[10px] text-red-400 font-bold uppercase flex justify-center items-center gap-1">
                                <span class="h-2 w-2 rounded-full bg-red-500"></span> Offline
                            </span>
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
                    </div>
                </div>
            </div>
            
            @if($account->permanent_notes)
                <div class="bg-red-900/10 border-l-4 border-red-500 p-4 max-w-md rounded-r shadow-lg backdrop-blur-sm">
                    <h4 class="text-red-400 font-bold text-xs uppercase mb-1">⚠️ Nota Operativa</h4>
                    <p class="text-white text-sm leading-relaxed">{{ $account->permanent_notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="flex border-b border-gray-700 mb-6 space-x-1 overflow-x-auto">
        @foreach(['partitions' => '📂 Particiones', 'zones' => '🔢 Zonas', 'users' => '👥 Usuarios', 'contacts' => '📞 Contactos', 'schedule' => '🕒 Horarios', 'notes' => '📌 Notas', 'log' => '📜 Bitácora'] as $key => $label)
            <button @click="setTab('{{ $key }}')" 
                :class="activeTab === '{{ $key }}' ? 'border-[#C6F211] text-[#C6F211] bg-[#C6F211]/10' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-800'" 
                class="py-3 px-5 border-b-2 font-bold text-sm transition whitespace-nowrap">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div x-show="activeTab === 'partitions'" x-cloak>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5">
            <h3 class="text-white font-bold text-lg mb-4">Particiones / Áreas</h3>
            
            <form action="{{ route('admin.accounts.partitions.store', $account->id) }}" method="POST" class="mb-6 bg-gray-900/50 p-4 rounded border border-gray-700 flex flex-col md:flex-row gap-4 items-end">
                @csrf
                <div class="w-full md:w-32">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">N° Área</label>
                    <input type="number" name="partition_number" class="form-input text-center font-mono font-bold" min="1" max="8" value="{{ ($account->partitions->max('partition_number') ?? 0) + 1 }}" required>
                </div>
                <div class="flex-1 w-full">
                    <label class="text-[10px] uppercase text-gray-500 font-bold mb-1">Nombre</label>
                    <input type="text" name="name" class="form-input" placeholder="Ej: Anexo" required>
                </div>
                <button type="submit" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 px-6 rounded text-sm shadow-lg">Crear</button>
            </form>

            <table class="w-full text-sm text-left text-gray-400">
                <thead class="bg-gray-800 text-xs uppercase text-gray-300">
                    <tr><th class="px-6 py-3">#</th><th class="px-6 py-3">Nombre</th><th class="px-6 py-3 text-right">Acción</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($account->partitions->sortBy('partition_number') as $part)
                        <tr class="hover:bg-gray-700/30">
                            <td class="px-6 py-4 font-mono text-white font-bold">{{ $part->partition_number }}</td>
                            <td class="px-6 py-4 text-white">{{ $part->name }}</td>
                            <td class="px-6 py-4 text-right">
                                @if($part->partition_number != 1)
                                    <form action="{{ route('admin.partitions.destroy', $part->id) }}" method="POST" onsubmit="return confirm('¿Eliminar partición? Se borrarán sus zonas.');" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:text-red-300 font-bold">🗑️</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'zones'" x-cloak>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5">
            {{-- 
            <form action="{{ route('admin.zones.destroy', $zone->id) }}" ...>
                @csrf @method('DELETE')
                <button class="text-red-500">🗑️</button>
            </form> 
            --}}
            @include('admin.customers.accounts.partials.zones_tab') 
            {{-- Sugerencia: Si es mucho código, puedes usar includes, pero por ahora pega el código anterior de zonas aquí --}}
        </div>
    </div>

    <div x-show="activeTab === 'users'" x-cloak>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5">
            <h3 class="text-white font-bold text-lg mb-4">Usuarios de Teclado</h3>
            
            <form action="{{ route('admin.accounts.users.store', $account->id) }}" method="POST" class="mb-6 bg-gray-900/50 p-4 rounded border border-gray-700 flex gap-4 items-end">
                @csrf
                <div class="w-24">
                    <label class="text-[10px] text-gray-500 mb-1 block">Slot #</label>
                    <input type="text" name="user_number" class="form-input text-center font-mono" placeholder="001" required>
                </div>
                <div class="flex-1">
                    <label class="text-[10px] text-gray-500 mb-1 block">Nombre</label>
                    <input type="text" name="name" class="form-input" required>
                </div>
                <div class="w-40">
                    <label class="text-[10px] text-gray-500 mb-1 block">Rol</label>
                    <select name="role" class="form-input text-sm">
                        <option value="user">Usuario</option>
                        <option value="master">Maestro</option>
                        <option value="duress">Coacción</option>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded text-sm">+ Agregar</button>
            </form>

            <table class="w-full text-sm text-left text-gray-400">
                <thead class="bg-gray-800 text-xs uppercase">
                    <tr><th class="px-4 py-3">Slot</th><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Rol</th><th class="px-4 py-3 text-right"></th></tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($account->panelUsers as $user)
                        <tr>
                            <td class="px-4 py-3 font-mono text-white">{{ $user->user_number }}</td>
                            <td class="px-4 py-3 text-white">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ ucfirst($user->role) }}</td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('admin.accounts.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Borrar?');">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-white">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'contacts'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-3">
                <h3 class="text-white font-bold mb-4">Lista de Contactos</h3>
                @foreach($account->customer->contacts as $index => $contact)
                    <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded border border-gray-600">
                        <div class="flex items-center gap-4">
                            <div class="bg-blue-600 text-white w-6 h-6 rounded-full flex items-center justify-center font-bold text-xs">{{ $index + 1 }}</div>
                            <div>
                                <p class="text-white text-sm font-bold">{{ $contact->name }}</p>
                                <p class="text-xs text-gray-400">{{ $contact->relationship }}</p>
                            </div>
                        </div>
                        <div class="text-right flex items-center gap-2">
                            <p class="text-white font-mono text-sm mr-2">{{ $contact->phone }}</p>
                            <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('¿Borrar contacto?');">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-white text-xs">✕</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="bg-gray-900/30 p-5 rounded border border-gray-700 h-fit">
                <h4 class="text-gray-300 font-bold text-sm mb-4">Nuevo Contacto</h4>
                <form action="{{ route('admin.customers.contacts.store', $account->customer_id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div><label class="text-[10px] text-gray-500 uppercase">Nombre</label><input type="text" name="name" class="form-input" required></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-[10px] text-gray-500 uppercase">Relación</label><input type="text" name="relationship" class="form-input" required></div>
                        <div><label class="text-[10px] text-gray-500 uppercase">Teléfono</label><input type="text" name="phone" class="form-input" required></div>
                    </div>
                    <button type="submit" class="w-full bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 rounded text-sm">Guardar</button>
                </form>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'schedule'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 text-center shadow-lg">
                <div class="h-12 w-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl">📅</div>
                <h3 class="text-white font-bold text-lg mb-2">Horario Semanal</h3>
                <p class="text-gray-500 text-sm mb-6">Apertura y cierre habitual (Lunes a Domingo).</p>
                <button @click="showWeeklyModal = true" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded font-bold transition w-full">
                    Configurar Semana
                </button>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-purple-500/30 p-6 shadow-lg">
                <h3 class="text-white font-bold text-lg flex items-center gap-2 mb-4">
                    <span class="text-purple-400">🚀</span> Horario Temporal
                </h3>
                
                <div class="space-y-2 mb-4">
                    @foreach($account->schedules->where('type', 'temporary') as $sched)
                        <div class="flex justify-between items-center bg-purple-900/20 p-2 rounded border border-purple-500/30 text-xs">
                            <div>
                                <span class="text-purple-200 font-bold block">{{ $sched->reason }}</span>
                                <span class="text-gray-400">Vence: {{ $sched->valid_until->format('d/m/Y') }}</span>
                            </div>
                            <form action="{{ route('admin.schedules.destroy', $sched->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-white font-bold px-2">✕</button>
                            </form>
                        </div>
                    @endforeach
                </div>

                <form action="{{ route('admin.accounts.schedules.temp.store', $account->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div><label class="text-[10px] text-gray-500 uppercase">Motivo</label><input type="text" name="reason" class="form-input bg-gray-900 border-gray-600" required></div>
                    <div class="grid grid-cols-2 gap-4">
                        <div><label class="text-[10px] text-gray-500 uppercase">Apertura</label><input type="time" name="open_time" class="form-input bg-gray-900 border-gray-600"></div>
                        <div><label class="text-[10px] text-gray-500 uppercase">Cierre</label><input type="time" name="close_time" class="form-input bg-gray-900 border-gray-600"></div>
                    </div>
                    <div><label class="text-[10px] text-gray-500 uppercase">Válido Hasta</label><input type="date" name="valid_until" class="form-input bg-gray-900 border-gray-600" required></div>
                    <button type="submit" class="w-full bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded font-bold mt-2">Crear Excepción</button>
                </form>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'notes'" x-cloak>
        <form action="{{ route('admin.accounts.notes.update', $account->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf @method('PUT')
            
            <div class="bg-[#1e293b] rounded-lg border border-red-900/30 p-5 shadow-lg">
                <h3 class="text-red-400 font-bold mb-2">⚠️ Nota Permanente</h3>
                <textarea name="permanent_notes" class="form-input bg-gray-900/50 border-gray-700 text-white h-40">{{ $account->permanent_notes }}</textarea>
            </div>

            <div class="bg-[#1e293b] rounded-lg border border-yellow-900/30 p-5 shadow-lg">
                <h3 class="text-yellow-400 font-bold mb-2">⏳ Nota Temporal</h3>
                <textarea name="temporary_notes" class="form-input bg-gray-900/50 border-gray-700 text-white h-24 mb-3">{{ $account->temporary_notes }}</textarea>
                <label class="text-xs text-gray-400">Válida hasta:</label>
                <input type="datetime-local" name="temporary_notes_until" class="form-input bg-gray-900 text-xs" value="{{ $account->temporary_notes_until }}">
            </div>

            <div class="md:col-span-2 text-right">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded font-bold shadow-lg">Guardar Notas</button>
            </div>
        </form>
    </div>

    <div x-show="activeTab === 'log'" x-cloak>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
            <h3 class="text-white font-bold mb-4">Bitácora de Eventos y Gestión</h3>
            
            <form action="{{ route('admin.accounts.log.store', $account->id) }}" method="POST" class="mb-6 bg-gray-900/50 p-4 rounded border border-gray-700">
                @csrf
                <div class="flex gap-4 mb-2">
                    <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="type" value="note" checked class="text-blue-500"> <span class="text-sm text-gray-300">Nota General</span></label>
                    <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="type" value="call" class="text-green-500"> <span class="text-sm text-gray-300">Llamada</span></label>
                    <label class="flex items-center gap-2 cursor-pointer"><input type="radio" name="type" value="alert" class="text-red-500"> <span class="text-sm text-gray-300">Alerta</span></label>
                </div>
                <textarea name="content" class="form-input w-full bg-gray-800 border-gray-600 text-sm mb-2" rows="2" placeholder="Escribe el detalle del evento..." required></textarea>
                <div class="text-right">
                    <button type="submit" class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-1 rounded text-sm">Registrar en Bitácora</button>
                </div>
            </form>

            <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                @if($account->logs && $account->logs->count() > 0)
                    @foreach($account->logs as $log)
                        <div class="flex gap-4 border-l-2 pl-4 {{ $log->type == 'alert' ? 'border-red-500' : ($log->type == 'call' ? 'border-green-500' : 'border-blue-500') }}">
                            <div class="flex-1">
                                <p class="text-gray-300 text-sm">{{ $log->content }}</p>
                                <div class="text-[10px] text-gray-500 mt-1 flex gap-2">
                                    <span>{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                    <span>•</span>
                                    <span>{{ $log->user->name ?? 'Sistema' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-gray-600 text-sm py-4">No hay registros en la bitácora.</p>
                @endif
            </div>
        </div>
    </div>

    <div x-show="showWeeklyModal" style="display: none;" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4">
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 w-full max-w-2xl shadow-2xl" @click.away="showWeeklyModal = false">
            <h3 class="text-white font-bold text-xl mb-4">Configuración Semanal</h3>
            <p class="text-gray-400 text-sm mb-4">Aquí iría una tabla de Lunes a Domingo con inputs de hora para cada día. (Funcionalidad pendiente de implementar en controlador).</p>
            <div class="flex justify-end gap-2">
                <button @click="showWeeklyModal = false" class="px-4 py-2 text-gray-300 hover:text-white">Cancelar</button>
                <button class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded font-bold">Guardar Cambios</button>
            </div>
        </div>
    </div>

</div>
@endsection