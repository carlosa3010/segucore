@extends('layouts.admin')

@section('title', 'Panel: ' . $account->account_number)

@section('content')
<div x-data="{ 
    activeTab: localStorage.getItem('activeTab') || 'partitions',
    showWeeklyModal: false,
    
    // Estados para Modals de Edición
    editPartitionModal: false,
    partitionForm: { id: null, name: '', number: '' },

    editZoneModal: false,
    zoneForm: { id: null, number: '', name: '', type: '', partition_id: '' },

    editUserModal: false,
    userForm: { id: null, number: '', name: '', role: '' },

    editContactModal: false,
    contactForm: { id: null, priority: '', name: '', relationship: '', phone: '' },

    setTab(tab) {
        this.activeTab = tab;
        localStorage.setItem('activeTab', tab);
    },

    // Helpers para abrir modals con datos
    openEditPartition(p) {
        this.partitionForm = { id: p.id, name: p.name, number: p.partition_number };
        this.editPartitionModal = true;
    },
    openEditZone(z) {
        this.zoneForm = { id: z.id, number: z.zone_number, name: z.name, type: z.type, partition_id: z.partition_id };
        this.editZoneModal = true;
    },
    openEditUser(u) {
        this.userForm = { id: u.id, number: u.user_number, name: u.name, role: u.role };
        this.editUserModal = true;
    },
    openEditContact(c) {
        this.contactForm = { id: c.id, priority: c.priority, name: c.name, relationship: c.relationship, phone: c.phone };
        this.editContactModal = true;
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
        @foreach(['partitions' => '📂 Particiones', 'zones' => '🔢 Zonas', 'users' => '👥 Usuarios', 'contacts' => '📞 Contactos', 'schedule' => '🕒 Horarios', 'notes' => '📌 Notas', 'log' => '📜 Bitácora'] as $key => $label)
            <button @click="setTab('{{ $key }}')" 
                :class="activeTab === '{{ $key }}' ? 'border-[#C6F211] text-[#C6F211] bg-[#C6F211]/10' : 'border-transparent text-gray-400 hover:text-white hover:bg-gray-800'" 
                class="py-3 px-5 border-b-2 font-bold text-sm transition whitespace-nowrap">
                {{ $label }}
            </button>
        @endforeach
    </div>

    <div x-show="activeTab === 'partitions'" x-cloak>
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
                            <td class="px-6 py-4 font-mono text-white font-bold text-lg bg-gray-800/30 w-16 text-center border-r border-gray-700">{{ $part->partition_number }}</td>
                            <td class="px-6 py-4"><span class="text-white font-medium text-base">{{ $part->name }}</span></td>
                            <td class="px-6 py-4 text-right">
                                <button @click="openEditPartition({{ $part }})" class="text-blue-400 hover:text-blue-300 font-bold mr-3" title="Editar">✏️</button>
                                
                                @if($part->partition_number != 1)
                                    <form action="{{ route('partitions.destroy', $part->id) }}" method="POST" onsubmit="return confirm('¿Eliminar partición? Se borrarán sus zonas asociadas.');" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-red-500 hover:text-red-300 font-bold" title="Eliminar">🗑️</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-500">Sin particiones.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'zones'" x-cloak>
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
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-4 rounded text-sm h-[42px] shadow transition">
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
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($account->zones->sortBy('zone_number') as $zone)
                        <tr class="hover:bg-gray-700/30 transition group">
                            <td class="px-4 py-3 font-mono text-white font-bold text-center border-r border-gray-700">{{ $zone->zone_number }}</td>
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ $zone->partition ? 'P'.$zone->partition->partition_number : 'P-' }}
                            </td>
                            <td class="px-4 py-3 text-white">{{ $zone->name }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs border border-gray-600 text-gray-300">{{ $zone->type }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button @click="openEditZone({{ $zone }})" class="text-blue-400 hover:text-blue-300 font-bold mr-2" title="Editar">✏️</button>
                                <form action="{{ route('admin.zones.destroy', $zone->id) }}" method="POST" onsubmit="return confirm('¿Borrar zona?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-red-300 font-bold px-2 py-1">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-500 italic border-2 border-dashed border-gray-700">No se han cargado zonas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'users'" x-cloak>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-5 shadow-lg">
            <h3 class="text-white font-bold text-lg mb-4 border-b border-gray-700 pb-2">Usuarios de Teclado</h3>
            
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
                    + Agregar
                </button>
            </form>

            <table class="w-full text-sm text-left text-gray-400">
                <thead class="bg-gray-800 text-xs uppercase">
                    <tr><th class="px-4 py-3">Slot</th><th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Rol</th><th class="px-4 py-3 text-right"></th></tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($account->panelUsers->sortBy('user_number') as $user)
                        <tr class="hover:bg-gray-700/30 group">
                            <td class="px-4 py-3 font-mono text-white">{{ $user->user_number }}</td>
                            <td class="px-4 py-3 text-white">{{ $user->name }}</td>
                            <td class="px-4 py-3">{{ ucfirst($user->role) }}</td>
                            <td class="px-4 py-3 text-right">
                                <button @click="openEditUser({{ $user }})" class="text-blue-400 hover:text-blue-300 font-bold mr-2" title="Editar">✏️</button>
                                <form action="{{ route('admin.accounts.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('¿Borrar usuario?');" class="inline">
                                    @csrf @method('DELETE')
                                    <button class="text-red-500 hover:text-red-300 font-bold">🗑️</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-8 text-center text-gray-500">Sin usuarios registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="activeTab === 'contacts'" x-cloak>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <div class="space-y-3">
                <h3 class="text-white font-bold mb-4">Lista de Contactos (Orden de Llamada)</h3>
                
                @forelse($account->customer->contacts->sortBy('priority') as $contact)
                    <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded border border-gray-600 hover:border-blue-500 transition group">
                        <div class="flex items-center gap-4">
                            <div class="bg-blue-600 text-white w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-md border border-blue-400">
                                {{ $contact->priority }}
                            </div>
                            <div>
                                <p class="text-white text-sm font-bold">{{ $contact->name }}</p>
                                <p class="text-xs text-gray-400 uppercase tracking-wider">{{ $contact->relationship }}</p>
                            </div>
                        </div>
                        <div class="text-right flex items-center gap-2">
                            <p class="text-white font-mono text-sm mr-2 bg-black/30 px-2 py-1 rounded">{{ $contact->phone }}</p>
                            <button @click="openEditContact({{ $contact }})" class="text-blue-400 hover:text-blue-300 text-xs font-bold px-2">✏️</button>
                            <form action="{{ route('admin.contacts.destroy', $contact->id) }}" method="POST" onsubmit="return confirm('¿Eliminar contacto?');" class="inline">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-200 text-xs transition px-2 font-bold">✕</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-700 rounded bg-gray-800/20">
                        No hay contactos de emergencia registrados.
                    </div>
                @endforelse
            </div>

            <div class="bg-gray-900/30 p-5 rounded border border-gray-700 h-fit shadow-lg">
                <h4 class="text-gray-300 font-bold text-sm mb-4 border-b border-gray-700 pb-2">Nuevo Contacto</h4>
                
                <form action="{{ route('admin.customers.contacts.store', $account->customer_id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="flex gap-4">
                        <div class="w-24">
                            <label class="text-[10px] text-gray-500 uppercase font-bold mb-1 block">Prioridad</label>
                            <input type="number" name="priority" class="form-input text-center font-bold text-white bg-gray-800 border-blue-500/50 focus:border-blue-500" 
                                   value="{{ $account->customer->contacts->count() + 1 }}" min="1" required>
                        </div>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 uppercase font-bold mb-1 block">Nombre Completo</label>
                            <input type="text" name="name" class="form-input" placeholder="Ej: Juan Pérez" required>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[10px] text-gray-500 uppercase font-bold mb-1 block">Relación</label>
                            <input type="text" name="relationship" class="form-input" placeholder="Ej: Vecino, Esposo" required>
                        </div>
                        <div>
                            <label class="text-[10px] text-gray-500 uppercase font-bold mb-1 block">Teléfono</label>
                            <input type="text" name="phone" class="form-input" placeholder="0414-XXXXXXX" required>
                        </div>
                    </div>

                    <button type="submit" class="w-full bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 rounded text-sm shadow mt-2 transition transform hover:scale-[1.02]">
                        + Guardar Contacto
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div x-show="activeTab === 'schedule'" x-cloak>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-[#1e293b] rounded-lg border border-gray-700 p-6 text-center shadow-lg group">
                <div class="h-12 w-12 bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl group-hover:bg-blue-600 group-hover:text-white transition">📅</div>
                <h3 class="text-white font-bold text-lg mb-2">Horario Semanal</h3>
                <p class="text-gray-500 text-sm mb-6">Apertura y cierre habitual (Lunes a Domingo).</p>
                <button @click="showWeeklyModal = true" class="bg-gray-700 hover:bg-gray-600 text-white px-6 py-2 rounded font-bold transition w-full">Configurar Semana</button>
            </div>
            <div class="bg-[#1e293b] rounded-lg border border-purple-500/30 p-6 shadow-lg relative">
                <div class="flex items-center gap-3 mb-4">
                    <div class="h-10 w-10 bg-purple-900/40 rounded-full flex items-center justify-center text-xl text-purple-300">🚀</div>
                    <div>
                        <h3 class="text-white font-bold text-lg">Horario Temporal</h3>
                        <p class="text-[10px] text-purple-400 font-bold uppercase tracking-wider">Prioridad Alta</p>
                    </div>
                </div>
                <div class="mb-4 space-y-2">
                    @foreach($account->schedules->where('type', 'temporary') as $sched)
                        <div class="flex justify-between items-center bg-purple-900/20 p-2 rounded border border-purple-500/30 text-xs">
                            <div>
                                <span class="text-purple-200 font-bold block">{{ $sched->reason }}</span>
                                <span class="text-gray-400">Vence: {{ optional($sched->valid_until)->format('d/m/Y') }}</span>
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
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Válido Hasta</label>
                        <input type="date" name="valid_until" class="form-input bg-gray-900 border-gray-600" required>
                    </div>
                    <button type="submit" class="w-full bg-purple-700 hover:bg-purple-600 text-white px-4 py-2 rounded font-bold transition shadow-lg mt-2">Crear Excepción</button>
                </form>
            </div>
        </div>
    </div>

    <div x-show="activeTab === 'notes'" x-cloak>
        <form action="{{ route('admin.accounts.notes.update', $account->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @csrf @method('PUT')
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
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-8 py-3 rounded font-bold shadow-lg transition transform hover:scale-105">💾 Guardar Notas</button>
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

    <div x-show="editPartitionModal" style="display: none;" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-transition>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 w-full max-w-md shadow-2xl" @click.away="editPartitionModal = false">
            <form :action="'/partitions/' + partitionForm.id" method="POST">
                @csrf @method('PUT')
                <div class="p-4 border-b border-gray-700"><h3 class="text-white font-bold">Editar Área #<span x-text="partitionForm.number"></span></h3></div>
                <div class="p-4">
                    <label class="text-[10px] text-gray-500 uppercase block mb-1">Nombre</label>
                    <input type="text" name="name" x-model="partitionForm.name" class="form-input" required>
                </div>
                <div class="p-4 bg-gray-800 border-t border-gray-700 flex justify-end gap-3">
                    <button type="button" @click="editPartitionModal = false" class="text-gray-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded font-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="editZoneModal" style="display: none;" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-transition>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 w-full max-w-lg shadow-2xl" @click.away="editZoneModal = false">
            <form :action="'/zones/' + zoneForm.id" method="POST">
                @csrf @method('PUT')
                <div class="p-4 border-b border-gray-700"><h3 class="text-white font-bold">Editar Zona</h3></div>
                <div class="p-4 grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Número</label>
                        <input type="text" name="zone_number" x-model="zoneForm.number" class="form-input bg-gray-700" readonly>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Partición</label>
                        <select name="partition_id" x-model="zoneForm.partition_id" class="form-input text-sm">
                            @foreach($account->partitions as $p)
                                <option value="{{ $p->id }}">{{ $p->partition_number }} - {{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Descripción</label>
                        <input type="text" name="name" x-model="zoneForm.name" class="form-input" required>
                    </div>
                    <div class="col-span-2">
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Tipo</label>
                        <select name="type" x-model="zoneForm.type" class="form-input text-sm">
                            <option value="Instantánea">Instantánea</option>
                            <option value="Retardada">Retardada</option>
                            <option value="24 Horas">24 Horas</option>
                            <option value="Fuego">Fuego</option>
                            <option value="Pánico">Pánico</option>
                            <option value="Médica">Médica</option>
                        </select>
                    </div>
                </div>
                <div class="p-4 bg-gray-800 border-t border-gray-700 flex justify-end gap-3">
                    <button type="button" @click="editZoneModal = false" class="text-gray-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded font-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="editUserModal" style="display: none;" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-transition>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 w-full max-w-md shadow-2xl" @click.away="editUserModal = false">
            <form :action="'/admin/panel-users/' + userForm.id" method="POST">
                @csrf @method('PUT')
                <div class="p-4 border-b border-gray-700"><h3 class="text-white font-bold">Editar Usuario</h3></div>
                <div class="p-4 space-y-3">
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Slot #</label>
                        <input type="text" name="user_number" x-model="userForm.number" class="form-input" required>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Nombre</label>
                        <input type="text" name="name" x-model="userForm.name" class="form-input" required>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Rol</label>
                        <select name="role" x-model="userForm.role" class="form-input text-sm">
                            <option value="user">Usuario Estándar</option>
                            <option value="master">Maestro</option>
                            <option value="duress">Coacción (Silencioso)</option>
                        </select>
                    </div>
                </div>
                <div class="p-4 bg-gray-800 border-t border-gray-700 flex justify-end gap-3">
                    <button type="button" @click="editUserModal = false" class="text-gray-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded font-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="editContactModal" style="display: none;" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-transition>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 w-full max-w-md shadow-2xl" @click.away="editContactModal = false">
            <form :action="'/admin/contacts/' + contactForm.id" method="POST">
                @csrf @method('PUT')
                <div class="p-4 border-b border-gray-700"><h3 class="text-white font-bold">Editar Contacto</h3></div>
                <div class="p-4 space-y-3">
                    <div class="flex gap-3">
                        <div class="w-1/4">
                            <label class="text-[10px] text-gray-500 uppercase block mb-1">Prioridad</label>
                            <input type="number" name="priority" x-model="contactForm.priority" class="form-input text-center font-bold" min="1" required>
                        </div>
                        <div class="flex-1">
                            <label class="text-[10px] text-gray-500 uppercase block mb-1">Nombre</label>
                            <input type="text" name="name" x-model="contactForm.name" class="form-input" required>
                        </div>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Relación</label>
                        <input type="text" name="relationship" x-model="contactForm.relationship" class="form-input" required>
                    </div>
                    <div>
                        <label class="text-[10px] text-gray-500 uppercase block mb-1">Teléfono</label>
                        <input type="text" name="phone" x-model="contactForm.phone" class="form-input" required>
                    </div>
                </div>
                <div class="p-4 bg-gray-800 border-t border-gray-700 flex justify-end gap-3">
                    <button type="button" @click="editContactModal = false" class="text-gray-400 hover:text-white">Cancelar</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-2 rounded font-bold">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showWeeklyModal" style="display: none;" class="fixed inset-0 bg-black/80 z-50 flex items-center justify-center p-4 backdrop-blur-sm" x-transition>
        <div class="bg-[#1e293b] rounded-lg border border-gray-700 w-full max-w-3xl shadow-2xl overflow-hidden" @click.away="showWeeklyModal = false">
            <form action="{{ route('admin.accounts.schedules.weekly.store', $account->id) }}" method="POST">
                @csrf
                <div class="p-6 border-b border-gray-700 bg-gray-800">
                    <h3 class="text-white font-bold text-xl">Configuración de Horario Semanal</h3>
                    <p class="text-gray-400 text-sm">Define las horas de apertura y cierre esperadas.</p>
                </div>
                
                <div class="p-6 max-h-[60vh] overflow-y-auto">
                    <table class="w-full text-sm text-gray-300">
                        <thead>
                            <tr class="text-xs uppercase text-gray-500 border-b border-gray-700">
                                <th class="text-left py-2">Día</th>
                                <th class="text-center py-2">Apertura</th>
                                <th class="text-center py-2">Cierre</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-700">
                            @php
                                $days = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
                                $getOpen = fn($d) => optional($account->schedules->where('type', 'weekly')->where('day_of_week', $d)->first())->open_time ? \Carbon\Carbon::parse($account->schedules->where('type', 'weekly')->where('day_of_week', $d)->first()->open_time)->format('H:i') : '';
                                $getClose = fn($d) => optional($account->schedules->where('type', 'weekly')->where('day_of_week', $d)->first())->close_time ? \Carbon\Carbon::parse($account->schedules->where('type', 'weekly')->where('day_of_week', $d)->first()->close_time)->format('H:i') : '';
                            @endphp

                            @foreach($days as $num => $name)
                                <tr class="hover:bg-gray-700/30 transition">
                                    <td class="py-3 font-medium text-white">{{ $name }}</td>
                                    <td class="py-3 text-center">
                                        <input type="time" name="days[{{ $num }}][open]" value="{{ $getOpen($num) }}" class="bg-gray-900 border border-gray-600 rounded text-center text-white px-2 py-1 focus:border-blue-500 focus:outline-none">
                                    </td>
                                    <td class="py-3 text-center">
                                        <input type="time" name="days[{{ $num }}][close]" value="{{ $getClose($num) }}" class="bg-gray-900 border border-gray-600 rounded text-center text-white px-2 py-1 focus:border-blue-500 focus:outline-none">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 bg-gray-800 border-t border-gray-700 flex justify-end gap-3">
                    <button type="button" @click="showWeeklyModal = false" class="px-4 py-2 text-gray-400 hover:text-white transition">Cancelar</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white px-6 py-2 rounded font-bold shadow-lg">Guardar Horario</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection