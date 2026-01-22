@extends('layouts.admin')
@section('title', 'Editar Conductor')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-lg bg-slate-800 rounded-lg border border-slate-700 p-6 shadow-xl">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-white">Editar Conductor</h2>
            <a href="{{ route('admin.drivers.index') }}" class="text-slate-400 hover:text-white text-sm">Cancelar</a>
        </div>
        
        <form action="{{ route('admin.drivers.update', $driver->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="flex justify-center mb-6">
                <div class="w-24 h-24 rounded-full bg-slate-700 overflow-hidden border-4 border-slate-600 relative group">
                    @if($driver->photo_path)
                        <img src="{{ Storage::url($driver->photo_path) }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-4xl">👤</div>
                    @endif
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">Nombre Completo</label>
                    <input type="text" name="full_name" value="{{ $driver->full_name }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                </div>
                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">No. Licencia / Cédula</label>
                    <input type="text" name="license_number" value="{{ $driver->license_number }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Teléfono</label>
                        <input type="text" name="phone" value="{{ $driver->phone }}" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                    </div>
                    <div>
                        <label class="block text-xs uppercase text-slate-400 mb-1">Estado</label>
                        <select name="status" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                            <option value="active" {{ $driver->status == 'active' ? 'selected' : '' }}>🟢 Activo</option>
                            <option value="inactive" {{ $driver->status == 'inactive' ? 'selected' : '' }}>🔴 Inactivo</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">Actualizar Foto</label>
                    <input type="file" name="photo" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-slate-400 text-xs">
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg transition">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
@endsection