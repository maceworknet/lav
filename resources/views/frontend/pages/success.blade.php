@extends('frontend.layouts.app')

@section('content')
    <div class="py-24 bg-white">
        <div class="max-w-2xl mx-auto px-4 text-center">
            
            <!-- Checkmark Icon -->
            <div class="inline-flex p-5 bg-emerald-50 text-emerald-600 rounded-full mb-8 shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-16 h-16">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                </svg>
            </div>

            <h1 class="text-4xl font-extrabold text-slate-900 font-serif leading-tight">Siparişiniz Alındı!</h1>
            <p class="text-slate-500 mt-3 text-base leading-relaxed">Ödemeniz başarıyla tahsil edilmiş ve siparişiniz hazırlık sırasına alınmıştır.</p>

            <!-- Order Specs Box -->
            <div class="mt-10 bg-slate-50 border border-slate-100 rounded-3xl p-6 text-left space-y-4">
                <div class="flex justify-between items-baseline border-b border-slate-200/60 pb-3">
                    <span class="text-xs font-bold text-slate-400 uppercase">Sipariş Kodu</span>
                    <span class="text-base font-black text-slate-800 tracking-wider">{{ $order->order_number }}</span>
                </div>
                
                <div class="flex justify-between items-baseline">
                    <span class="text-xs font-bold text-slate-400 uppercase">Alıcı</span>
                    <span class="text-sm font-semibold text-slate-700">{{ $order->recipient_name }}</span>
                </div>

                <div class="flex justify-between items-baseline">
                    <span class="text-xs font-bold text-slate-400 uppercase">Teslimat Tarihi</span>
                    <span class="text-sm font-semibold text-slate-700">
                        {{ \Carbon\Carbon::parse($order->delivery_date)->format('d.m.Y') }} ({{ $order->delivery_slot }})
                    </span>
                </div>

                <div class="flex justify-between items-start">
                    <span class="text-xs font-bold text-slate-400 uppercase mt-0.5">Adres</span>
                    <span class="text-xs font-medium text-slate-600 max-w-[250px] text-right">
                        {{ $order->recipient_address }}, {{ $order->recipient_neighborhood }}, {{ $order->recipient_district }} / Diyarbakır
                    </span>
                </div>
                
                @if($order->card_note)
                    <div class="border-t border-slate-200/60 pt-4 space-y-1">
                        <span class="text-xs font-bold text-slate-400 uppercase">Kart Notu</span>
                        <p class="text-xs text-slate-500 italic bg-rose-50/30 p-3 rounded-lg border border-rose-100/30">
                            "{{ $order->card_note }}" @if($order->card_note_signature) — <strong>{{ $order->card_note_signature }}</strong> @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="mt-12 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('tracking', ['order_number' => $order->order_number]) }}" class="bg-slate-900 text-white font-bold py-4 px-8 rounded-xl hover:bg-slate-800 shadow-sm transition">
                    Siparişi Takip Et
                </a>
                <a href="/" class="bg-rose-50 text-rose-600 font-bold py-4 px-8 rounded-xl hover:bg-rose-100 transition">
                    Alışverişe Devam Et
                </a>
            </div>

            <!-- Help message -->
            <p class="mt-8 text-xs text-slate-400">
                Herhangi bir sorunuz için destek hattımız üzerinden bizimle iletişime geçebilirsiniz.
            </p>
        </div>
    </div>
@endsection
