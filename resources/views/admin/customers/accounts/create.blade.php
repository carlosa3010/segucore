@extends('layouts.admin')

@section('title', 'Nueva Cuenta de Alarma')

@section('content')
<div class="max-w-2xl mx-auto mt-10">
    <div class="bg-[#1e293b] border border-gray-700 rounded-lg shadow-xl overflow-hidden">
        
        <div class="bg-gray-800/50 p-6 border-b border-gray-700">
            <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <span class="text-[#C6F211]">📟</span> Nueva Cuenta de Alarma
            </h2>
            <p class="text-sm text-gray-400 mt-1">
                Vinculando panel para el cliente: <strong class="text-white">{{ $customer->full_name }}</strong>
            </p>
        </div>

        <form action="{{ route('admin.accounts.store') }}" method="POST" class="p-8">
            @csrf
            <input type="hidden" name="customer_id" value="{{ $customer->id }}">

            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-bold text-gray-300 mb-2">Número de Abonado (Account Number) *</label>
                    <input type="text" name="account_number" required 
                           class="form-input text-2xl font-mono tracking-widest text-center border-yellow-500/50 focus:border-yellow-400" 
                           placeholder="1234">
                    <p class="text-xs text-gray-500 mt-2">
                        Este código debe coincidir exactamente con el programado en el panel de alarma (SIA).
                    </p>
                    @error('account_number') <span class="text-red-500 text-sm block mt-1">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm text-gray-400 mb-2">Notas / Ubicación del Panel</label>
                    <textarea name="notes" rows="3" class="form-input" placeholder="Ej: Panel principal en pasillo, Planta Baja. Modelo DSC PowerSeries."></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-4">
                <a href="{{ route('admin.customers.show', $customer->id) }}" class="px-4 py-2 text-gray-400 hover:text-white transition">Cancelar</a>
                <button type="submit" class="bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-2 px-6 rounded shadow-lg transform hover:scale-105 transition">
                    Guardar Panel
                </button>
            </div>
        </form>
    </div>
</div>
@endsection