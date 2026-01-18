<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerContact; // Importante para eliminar contactos
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Listado de Clientes con Buscador Avanzado.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        // Buscador: Busca por Nombre, Apellido, Razón Social o Identificación
        if ($request->has('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('first_name', 'LIKE', "%$s%")
                  ->orWhere('last_name', 'LIKE', "%$s%")
                  ->orWhere('business_name', 'LIKE', "%$s%") // Buscar empresas
                  ->orWhere('national_id', 'LIKE', "%$s%");
            });
        }

        // Ordenar por fecha de creación descendente
        $customers = $query->withCount('accounts')->orderBy('created_at', 'desc')->paginate(15);
        
        return view('admin.customers.index', compact('customers'));
    }

    public function create()
    {
        return view('admin.customers.create');
    }

    /**
     * Guardar Cliente con validación condicional.
     */
    public function store(Request $request)
    {
        // 1. Reglas Base
        $rules = [
            'type' => 'required|in:person,company',
            'national_id' => 'required|string|unique:customers,national_id|max:20',
            'email' => 'nullable|email|max:255',
            'phone_1' => 'required|string|max:50',
            'phone_2' => 'nullable|string|max:50',
            'address_billing' => 'required|string',
            'city' => 'required|string|max:100',
            'monitoring_password' => 'nullable|string|max:50',
            'duress_password' => 'nullable|string|max:50',
        ];

        // 2. Validación Condicional
        if ($request->type === 'company') {
            $rules['business_name'] = 'required|string|max:255';
            $rules['first_name'] = 'nullable|string|max:100';
            $rules['last_name']  = 'nullable|string|max:100';
        } else {
            $rules['first_name'] = 'required|string|max:100';
            $rules['last_name']  = 'required|string|max:100';
            $rules['business_name'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        // 3. Crear
        $customer = Customer::create($validated);

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function show($id)
    {
        $customer = Customer::with(['accounts', 'gpsDevices', 'contacts', 'invoices'])
            ->findOrFail($id);

        return view('admin.customers.show', compact('customer'));
    }

    public function edit($id)
    {
        $customer = Customer::findOrFail($id);
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $rules = [
            'national_id' => 'required|string|max:20|unique:customers,national_id,'.$id,
            'phone_1' => 'required|string|max:50',
            'address_billing' => 'required|string',
            'city' => 'required|string',
        ];

        if ($request->type === 'company') {
            $rules['business_name'] = 'required|string|max:255';
        } else {
            $rules['first_name'] = 'required|string|max:100';
            $rules['last_name'] = 'required|string|max:100';
        }

        $customer->update($request->validate($rules) + $request->only([
            'email', 'phone_2', 'monitoring_password', 'duress_password', 'is_active', 'type'
        ]));

        return redirect()->route('admin.customers.show', $id)
            ->with('success', 'Datos del cliente actualizados.');
    }

    /**
     * Eliminar cliente y sus relaciones (Cascada).
     */
    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete(); 

        return redirect()->route('admin.customers.index')
            ->with('success', 'Cliente eliminado permanentemente.');
    }

    /**
     * Suspender/Reactivar Cliente y todos sus servicios.
     */
    public function toggleStatus($id)
    {
        $customer = Customer::findOrFail($id);
        
        $newStatus = !$customer->is_active; // Invertir estado
        $customer->update(['is_active' => $newStatus]);

        $message = $newStatus ? 'Cliente Reactivado.' : 'Cliente Suspendido.';

        // Lógica de Cascada: Si se suspende al cliente, suspender sus paneles activos
        if (!$newStatus) {
            $count = $customer->accounts()->where('service_status', 'active')
                ->update(['service_status' => 'suspended']);
            
            if ($count > 0) {
                $message .= " Se suspendieron $count cuentas de alarma asociadas.";
            }
        }

        return back()->with($newStatus ? 'success' : 'error', $message);
    }

    /**
     * Guardar Contacto de Emergencia (Nuevo)
     * Llamado desde la ficha del cliente o desde la cuenta de alarma.
     */
    public function storeContact(Request $request, $customerId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'relationship' => 'required|string|max:50',
            'phone' => 'required|string|max:50'
        ]);

        $customer = Customer::findOrFail($customerId);
        $customer->contacts()->create($validated);

        return back()->with('success', 'Contacto de emergencia agregado.');
    }

    /**
     * Eliminar Contacto (Nuevo)
     */
    public function destroyContact($id)
    {
        $contact = CustomerContact::findOrFail($id);
        $contact->delete();

        return back()->with('success', 'Contacto eliminado.');
    }
}