@extends('frontend.layouts.app')

@section('content')
    <div class="py-16 bg-rose-50/20 flex items-center justify-center min-h-[70vh]">
        <div class="max-w-md w-full px-4">
            
            <div class="bg-white rounded-3xl p-8 border border-slate-100 space-y-6">
                <div class="text-center">
                    <span class="text-rose-600 font-bold text-xs uppercase tracking-widest">LAV ÇİÇEKÇİLİK</span>
                    <h1 class="text-3xl font-extrabold text-slate-900 font-serif mt-2">Şifremi Unuttum</h1>
                    <p class="text-slate-500 text-sm mt-2">E-posta adresinizi girerek şifre sıfırlama bağlantısı talep edin.</p>
                </div>

                @if(session('status'))
                    <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-semibold p-4 rounded-xl">
                        {{ session('status') }}
                    </div>
                @endif

                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs font-semibold p-4 rounded-xl">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="bg-rose-50 border border-rose-100 text-rose-800 text-xs font-semibold p-4 rounded-xl space-y-1">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('customer.password.email') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">E-posta Adresi</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                    </div>

                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3.5 rounded-xl transition duration-300">Sıfırlama Bağlantısı Gönder</button>
                </form>

                <div class="text-center pt-4 border-t border-slate-50">
                    <p class="text-xs text-slate-500 font-medium">Giriş sayfasına geri dön <a href="{{ route('customer.login') }}" class="text-rose-600 hover:underline font-bold">Giriş Yap</a></p>
                </div>
            </div>

        </div>
    </div>
@endsection
