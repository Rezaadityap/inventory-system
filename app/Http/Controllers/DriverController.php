<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class DriverController extends Controller
{
    public function index()
    {
        $drivers = Driver::with('user')->get();

        return view('driver.index', compact('drivers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',

            'phone_number' => 'required|string|max:13',
            'driver_license' => 'required|string|max:16',
            'available_status' => ['required', Rule::in(['Tersedia', 'Sibuk', 'Libur'])],
        ]);

        $photo = null;
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo')->store('driver_images', 'public');
        }

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'photo' => $photo,
        ]);

        $user->setRole(Role::firstOrCreate(['name' => 'pengemudi', 'guard_name' => 'web']));

        Driver::create([
            'user_id' => $user->id,
            'phone_number' => $data['phone_number'],
            'driver_license' => $data['driver_license'],
            'available_status' => $data['available_status'],
        ]);

        return redirect()->route('drivers.index')->with('success', 'Pengemudi berhasil ditambahkan.');
    }

    public function update(Request $request, Driver $driver)
    {
        $user = $driver->user;

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'photo' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',

            'phone_number' => 'required|string|max:13',
            'driver_license' => 'required|string|max:16',
            'available_status' => ['required', Rule::in(['Tersedia', 'Sibuk', 'Libur'])],
        ]);

        $userData = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
        ];

        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $userData['photo'] = $request->file('photo')->store('driver_images', 'public');
        }

        $user->update($userData);

        $driver->update([
            'phone_number' => $data['phone_number'],
            'driver_license' => $data['driver_license'],
            'available_status' => $data['available_status'],
        ]);

        return redirect()->route('drivers.index')->with('success', 'Pengemudi berhasil diperbarui.');
    }

    public function destroy(Driver $driver)
    {
        $user = $driver->user;

        if ($user && $user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        $driver->delete();
        $user?->delete();

        return redirect()->route('drivers.index')->with('success', 'Pengemudi berhasil dihapus.');
    }
}
