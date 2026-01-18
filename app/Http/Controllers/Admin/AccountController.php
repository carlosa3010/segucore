<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmAccount;
use App\Models\Customer;
use App\Models\AlarmPartition; // Asegúrate de crear este Modelo
use Illuminate\Http\Request;

class AccountController extends Controller
{
    // 1. LISTADO GENERAL DE CUENTAS (VISTA INDEX QUE FALTABA)
    public function index(Request $request)
    {
        $query = AlarmAccount::with('customer');

        if ($request->has('search')) {
            $s = $request->search;
            $query->where('account_number', 'LIKE', "%$s%")
                  ->orWhere('branch_name', 'LIKE', "%$s%")
                  ->orWhereHas('customer', function($q) use ($s) {
                      $q->where('first_name', 'LIKE', "%$s%")
                        ->orWhere('last_name', 'LIKE', "%$s%")
                        ->orWhere('business_name', 'LIKE', "%$s%");
                  });
        }

        $accounts = $query->paginate(20);
        return view('admin.customers.accounts.index', compact('accounts'));
    }

    public function create(Request $request)
    {
        $customer = null;
        if ($request->has('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
        }
        // Si no viene cliente, obtenemos todos para el select (solo activos)
        $customers = Customer::where('is_active', true)->orderBy('created_at', 'desc')->take(50)->get();
        
        return view('admin.customers.accounts.create', compact('customer', 'customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'account_number' => 'required|string|unique:alarm_accounts,account_number',
            'branch_name' => 'nullable|string',
            'installation_address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'device_model' => 'nullable|string',
        ]);

        $account = AlarmAccount::create($validated + ['service_status' => 'active']);

        // Crear partición por defecto (Partición 1)
        $account->partitions()->create([
            'partition_number' => 1,
            'name' => 'Sistema General'
        ]);

        return redirect()->route('admin.accounts.show', $account->id)
            ->with('success', 'Cuenta creada. Configura las zonas ahora.');
    }

    public function show($id)
    {
        // Cargamos TODO: Cliente, Zonas, Particiones, Horarios (Si existe relación)
        $account = AlarmAccount::with(['customer.contacts', 'zones', 'partitions'])->findOrFail($id);
        return view('admin.customers.accounts.show', compact('account'));
    }

    // Método para guardar Particiones
    public function storePartition(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        $request->validate(['name' => 'required', 'partition_number' => 'required|integer']);
        
        // Simulación de modelo Partition (Debes crear App\Models\AlarmPartition)
        $account->partitions()->create($request->all()); 
        
        return back()->with('success', 'Partición agregada.');
    }
    
    // Método para actualizar notas
    public function updateNotes(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        $account->update($request->only(['permanent_notes', 'temporary_notes', 'temporary_notes_until']));
        return back()->with('success', 'Notas operativas actualizadas.');
    }
}