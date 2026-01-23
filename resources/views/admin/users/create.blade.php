@extends('layouts.admin')
@section('title', 'Nuevo Usuario')

@section('content')
<div class="max-w-xl mx-auto bg-slate-800 p-8 rounded-lg border border-slate-700 shadow-xl">
    <h2 class="text-2xl font-bold text-white mb-6">Crear Nuevo Usuario</h2>

    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm text-slate-400 mb-1">Nombre Completo</label>
            <input type="text" name="name" class="form-input w-full rounded bg-slate-900 border-slate-600 text-white focus:border-blue-500" required value="{{ old('name') }}">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Correo Electrónico</label>
            <input type="email" name="email" class="form-input w-full rounded bg-slate-900 border-slate-600 text-white focus:border-blue-500" required value="{{ old('email') }}">
            @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1">Contraseña</label>
                <input type="password" name="password" class="form-input w-full rounded bg-slate-900 border-slate-600 text-white focus:border-blue-500" required>
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block text-sm text-slate-400 mb-1">Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" class="form-input w-full rounded bg-slate-900 border-slate-600 text-white focus:border-blue-500" required>
            </div>
        </div>

        <div>
            <label class="block text-sm text-slate-400 mb-1">Rol de Usuario</label>
            <select name="role" id="roleSelect" class="form-select w-full rounded bg-slate-900 border-slate-600 text-white focus:border-blue-500" onchange="toggleCustomerSelect()">
                <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operador (Monitoreo)</option>
                <option value="supervisor" {{ old('role') == 'supervisor' ? 'selected' : '' }}>Supervisor (Gestión)</option>
                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador (Total)</option>
                <option value="client" {{ old('role') == 'client' ? 'selected' : '' }}>Cliente (Solo Lectura)</option>
            </select>
            @error('role') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div id="customerContainer" class="hidden bg-slate-700/50 p-4 rounded border border-slate-600">
            <label class="block text-sm text-blue-400 font-bold mb-1">Asignar a Cliente (Empresa/Persona)</label>
            <p class="text-xs text-slate-400 mb-2">Este usuario solo verá los datos asociados a este cliente.</p>
            
            <select name="customer_id" class="form-select w-full rounded bg-slate-900 border-slate-600 text-white focus:border-blue-500">
                <option value="">-- Seleccione un Cliente --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} ({{ $customer->dni_cif }})
                    </option>
                @endforeach
            </select>
            @error('customer_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" name="is_active" id="active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="rounded bg-slate-700 border-slate-600 text-blue-600 focus:ring-blue-500">
            <label for="active" class="text-sm text-slate-300">Usuario Activo (Puede iniciar sesión)</label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-slate-700">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-slate-400 hover:text-white transition">Cancelar</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded transition shadow-lg shadow-blue-900/50">
                Guardar Usuario
            </button>
        </div>
    </form>
</div>

<script>
    function toggleCustomerSelect() {
        const role = document.getElementById('roleSelect').value;
        const container = document.getElementById('customerContainer');
        
        if (role === 'client') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
            // Opcional: limpiar la selección si cambia de rol
            // container.querySelector('select').value = ""; 
        }
    }

    // Ejecutar al cargar por si hay un error de validación y volvemos con 'client' seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        toggleCustomerSelect();
    });
</script>
@endsection