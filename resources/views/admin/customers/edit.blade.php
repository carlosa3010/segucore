@extends('layouts.admin')

@section('title', 'Editar Cliente: ' . $customer->full_name)

@section('content')
<div class="max-w-5xl mx-auto" x-data="{ type: '{{ old('type', $customer->type) }}' }">
    
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Editar Cliente</h1>
        <a href="{{ route('admin.customers.show', $customer->id) }}" class="text-gray-400 hover:text-white transition">&larr; Cancelar</a>
    </div>

    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST" class="bg-[#1e293b] p-8 rounded-lg border border-gray-700 shadow-xl">
        @csrf
        @method('PUT')

        <div class="mb-8 flex gap-6 border-b border-gray-700 pb-6">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="radio" name="type" value="person" x-model="type" class="w-5 h-5 text-[#C6F211] focus:ring-[#C6F211] bg-gray-800 border-gray-600">
                <span class="text-white font-bold" :class="type=='person' ? 'text-[#C6F211]' : ''">Persona Natural</span>
            </label>
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="radio" name="type" value="company" x-model="type" class="w-5 h-5 text-[#C6F211] focus:ring-[#C6F211] bg-gray-800 border-gray-600">
                <span class="text-white font-bold" :class="type=='company' ? 'text-[#C6F211]' : ''">Empresa / Jurídico</span>
            </label>
        </div>

        <h3 class="text-[#C6F211] font-bold uppercase text-xs mb-4">Datos Fiscales y Legales</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <div>
                <label class="block text-sm text-gray-400 mb-1" x-text="type=='person' ? 'Cédula / ID *' : 'RIF / Tax ID *'"></label>
                <input type="text" name="national_id" required 
                       class="form-input font-mono tracking-wide" 
                       value="{{ old('national_id', $customer->national_id) }}">
                @error('national_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div x-show="type === 'company'" class="md:col-span-2">
                <label class="block text-sm text-gray-400 mb-1">Razón Social (Nombre Empresa) *</label>
                <input type="text" name="business_name" 
                       class="form-input" 
                       value="{{ old('business_name', $customer->business_name) }}"
                       placeholder="Inversiones El Éxito C.A.">
            </div>

            <div>
                <label class="block text-sm text-gray-400 mb-1" x-text="type=='person' ? 'Nombres *' : 'Nombre Representante'"></label>
                <input type="text" name="first_name" 
                       class="form-input" 
                       value="{{ old('first_name', $customer->first_name) }}">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1" x-text="type=='person' ? 'Apellidos *' : 'Apellido Representante'"></label>
                <input type="text" name="last_name" 
                       class="form-input" 
                       value="{{ old('last_name', $customer->last_name) }}">
            </div>
        </div>

        <h3 class="text-[#C6F211] font-bold uppercase text-xs mb-4 mt-8 border-t border-gray-700 pt-6">Ubicación y Contacto</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Teléfono Principal *</label>
                <input type="text" name="phone_1" required 
                       class="form-input" 
                       value="{{ old('phone_1', $customer->phone_1) }}">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Teléfono Secundario</label>
                <input type="text" name="phone_2" 
                       class="form-input" 
                       value="{{ old('phone_2', $customer->phone_2) }}">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Email Facturación</label>
                <input type="email" name="email" 
                       class="form-input" 
                       value="{{ old('email', $customer->email) }}">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Ciudad</label>
                <input type="text" name="city" required 
                       class="form-input" 
                       value="{{ old('city', $customer->city) }}">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-400 mb-1">Dirección Fiscal *</label>
                <textarea name="address_billing" required class="form-input" rows="2">{{ old('address_billing', $customer->address_billing) }}</textarea>
            </div>
        </div>

        <h3 class="text-red-400 font-bold uppercase text-xs mb-4 mt-8 border-t border-gray-700 pt-6">Seguridad Maestra</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Palabra Clave</label>
                <input type="text" name="monitoring_password" 
                       class="form-input border-blue-500/30 text-blue-100" 
                       value="{{ old('monitoring_password', $customer->monitoring_password) }}">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Clave Coacción</label>
                <input type="text" name="duress_password" 
                       class="form-input border-red-500/30 text-red-200" 
                       value="{{ old('duress_password', $customer->duress_password) }}">
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-700">
            <label class="flex items-center gap-3">
                <input type="checkbox" name="is_active" value="1" 
                       class="w-5 h-5 rounded bg-gray-800 border-gray-600 text-[#C6F211] focus:ring-[#C6F211]"
                       {{ $customer->is_active ? 'checked' : '' }}>
                <span class="text-white text-sm font-bold">Cliente Activo</span>
            </label>
        </div>

        <div class="mt-8 flex justify-end gap-4">
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="px-6 py-3 rounded text-gray-400 hover:bg-gray-800 transition">Cancelar</a>
            <button type="submit" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-3 px-8 rounded shadow-lg transform hover:scale-105 transition">
                Actualizar Datos
            </button>
        </div>
    </form>
</div>
@endsection