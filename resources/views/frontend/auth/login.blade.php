@extends('frontend.layouts.app')

@section('content')
    <div class="py-16 bg-rose-50/20 min-h-[80vh]">
        <div class="max-w-6xl mx-auto px-4">
            
            <div class="text-center mb-10">
                <span class="text-rose-600 font-bold text-xs uppercase tracking-widest">LAV ÇİÇEKÇİLİK</span>
                <h1 class="text-4xl font-extrabold text-slate-900 font-serif mt-2">Giriş Yapın veya Kayıt Olun</h1>
                <p class="text-slate-500 text-sm mt-2 max-w-md mx-auto">Siparişlerinizi takip etmek, favorilerinizi kaydetmek ve hızlı alışveriş yapmak için giriş yapabilir veya yeni bir hesap oluşturabilirsiniz.</p>
            </div>

            @php
                $googleActive = \App\Models\Setting::where('key', 'google_auth_active')->first()?->value === '1';
            @endphp

            @if($googleActive)
                <div class="max-w-md mx-auto mb-10">
                    <a href="{{ route('customer.auth.google') }}" class="flex items-center justify-center gap-3 w-full bg-white hover:bg-slate-50 text-slate-700 font-bold py-3.5 px-4 border border-slate-200 rounded-2xl transition duration-300 hover:border-slate-300">
                        <svg class="w-5 h-5" viewBox="0 0 24 24">
                            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                        </svg>
                        <span class="text-sm font-semibold">Google ile Giriş Yap / Kayıt Ol</span>
                    </a>
                    <div class="relative flex py-6 items-center">
                        <div class="flex-grow border-t border-slate-200/80"></div>
                        <span class="flex-shrink mx-4 text-slate-400 text-xs font-bold uppercase tracking-wider">Veya e-posta ile devam edin</span>
                        <div class="flex-grow border-t border-slate-200/80"></div>
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="max-w-md mx-auto mb-6 bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-semibold p-4 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="max-w-md mx-auto mb-6 bg-rose-50 border border-rose-100 text-rose-800 text-xs font-semibold p-4 rounded-xl space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                
                <!-- Login Card -->
                <div class="bg-white rounded-3xl p-8 border border-slate-100 space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 font-serif">Giriş Yap</h2>
                        <p class="text-slate-500 text-xs mt-1">Kayıtlı hesabınızla giriş yapın.</p>
                    </div>

                    <form action="{{ route('customer.login') }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label for="login_email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">E-posta Adresi</label>
                            <input type="email" name="email" id="login_email" value="{{ old('email') }}" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                        </div>

                        <div>
                            <label for="login_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Şifre</label>
                            <input type="password" name="password" id="login_password" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                        </div>

                        <div class="flex justify-between items-center">
                            <div class="flex items-center">
                                <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-rose-600 border-slate-300 rounded focus:ring-rose-500">
                                <label for="remember" class="ml-2 text-xs font-semibold text-slate-600 cursor-pointer select-none">Beni Hatırla</label>
                            </div>
                            <a href="{{ route('customer.password.request') }}" class="text-xs font-semibold text-rose-600 hover:underline">Şifremi Unuttum</a>
                        </div>

                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3.5 rounded-xl transition duration-300">Giriş Yap</button>
                    </form>
                </div>

                <!-- Register Card -->
                <div class="bg-white rounded-3xl p-8 border border-slate-100 space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-900 font-serif">Kayıt Ol</h2>
                        <p class="text-slate-500 text-xs mt-1">Hızlıca yeni üyelik oluşturun.</p>
                    </div>

                    <form action="{{ route('customer.register') }}" method="POST" class="space-y-4">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="reg_first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Ad</label>
                                <input type="text" name="first_name" id="reg_first_name" value="{{ old('first_name') }}" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                            </div>
                            <div>
                                <label for="reg_last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Soyad</label>
                                <input type="text" name="last_name" id="reg_last_name" value="{{ old('last_name') }}" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                            </div>
                        </div>

                        <div>
                            <label for="reg_email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">E-posta Adresi</label>
                            <input type="email" name="email" id="reg_email" value="{{ old('email') }}" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                        </div>

                        <div>
                            <label for="reg_phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Telefon Numarası</label>
                            <input type="text" name="phone" id="reg_phone" value="{{ old('phone') }}" placeholder="05xx xxx xx xx" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="reg_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Şifre</label>
                                <input type="password" name="password" id="reg_password" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                            </div>

                            <div>
                                <label for="reg_password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Şifre Tekrarı</label>
                                <input type="password" name="password_confirmation" id="reg_password_confirmation" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                            </div>
                        </div>

                        <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3.5 rounded-xl transition duration-300">Kayıt Ol</button>
                    </form>
                </div>

            </div>
        </div>
    </div>
@endsection
