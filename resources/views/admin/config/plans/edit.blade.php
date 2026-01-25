@extends('layouts.admin')

@section('title', 'Editar Plan: ' . $plan->name)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-white">Editar Plan de Servicio</h1>
            <p class="text-gray-400 text-sm">Modifica las tasas y condiciones del plan <span class="text-[#C6F211] font-mono">{{ $plan->name }}</span></p>
        </div>
        <a href="{{ route('admin.config.plans.index') }}" class="text-gray-400 hover:text-white transition">&larr; Volver al listado</a>
    </div>

    <form action="{{ route('admin.config.plans.update', $plan->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <div class="md:col-span-2 space-y-6">
                <div class="bg-[#1e293b] p-6 rounded-lg border border-gray-700 shadow-xl">
                    <h3 class="text-[#C6F211] font-bold uppercase text-xs mb-4 border-b border-gray-700 pb-2">Datos Generales</h3>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Nombre del Plan</label>
                            <input type="text" name="name" value="{{ old('name', $plan->name) }}" 
                                   class="form-input" required placeholder="Ej: Plan Residencial Premium">
                            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Descripción / Notas Internas</label>
                            <textarea name="description" class="form-input" rows="4" 
                                      placeholder="Detalles sobre lo que incluye este plan...">{{ old('description', $plan->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-[#1e293b] p-6 rounded-lg border border-gray-700 shadow-xl">
                    <h3 class="text-blue-400 font-bold uppercase text-xs mb-4 border-b border-gray-700 pb-2">Estructura de Costos (USD)</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1 font-semibold">Precio Base</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" 
                                       class="form-input pl-7" required>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Cobro fijo mensual por mantenimiento.</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1 font-semibold text-blue-300">Tasa x GPS</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                <input type="number" step="0.01" name="gps_price" value="{{ old('gps_price', $plan->gps_price) }}" 
                                       class="form-input pl-7 border-blue-500/30" required>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Monto por cada vehículo activo.</p>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1 font-semibold text-red-300">Tasa x Alarma</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">$</span>
                                <input type="number" step="0.01" name="alarm_price" value="{{ old('alarm_price', $plan->alarm_price) }}" 
                                       class="form-input pl-7 border-red-500/30" required>
                            </div>
                            <p class="text-[10px] text-gray-500 mt-1">Monto por cada cuenta/panel.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-[#1e293b] p-6 rounded-lg border border-gray-700 shadow-xl">
                    <h3 class="text-gray-400 font-bold uppercase text-xs mb-4 border-b border-gray-700 pb-2">Configuración</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Ciclo de Facturación</label>
                            <select name="billing_cycle" class="form-input">
                                <option value="monthly" {{ $plan->billing_cycle == 'monthly' ? 'selected' : '' }}>Mensual</option>
                                <option value="yearly" {{ $plan->billing_cycle == 'yearly' ? 'selected' : '' }}>Anual</option>
                                <option value="quarterly" {{ $plan->billing_cycle == 'quarterly' ? 'selected' : '' }}>Trimestral</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-3 pt-2">
                            <input type="checkbox" name="is_active" id="is_active" value="1" 
                                   {{ $plan->is_active ? 'checked' : '' }}
                                   class="w-5 h-5 rounded bg-gray-800 border-gray-600 text-[#C6F211] focus:ring-[#C6F211]">
                            <label for="is_active" class="text-white text-sm font-bold cursor-pointer">Plan Habilitado</label>
                        </div>
                    </div>
                </div>

                <div class="bg-blue-900/20 p-4 rounded-lg border border-blue-500/30">
                    <div class="flex gap-3">
                        <span class="text-blue-400 text-xl">ℹ️</span>
                        <p class="text-xs text-blue-200 leading-relaxed">
                            Los cambios en las tasas se aplicarán únicamente a las **próximas facturas** que se generen. Las facturas emitidas anteriormente mantendrán su valor histórico.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-3">
                    <button type="submit" class="w-full bg-[#C6F211] hover:bg-[#a3c90d] text-black font-bold py-3 rounded shadow-lg transform hover:scale-[1.02] transition">
                        Actualizar Plan
                    </button>
                    <a href="{{ route('admin.config.plans.index') }}" class="w-full bg-gray-700 hover:bg-gray-600 text-white font-bold py-3 rounded text-center transition">
                        Cancelar
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection