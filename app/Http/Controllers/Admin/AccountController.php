<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmAccount;
use App\Models\Customer;
// use App\Models\AlarmPartition; // No es estrictamente necesario importarlo si usamos la relación $account->partitions()
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Listado General de Cuentas de Monitoreo.
     */
    public function index(Request $request)
    {
        $query = AlarmAccount::with('customer');

        // Buscador Avanzado
        if ($request->has('search')) {
            $s = $request->search;
            $query->where('account_number', 'LIKE', "%$s%")
                  ->orWhere('branch_name', 'LIKE', "%$s%")
                  ->orWhere('installation_address', 'LIKE', "%$s%")
                  ->orWhereHas('customer', function($q) use ($s) {
                      $q->where('first_name', 'LIKE', "%$s%")
                        ->orWhere('last_name', 'LIKE', "%$s%")
                        ->orWhere('business_name', 'LIKE', "%$s%")
                        ->orWhere('national_id', 'LIKE', "%$s%");
                  });
        }

        $accounts = $query->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.customers.accounts.index', compact('accounts'));
    }

    /**
     * Formulario de Creación.
     */
    public function create(Request $request)
    {
        $customer = null;
        // Si venimos desde la ficha de un cliente, pre-cargamos ese cliente
        if ($request->has('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
        }
        
        // Obtenemos lista de clientes activos para el selector (por si no viene pre-seleccionado)
        $customers = Customer::where('is_active', true)
                             ->orderBy('created_at', 'desc')
                             ->take(100) // Límite por rendimiento
                             ->get();
        
        return view('admin.customers.accounts.create', compact('customer', 'customers'));
    }

    /**
     * Guardar nueva cuenta.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'account_number' => 'required|string|unique:alarm_accounts,account_number|max:50',
            'branch_name' => 'nullable|string|max:100',
            'installation_address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'device_model' => 'nullable|string|max:100',
        ]);

        // Crear la cuenta con estado activo por defecto
        $account = AlarmAccount::create($validated + ['service_status' => 'active']);

        // Crear automáticamente la Partición 1 (Sistema General)
        // Esto evita errores visuales y da una base inicial
        $account->partitions()->create([
            'partition_number' => 1,
            'name' => 'Sistema General'
        ]);

        return redirect()->route('admin.accounts.show', $account->id)
            ->with('success', 'Cuenta creada exitosamente. Ahora configura las zonas.');
    }

    /**
     * Ficha Técnica (Dashboard del Panel).
     */
    public function show($id)
    {
        // Cargamos todas las relaciones necesarias para la vista "Maestra"
        $account = AlarmAccount::with(['customer.contacts', 'zones', 'partitions'])
            ->findOrFail($id);
            
        return view('admin.customers.accounts.show', compact('account'));
    }

    /**
     * Actualizar datos generales (Dirección, Coordenadas, Modelo).
     */
    public function update(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        
        $validated = $request->validate([
            'branch_name' => 'nullable|string|max:100',
            'installation_address' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'device_model' => 'nullable|string|max:100',
        ]);

        $account->update($validated);

        return back()->with('success', 'Datos del panel actualizados.');
    }

    /**
     * Eliminar Cuenta (y sus zonas/particiones/eventos en cascada).
     */
    public function destroy($id)
    {
        $account = AlarmAccount::findOrFail($id);
        $account->delete();

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Cuenta de monitoreo eliminada correctamente.');
    }

    /**
     * Agregar una Partición extra (ej: Anexo, Tienda).
     */
    public function storePartition(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        
        $request->validate([
            'partition_number' => 'required|integer|min:1|max:8', // Típicamente máx 8 particiones
            'name' => 'required|string|max:100'
        ]);
        
        // Verificar duplicidad de número de partición
        if($account->partitions()->where('partition_number', $request->partition_number)->exists()){
            return back()->with('error', 'El número de partición ya existe.');
        }

        $account->partitions()->create($request->all()); 
        
        return back()->with('success', 'Partición agregada.');
    }
    
    /**
     * Actualizar Notas Operativas (Permanentes y Temporales).
     */
    public function updateNotes(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        
        $account->update($request->only([
            'permanent_notes', 
            'temporary_notes', 
            'temporary_notes_until'
        ]));
        
        return back()->with('success', 'Notas operativas actualizadas.');
    }
}