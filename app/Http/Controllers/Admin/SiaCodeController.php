<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SiaCode;
use Illuminate\Http\Request;

class SiaCodeController extends Controller
{
    public function index()
    {
        // Ordenar por prioridad (descendente) y luego por código
        $codes = SiaCode::orderBy('priority', 'desc')->orderBy('code', 'asc')->paginate(20);
        return view('admin.sia_codes.index', compact('codes'));
    }

    public function create()
    {
        return view('admin.sia_codes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:sia_codes,code',
            'description' => 'required|string|max:255',
            'priority' => 'required|integer|min:0|max:5',
            'color_hex' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'sound_alert' => 'nullable|string',
        ]);

        SiaCode::create($validated);

        return redirect()->route('admin.sia-codes.index')
            ->with('success', 'Código SIA creado correctamente.');
    }

    public function edit($id)
    {
        $siaCode = SiaCode::findOrFail($id);
        return view('admin.sia_codes.edit', compact('siaCode'));
    }

    public function update(Request $request, $id)
    {
        $siaCode = SiaCode::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:3|unique:sia_codes,code,' . $id,
            'description' => 'required|string|max:255',
            'priority' => 'required|integer|min:0|max:5',
            'color_hex' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
            'sound_alert' => 'nullable|string',
        ]);

        $siaCode->update($validated);

        return redirect()->route('admin.sia-codes.index')
            ->with('success', 'Código SIA actualizado.');
    }

    public function destroy($id)
    {
        $siaCode = SiaCode::findOrFail($id);
        $siaCode->delete();

        return redirect()->route('admin.sia-codes.index')
            ->with('success', 'Código eliminado.');
    }
}