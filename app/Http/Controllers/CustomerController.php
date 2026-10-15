<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::with('user');
        $users = User::withRole('pelanggan')->get();

        return view('customer.index', compact('customers', 'users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255|unique:customers,customer_name',
            'email' => 'nullable|email|unique:customers,email',
            'phone_number' => 'required|min:10|numeric',
            'company_photo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'address' => 'required|string',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($request->hasFile('company_photo')) {
            $data['company_photo'] = $request->file('company_photo')->store('company_photos', 'public');
        }

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil ditambahkan');
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'customer_name' => 'required|string|max:255|unique:customers,customer_name,' . $customer->id,
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'phone_number' => 'required|min:11|numeric',
            'company_photo' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'address' => 'required|string',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($request->hasFile('company_photo')) {
            if ($customer->company_photo) {
                Storage::disk('public')->delete($customer->company_photo);
            }

            $data['company_photo'] = $request->file('company_photo')->store('company_photos', 'public');
        }

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Customer berhasil diedit.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->company_photo) {
            Storage::disk('public')->delete($customer->company_photo);
        }

        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer berhasil dihapus.');
    }
}
