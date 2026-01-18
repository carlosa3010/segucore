<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // Listado de Clientes
    public function index(Request $request)
    {
        $query = Customer::query();

        // Buscador simple
        if ($request->has('search')) {
            $s = $request->search;
            $query->where('first_name', 'LIKE', "%$s%")
                  ->orWhere('last_name', 'LIKE', "%$s%")
                  ->orWhere('national_id', 'LIKE', "%$s%");
        }

        $customers = $query->paginate(15);
        return view('admin.customers.index', compact('customers'));
    }

    // Formulario de Creación
    public function create()
    {
        return view('admin.customers.create');
    }

    // Guardar Cliente Nuevo
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'national_id' => 'required|string|unique:customers,national_id',
            'email' => 'nullable|email',
            'phone_1' => 'required|string',
            'address' => 'required|string',
            // Monitoring passwords
            'monitoring_password' => 'required|string|min:4',
            'duress_password' => 'nullable|string',
        ]);

        $customer = Customer::create($validated);

        return redirect()->route('admin.customers.show', $customer->id)
            ->with('success', 'Cliente registrado correctamente.');
    }

    // Ver Ficha Técnica del Cliente (Dashboard del Cliente en Admin)
    public function show($id)
    {
        // Cargamos relaciones vitales para la ficha técnica
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
        $customer->update($request->all());
        return redirect()->route('admin.customers.show', $id)->with('success', 'Datos actualizados.');
    }
}