<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::withCount('devices')->orderBy('full_name')->paginate(20);
        return view('admin.drivers.index', compact('drivers'));
    }

    public function create()
    {
        return view('admin.drivers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'license_number' => 'required|string|unique:drivers,license_number',
            'phone' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $request->file('photo')->store('drivers', 'public');
        }

        Driver::create($validated);
        return redirect()->route('admin.drivers.index')->with('success', 'Conductor registrado.');
    }

    public function edit(Driver $driver)
    {
        return view('admin.drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:100',
            'license_number' => 'required|string|unique:drivers,license_number,' . $driver->id,
            'phone' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            if ($driver->photo_path) Storage::disk('public')->delete($driver->photo_path);
            $validated['photo_path'] = $request->file('photo')->store('drivers', 'public');
        }

        $driver->update($validated);
        return redirect()->route('admin.drivers.index')->with('success', 'Conductor actualizado.');
    }

    public function destroy(Driver $driver)
    {
        if ($driver->photo_path) Storage::disk('public')->delete($driver->photo_path);
        $driver->delete();
        return back()->with('success', 'Conductor eliminado.');
    }
}