<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\AccountRequestNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AccountRequestController extends Controller
{
    public function create(): View
    {
        return view('auth.request-user');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'email' => is_string($request->input('email')) ? strtolower(trim($request->input('email'))) : $request->input('email'),
        ]);

        $data = $request->validate([
            // Data customer
            'customer_name' => ['required', 'string', 'max:255', 'unique:customers,customer_name'],
            'phone_number' => ['required', 'digits_between:10,13'],
            'address' => ['required', 'string'],
            'company_photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],

            // Data akun (user)
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'lowercase', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        DB::transaction(function () use ($request, $data) {
            $photo = $request->file('company_photo')?->store('company_photos', 'public');

            $user = User::create([
                'name' => $data['name'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'is_active' => false,
            ]);

            $user->setRole(Role::firstOrCreate([
                'name' => 'pelanggan',
                'guard_name' => 'web',
            ]));

            $customer = Customer::create([
                'customer_name' => $data['customer_name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'],
                'company_photo' => $photo,
                'address' => $data['address'],
                'user_id' => $user->id,
            ]);

            Notification::send(
                User::withRole('admin')->get(),
                new AccountRequestNotification($user, $customer),
            );
        });

        return redirect()->route('login')->with(
            'status',
            'Permintaan akun kamu sudah dikirim. Admin akan meninjau dan mengaktifkannya.',
        );
    }
}
