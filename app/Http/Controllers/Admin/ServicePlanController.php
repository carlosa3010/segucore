<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePlan;
use Illuminate\Http\Request;

class ServicePlanController extends Controller
{
    public function index()
    {
        $plans = ServicePlan::all();
        return view('admin.config.plans.index', compact('plans'));
    }

    public function create()
    {
        return view('admin.config.plans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric|min:0', // Precio Base
            'gps_price' => 'required|numeric|min:0', // Tasa GPS
            'alarm_price' => 'required|numeric|min:0', // Tasa Alarma
        ]);

        ServicePlan::create([
            'name' => $request->name,
            'price' => $request->price,
            'billing_cycle' => $request->billing_cycle ?? 'monthly',
            'description' => $request->description,
            'features' => [
                'gps_price' => $request->gps_price,
                'alarm_price' => $request->alarm_price,
            ],
            'is_active' => true
        ]);

        return redirect()->route('admin.config.plans.index')->with('success', 'Plan creado correctamente.');
    }

    public function edit(ServicePlan $plan)
    {
        return view('admin.config.plans.edit', compact('plan'));
    }

    public function update(Request $request, ServicePlan $plan)
    {
        $plan->update([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'features' => [
                'gps_price' => $request->gps_price,
                'alarm_price' => $request->alarm_price,
            ],
        ]);
        return redirect()->route('admin.config.plans.index')->with('success', 'Plan actualizado.');
    }
}