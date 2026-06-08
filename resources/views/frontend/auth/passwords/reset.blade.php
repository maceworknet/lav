@extends('frontend.layouts.app')

@section('content')
    <div class="py-16 bg-rose-50/20 flex items-center justify-center min-h-[70vh]">
        <div class="max-w-md w-full px-4">
            
            <div class="bg-white rounded-3xl p-8 border border-slate-100 space-y-6">
                <div class="text-center">
                    <span class="text-rose-600 font-bold text-xs uppercase tracking-widest">LAV ÇİÇEKÇİLİK</span>
                    <h1 class="text-3xl font-extrabold text-slate-900 font-serif mt-2">Yeni Şifre Belirle</h1>
                    <p class="text-slate-500 text-sm mt-2">Lütfen hesabınız için yeni şifrenizi girin.</p>
                </div>

                @if($errors->any())
                    <div class="bg-rose-50 border border-rose-100 text-rose-800 text-xs font-semibold p-4 rounded-xl space-y-1">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form action="{{ route('customer.password.update') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">E-posta Adresi</label>
                        <input type="email" name="email" id="email" value="{{ $email ?? old('email') }}" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Yeni Şifre</label>
                        <input type="password" name="password" id="password" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Yeni Şifre Tekrarı</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                    </div>

                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-3.5 rounded-xl transition duration-300">Şifremi Güncelle</button>
                </form>
            </div>

        </div>
    </div>
@endsection
