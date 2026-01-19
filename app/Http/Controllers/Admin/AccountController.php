<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlarmAccount;
use App\Models\Customer;
use App\Models\AlarmPartition;
use App\Models\PanelUser;
use App\Models\AccountSchedule;
use App\Models\AccountLog;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AccountController extends Controller
{
    /**
     * 1. LISTADO GENERAL
     */
    public function index(Request $request)
    {
        $query = AlarmAccount::with('customer');

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
     * 2. CREACIÓN
     */
    public function create(Request $request)
    {
        $customer = null;
        if ($request->has('customer_id')) {
            $customer = Customer::findOrFail($request->customer_id);
        }
        
        $customers = Customer::where('is_active', true)
                             ->orderBy('created_at', 'desc')
                             ->take(100)
                             ->get();
        
        return view('admin.customers.accounts.create', compact('customer', 'customers'));
    }

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

        $account = AlarmAccount::create($validated + ['service_status' => 'active']);

        // Crear partición por defecto (Partición 1)
        $account->partitions()->create([
            'partition_number' => 1,
            'name' => 'Sistema General'
        ]);

        // Auditoría Inicial
        $account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'note',
            'content' => 'SISTEMA: Cuenta creada e inicializada.'
        ]);

        return redirect()->route('admin.accounts.show', $account->id)
            ->with('success', 'Cuenta creada. Configura las zonas ahora.');
    }

    /**
     * 3. FICHA TÉCNICA Y EDICIÓN
     */
    public function show($id)
    {
        // Cargamos todas las relaciones necesarias para la vista
        $account = AlarmAccount::with([
            'customer.contacts', 
            'zones.partition', 
            'partitions', 
            'panelUsers', 
            'schedules',
            'logs.user' // Cargamos logs y el usuario que creó el log
        ])->findOrFail($id);
            
        return view('admin.customers.accounts.show', compact('account'));
    }

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

    public function destroy($id)
    {
        $account = AlarmAccount::findOrFail($id);
        $account->delete(); 

        return redirect()->route('admin.accounts.index')
            ->with('success', 'Cuenta de monitoreo eliminada correctamente.');
    }

    /**
     * 4. GESTIÓN DE NOTAS Y BITÁCORA
     */
    public function updateNotes(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        $account->update($request->only(['permanent_notes', 'temporary_notes', 'temporary_notes_until']));
        
        // Auditoría
        $account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'note',
            'content' => 'SISTEMA: Se actualizaron las notas operativas.'
        ]);

        return back()->with('success', 'Notas operativas actualizadas.');
    }

    public function storeLog(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        
        $request->validate([
            'content' => 'required|string',
            'type' => 'required|in:note,call,alert'
        ]);

        $account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => $request->type,
            'content' => $request->content
        ]);

        return back()->with('success', 'Entrada agregada a la bitácora.');
    }

    /**
     * 5. GESTIÓN DE PARTICIONES
     */
    public function storePartition(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        $request->validate([
            'partition_number' => 'required|integer|min:1|max:8',
            'name' => 'required|string|max:100'
        ]);
        
        if($account->partitions()->where('partition_number', $request->partition_number)->exists()){
            return back()->with('error', 'El número de partición ya existe.');
        }

        $account->partitions()->create($request->all()); 

        // Auditoría
        $account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'note',
            'content' => "SISTEMA: Se agregó el Área #{$request->partition_number} ({$request->name})."
        ]);

        return back()->with('success', 'Partición agregada.');
    }

    // Actualizar Partición (NUEVO)
    public function updatePartition(Request $request, $id)
    {
        $partition = AlarmPartition::with('account')->findOrFail($id);
        $request->validate(['name' => 'required|string|max:100']);
        
        $oldName = $partition->name;
        $partition->update(['name' => $request->name]);

        // Auditoría
        $partition->account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'note',
            'content' => "SISTEMA: Se renombró el Área #{$partition->partition_number} de '{$oldName}' a '{$request->name}'."
        ]);

        return back()->with('success', 'Nombre de partición actualizado.');
    }

    public function destroyPartition($id)
    {
        $partition = AlarmPartition::with('account')->findOrFail($id);
        $account = $partition->account;
        
        if($partition->partition_number == 1) {
            return back()->with('error', 'No se puede eliminar la Partición Principal (1).');
        }

        $num = $partition->partition_number;
        $partition->delete();

        // Auditoría
        if($account) {
            $account->logs()->create([
                'user_id' => auth()->id() ?? 1,
                'type' => 'note',
                'content' => "SISTEMA: Se eliminó el Área #{$num}."
            ]);
        }

        return back()->with('success', 'Partición eliminada.');
    }

    /**
     * 6. GESTIÓN DE USUARIOS DEL PANEL
     */
    public function storePanelUser(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        $validated = $request->validate([
            'user_number' => 'required|string|max:10',
            'name' => 'required|string|max:100',
            'role' => 'required|string'
        ]);

        $account->panelUsers()->create($validated);

        // Auditoría
        $account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'note',
            'content' => "SISTEMA: Se creó usuario de panel '{$validated['name']}' (Slot: {$validated['user_number']})."
        ]);

        return back()->with('success', 'Usuario de panel agregado.');
    }

    // Actualizar Usuario de Panel (NUEVO)
    public function updatePanelUser(Request $request, $id)
    {
        $user = PanelUser::with('account')->findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|string',
            'user_number' => 'required|string|max:10'
        ]);

        $user->update($validated);

        // Auditoría
        $user->account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'note',
            'content' => "SISTEMA: Se editaron datos del usuario de panel (Slot: {$validated['user_number']})."
        ]);

        return back()->with('success', 'Usuario actualizado.');
    }

    public function destroyPanelUser($id)
    {
        $user = PanelUser::with('account')->findOrFail($id);
        $account = $user->account;
        $name = $user->name;
        
        $user->delete();

        // Auditoría
        if($account) {
            $account->logs()->create([
                'user_id' => auth()->id() ?? 1,
                'type' => 'note',
                'content' => "SISTEMA: Se eliminó el usuario de panel '{$name}'."
            ]);
        }

        return back()->with('success', 'Usuario de panel eliminado.');
    }

    /**
     * 7. GESTIÓN DE HORARIOS (TEMPORAL Y SEMANAL)
     */
    
    // Guardar Horario Temporal (Excepciones)
    public function storeTempSchedule(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        
        $request->validate([
            'reason' => 'required|string',
            'valid_until' => 'required|date' 
        ]);

        $account->schedules()->create([
            'type' => 'temporary',
            'day_of_week' => 0, // 0 = Comodín para temporal
            'open_time' => $request->open_time,
            'close_time' => $request->close_time,
            'reason' => $request->reason,
            'valid_until' => $request->valid_until
        ]);

        // Auditoría
        $account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'note',
            'content' => "SISTEMA: Se creó un horario temporal por '{$request->reason}' hasta {$request->valid_until}."
        ]);

        return back()->with('success', 'Horario temporal creado.');
    }

    // Guardar Horario Semanal (Lunes a Domingo)
    public function storeWeeklySchedule(Request $request, $id)
    {
        $account = AlarmAccount::findOrFail($id);
        
        // 1. Limpiar horario semanal previo
        $account->schedules()->where('type', 'weekly')->delete();

        // 2. Recorrer los días enviados (1=Lunes a 7=Domingo)
        if ($request->has('days')) {
            foreach ($request->days as $day => $times) {
                if (!empty($times['open']) && !empty($times['close'])) {
                    $account->schedules()->create([
                        'type' => 'weekly',
                        'day_of_week' => $day, 
                        'open_time' => $times['open'],
                        'close_time' => $times['close'],
                        'tolerance_minutes' => 30
                    ]);
                }
            }
        }

        // Auditoría
        $account->logs()->create([
            'user_id' => auth()->id() ?? 1,
            'type' => 'note',
            'content' => "SISTEMA: Se actualizó la configuración del horario semanal."
        ]);

        return back()->with('success', 'Horario semanal actualizado correctamente.');
    }

    // Eliminar cualquier horario
    public function destroySchedule($id)
    {
        $schedule = AccountSchedule::with('account')->findOrFail($id);
        $account = $schedule->account;
        $type = $schedule->type == 'temporary' ? 'Temporal' : 'Semanal';
        
        $schedule->delete();

        // Auditoría
        if($account) {
            $account->logs()->create([
                'user_id' => auth()->id() ?? 1,
                'type' => 'note',
                'content' => "SISTEMA: Se eliminó una regla de horario ({$type})."
            ]);
        }

        return back()->with('success', 'Horario eliminado.');
    }
}