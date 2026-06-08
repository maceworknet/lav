<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules;
use Laravel\Socialite\Facades\Socialite;

class CustomerAuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        return view('frontend.auth.login');
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('customer')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Link guest favorites and cart if exist
            $this->mergeGuestFavoritesToCustomer();
            $this->mergeGuestCartToCustomer();

            return redirect()->intended(route('customer.account'))
                ->with('success', 'Başarıyla giriş yaptınız.');
        }

        return back()->withErrors([
            'email' => 'Girdiğiniz bilgiler kayıtlarımızla eşleşmiyor.',
        ])->onlyInput('email');
    }

    /**
     * Show the registration form.
     */
    public function showRegisterForm()
    {
        return view('frontend.auth.register');
    }

    /**
     * Handle registration request.
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $customer = Customer::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'is_guest' => false,
        ]);

        Auth::guard('customer')->login($customer);

        // Link guest favorites and cart if exist
        $this->mergeGuestFavoritesToCustomer();
        $this->mergeGuestCartToCustomer();

        return redirect()->route('customer.account')
            ->with('success', 'Hesabınız başarıyla oluşturuldu.');
    }

    /**
     * Logout customer.
     */
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Başarıyla çıkış yaptınız.');
    }

    /**
     * Helper to merge guest favorites into customer favorites.
     */
    private function mergeGuestFavoritesToCustomer()
    {
        if (session()->has('guest_token')) {
            $guestToken = session('guest_token');
            $customerId = Auth::guard('customer')->id();

            $guestFavorites = \App\Models\Favorite::where('guest_token', $guestToken)->get();

            foreach ($guestFavorites as $fav) {
                $exists = \App\Models\Favorite::where('customer_id', $customerId)
                    ->where('product_id', $fav->product_id)
                    ->exists();

                if (!$exists) {
                    $fav->update([
                        'customer_id' => $customerId,
                        'guest_token' => null
                    ]);
                } else {
                    $fav->delete();
                }
            }
        }
    }

    /**
     * Helper to merge guest cart into customer cart.
     */
    private function mergeGuestCartToCustomer()
    {
        if (session()->has('guest_token')) {
            $guestToken = session('guest_token');
            $guestCart = \App\Models\Cart::where('guest_token', $guestToken)->first();
            $customerCart = \App\Models\Cart::where('customer_id', Auth::guard('customer')->id())->first();

            if ($guestCart && $guestCart->items->count() > 0) {
                if (!$customerCart) {
                    // Just transfer the guest cart to customer
                    $guestCart->update([
                        'customer_id' => Auth::guard('customer')->id(),
                        'guest_token' => null
                    ]);
                } else {
                    // Merge items
                    foreach ($guestCart->items as $guestItem) {
                        $existingItem = $customerCart->items()
                            ->where('product_id', $guestItem->product_id)
                            ->where('options', json_encode($guestItem->options))
                            ->first();

                        if ($existingItem) {
                            $existingItem->increment('quantity', $guestItem->quantity);
                        } else {
                            $guestItem->update(['cart_id' => $customerCart->id]);
                        }
                    }
                    $guestCart->delete();
                }
            }
            session()->forget('guest_token');
        }
    }

    /**
     * Redirect the customer to the Google authentication page.
     */
    public function redirectToGoogle()
    {
        $active = \App\Models\Setting::where('key', 'google_auth_active')->first()?->value;
        if ($active !== '1') {
            return redirect()->route('customer.login')->withErrors(['email' => 'Google ile giriş şu anda aktif değil.']);
        }

        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the customer information from Google.
     */
    public function handleGoogleCallback()
    {
        $active = \App\Models\Setting::where('key', 'google_auth_active')->first()?->value;
        if ($active !== '1') {
            return redirect()->route('customer.login')->withErrors(['email' => 'Google ile giriş şu anda aktif değil.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('customer.login')->withErrors(['email' => 'Google Giriş işlemi iptal edildi veya bir hata oluştu: ' . $e->getMessage()]);
        }

        // Split name into first_name and last_name
        $fullName = $googleUser->getName();
        $nameParts = explode(' ', $fullName);
        $lastName = array_pop($nameParts);
        $firstName = implode(' ', $nameParts);
        if (empty($firstName)) {
            $firstName = $lastName;
            $lastName = '';
        }

        // Try to find customer by google_id
        $customer = Customer::where('google_id', $googleUser->getId())->first();

        if (!$customer) {
            // Check if email already exists
            $customer = Customer::where('email', $googleUser->getEmail())->first();

            if ($customer) {
                // Link Google account to existing customer
                $customer->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } else {
                // Create a new customer
                $customer = Customer::create([
                    'first_name' => $firstName ?: 'Google',
                    'last_name' => $lastName ?: 'Kullanıcısı',
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'is_guest' => false,
                ]);
            }
        } else {
            // Update avatar if changed
            $customer->update([
                'avatar' => $googleUser->getAvatar(),
            ]);
        }

        Auth::guard('customer')->login($customer);

        // Link guest favorites and cart if exist
        $this->mergeGuestFavoritesToCustomer();
        $this->mergeGuestCartToCustomer();

        return redirect()->route('customer.account')
            ->with('success', 'Google ile başarıyla giriş yaptınız.');
    }

    /**
     * Display the form to request a password reset link.
     */
    public function showLinkRequestForm()
    {
        return view('frontend.auth.passwords.email');
    }

    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::broker('customers')->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Şifre sıfırlama bağlantısı e-posta adresinize gönderildi.')
            : back()->withErrors(['email' => 'Bu e-posta adresiyle eşleşen bir üye bulunamadı.']);
    }

    /**
     * Display the password reset view for the given token.
     */
    public function showResetForm(Request $request, $token = null)
    {
        return view('frontend.auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    /**
     * Reset the given user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::broker('customers')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
                
                Auth::guard('customer')->login($user);
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('customer.account')->with('success', 'Şifreniz başarıyla sıfırlandı ve giriş yapıldı.')
            : back()->withErrors(['email' => 'Şifre sıfırlama işlemi başarısız oldu. Lütfen tekrar deneyin.']);
    }
}
