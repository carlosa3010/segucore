<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmAccount;
use App\Models\Customer;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    // Formulario de Creación (Viene del botón en Cliente)
    public function create(Request $request)
    {
        $customer = null;
        if ($request->has('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
        }
        
        // Si no hay cliente seleccionado, podrías cargar una lista, 
        // pero por ahora forzamos a venir desde la ficha del cliente.
        
        return view('admin.customers.accounts.create', compact('customer'));
    }

    // Guardar la Cuenta Básica
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'account_number' => 'required|string|unique:alarm_accounts,account_number',
            'branch_name' => 'nullable|string',
            'installation_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'device_model' => 'nullable|string', // Ej: DSC Neo
            'notes' => 'nullable|string',
        ]);

        $account = AlarmAccount::create($validated + ['service_status' => 'active']);

        // Redirigir a la Ficha de la Cuenta para configurar Zonas
        return redirect()->route('admin.accounts.show', $account->id)
            ->with('success', 'Panel creado. Ahora configura las zonas y ubicación.');
    }

    // LA FICHA TÉCNICA DEL PANEL (El cerebro de la configuración)
    public function show($id)
    {
        $account = AlarmAccount::with(['customer', 'zones'])->findOrFail($id);
        return view('admin.customers.accounts.show', compact('account'));
    }

    public function update(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        
        $account->update($request->validate([
            'branch_name' => 'nullable|string',
            'installation_address' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'notes' => 'nullable|string',
        ]));

        return back()->with('success', 'Datos del panel actualizados.');
    }
}