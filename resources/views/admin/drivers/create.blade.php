@extends('layouts.admin')
@section('title', 'Nuevo Conductor')

@section('content')
<div class="bg-slate-900 min-h-screen p-4 flex justify-center">
    <div class="w-full max-w-lg bg-slate-800 rounded-lg border border-slate-700 p-6 shadow-xl">
        <h2 class="text-xl font-bold text-white mb-6">Registrar Conductor</h2>
        
        <form action="{{ route('admin.drivers.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">Nombre Completo</label>
                    <input type="text" name="full_name" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                </div>
                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">No. Licencia / Cédula</label>
                    <input type="text" name="license_number" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white" required>
                </div>
                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">Teléfono</label>
                    <input type="text" name="phone" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-white">
                </div>
                <div>
                    <label class="block text-xs uppercase text-slate-400 mb-1">Fotografía</label>
                    <input type="file" name="photo" class="w-full bg-slate-900 border border-slate-600 rounded p-2 text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                </div>
            </div>
            
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('admin.drivers.index') }}" class="text-slate-400 text-sm py-2 px-4 hover:text-white">Cancelar</a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 px-6 rounded-lg">Guardar</button>
            </div>
        </form>
    </div>
</div>
@endsection