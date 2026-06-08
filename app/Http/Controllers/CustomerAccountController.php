<?php

namespace App\Http\Controllers;

use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class CustomerAccountController extends Controller
{
    /**
     * Show customer account dashboard.
     */
    public function index(Request $request)
    {
        $customer = Auth::guard('customer')->user();
        $orders = $customer->orders()
            ->with(['items.product.images', 'statusHistories'])
            ->latest()
            ->get();
        $addresses = $customer->addresses;
        
        $activeTab = $request->input('tab', 'overview');

        return view('frontend.account.dashboard', compact('customer', 'orders', 'addresses', 'activeTab'));
    }

    /**
     * Update customer profile info.
     */
    public function updateProfile(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $customer->update($request->only('first_name', 'last_name', 'email', 'phone'));

        return redirect()->route('customer.account', ['tab' => 'profile'])
            ->with('success', 'Profil bilgileriniz başarıyla güncellendi.');
    }

    /**
     * Update customer password.
     */
    public function updatePassword(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'current_password' => ['required', 'current_password:customer'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $customer->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('customer.account', ['tab' => 'profile'])
            ->with('success', 'Şifreniz başarıyla güncellendi.');
    }

    /**
     * Add new address.
     */
    public function addAddress(Request $request)
    {
        $customer = Auth::guard('customer')->user();

        $request->validate([
            'title' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:100'],
            'address_line' => ['required', 'string'],
            'city' => ['required', 'string', 'max:50'],
            'district' => ['required', 'string', 'max:50'],
            'neighborhood' => ['required', 'string', 'max:100'],
        ]);

        $customer->addresses()->create($request->all());

        return redirect()->route('customer.account', ['tab' => 'addresses'])
            ->with('success', 'Yeni adresiniz başarıyla eklendi.');
    }

    /**
     * Update customer address.
     */
    public function updateAddress(Request $request, $id)
    {
        $customer = Auth::guard('customer')->user();
        $address = $customer->addresses()->findOrFail($id);

        $request->validate([
            'title' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['required', 'string', 'max:20'],
            'company' => ['nullable', 'string', 'max:100'],
            'address_line' => ['required', 'string'],
            'city' => ['required', 'string', 'max:50'],
            'district' => ['required', 'string', 'max:50'],
            'neighborhood' => ['required', 'string', 'max:100'],
        ]);

        $address->update($request->all());

        return redirect()->route('customer.account', ['tab' => 'addresses'])
            ->with('success', 'Adresiniz başarıyla güncellendi.');
    }

    /**
     * Delete an address.
     */
    public function deleteAddress($id)
    {
        $customer = Auth::guard('customer')->user();
        $address = $customer->addresses()->findOrFail($id);
        $address->delete();

        return redirect()->route('customer.account', ['tab' => 'addresses'])
            ->with('success', 'Adres başarıyla silindi.');
    }
}
