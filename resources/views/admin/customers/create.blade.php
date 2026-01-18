@extends('layouts.admin')

@section('title', 'Nuevo Cliente')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-white">Registrar Nuevo Abonado</h1>
            <a href="{{ route('admin.customers.index') }}" class="text-gray-400 hover:text-white transition">
                &larr; Cancelar y Volver
            </a>
        </div>

        <form action="{{ route('admin.customers.store') }}" method="POST" class="bg-[#1e293b] p-8 rounded-lg border border-gray-700 shadow-xl">
            @csrf

            <h3 class="text-[#C6F211] font-bold uppercase tracking-wider text-xs mb-4 border-b border-gray-700 pb-2">
                Datos de Identificación
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Nombre(s) *</label>
                    <input type="text" name="first_name" required class="form-input" placeholder="Ej: Juan Carlos">
                    @error('first_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Apellido(s) *</label>
                    <input type="text" name="last_name" required class="form-input" placeholder="Ej: Pérez González">
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Cédula / RIF / ID *</label>
                    <input type="text" name="national_id" required class="form-input" placeholder="V-12345678">
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-1">Correo Electrónico</label>
                    <input type="email" name="email" class="form-input" placeholder="cliente@email.com">
                </div>
            </div>

            <h3 class="text-[#C6F211] font-bold uppercase tracking-wider text-xs mb-4 border-b border-gray-700 pb-2">
                Ubicación y Contacto
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Teléfono Principal *</label>
                    <input type="text" name="phone_1" required class="form-input" placeholder="+58 414 ...">
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Teléfono Secundario</label>
                    <input type="text" name="phone_2" class="form-input" placeholder="+58 251 ...">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-gray-400 mb-1">Dirección de Monitoreo *</label>
                    <textarea name="address" rows="2" required class="form-input" placeholder="Urb. El Parral, Calle 2, Casa #45..."></textarea>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Ciudad / Sector</label>
                    <input type="text" name="city" class="form-input" placeholder="Barquisimeto">
                </div>
            </div>

            <h3 class="text-red-400 font-bold uppercase tracking-wider text-xs mb-4 border-b border-gray-700 pb-2">
                Seguridad y Contraseñas
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Palabra Clave (Verificación) *</label>
                    <input type="text" name="monitoring_password" required class="form-input border-blue-500/50" placeholder="Ej: PERRO AZUL">
                    <p class="text-xs text-gray-500 mt-1">Palabra para confirmar identidad vía telefónica.</p>
                </div>
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Clave de Coacción (Peligro)</label>
                    <input type="text" name="duress_password" class="form-input border-red-500/50 text-red-300" placeholder="Ej: CIELO ROJO">
                    <p class="text-xs text-gray-500 mt-1">Palabra que indica amenaza sin alertar al intruso.</p>
                </div>
            </div>

            <div class="flex justify-end gap-4 border-t border-gray-700 pt-6">
                <button type="reset" class="px-6 py-2 rounded text-gray-400 hover:text-white hover:bg-gray-800 transition">
                    Limpiar
                </button>
                <button type="submit" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 px-8 rounded shadow-lg shadow-lime-900/20 transform hover:scale-105 transition">
                    Guardar Cliente
                </button>
            </div>
        </form>
    </div>
@endsection