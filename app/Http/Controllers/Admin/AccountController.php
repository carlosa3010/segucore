<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmAccount;
use App\Models\Customer;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    // Mostrar formulario de creación (vinculado a un cliente)
    public function create(Request $request)
    {
        $customer = null;
        if ($request->has('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
        }
        
        // Si no hay cliente preseleccionado, podrías cargar una lista, 
        // pero por seguridad es mejor obligar a entrar desde la ficha del cliente.
        if (!$customer) {
            return redirect()->route('admin.customers.index')
                ->with('error', 'Seleccione un cliente primero para agregar una cuenta.');
        }

        return view('admin.customers.accounts.create', compact('customer'));
    }

    // Guardar la cuenta en la BD
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'account_number' => 'required|string|unique:alarm_accounts,account_number|max:20',
            'notes' => 'nullable|string|max:255',
        ]);

        AlarmAccount::create([
            'customer_id' => $request->customer_id,
            'account_number' => $request->account_number,
            'notes' => $request->notes,
            'is_active' => true,
        ]);

        return redirect()->route('admin.customers.show', $request->customer_id)
            ->with('success', 'Cuenta de alarma agregada correctamente.');
    }
}