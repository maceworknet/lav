@extends('frontend.layouts.app')

@section('content')
    @php
        $phone = \App\Models\Setting::where('key', 'site_phone')->value('value') ?? '';
        $whatsapp = \App\Models\Setting::where('key', 'site_whatsapp')->value('value') ?? '';
        $email = \App\Models\Setting::where('key', 'site_email')->value('value') ?? '';
        $address = \App\Models\Setting::where('key', 'site_address')->value('value') ?? '';
        $hours = \App\Models\Setting::where('key', 'working_hours')->value('value') ?? '';
    @endphp

    <div class="py-16 bg-rose-50/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-rose-600 font-semibold text-xs tracking-widest uppercase">BİZE ULAŞIN</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 font-serif mt-2">İletişim Bilgilerimiz</h1>
                <p class="mt-4 text-slate-500 text-sm sm:text-base">Diyarbakır genelinde sipariş ve teslimat süreçleriniz hakkında bilgi almak veya destek talep etmek için bizimle iletişime geçin.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-stretch">
                
                <!-- Left Info (5 Cols) -->
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm space-y-6 flex flex-col justify-between h-full">
                        <div class="space-y-6">
                            <h2 class="text-xl font-bold text-slate-900 font-serif border-b border-slate-50 pb-3">Atölye İletişim Detayları</h2>
                            
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase">Adres</h3>
                                    <p class="text-sm font-semibold text-slate-700 mt-1 leading-relaxed">{{ $address }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-2.824-1.802-5.122-4.1-6.924-6.924l1.293-.97c.362-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H3.75A2.25 2.25 0 0 0 1.5 3.75v3Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase">Telefon</h3>
                                    <p class="text-sm font-bold text-slate-700 mt-1">{{ $phone }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25H4.5a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase">E-Posta</h3>
                                    <p class="text-sm font-semibold text-slate-700 mt-1">{{ $email }}</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-rose-50 text-rose-600 rounded-xl shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xs font-bold text-slate-400 uppercase">Çalışma Saatleri</h3>
                                    <p class="text-sm font-semibold text-slate-700 mt-1">{{ $hours }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- WhatsApp floating trigger -->
                        @if($whatsapp)
                            @php
                                $whatsappUrl = app(\App\Services\NotificationService::class)->generateWhatsAppLink($whatsapp, 'Merhaba, online mağazanız üzerinden destek almak istiyorum.');
                            @endphp
                            <div class="pt-6 border-t border-slate-50 mt-6">
                                <a href="{{ $whatsappUrl }}" target="_blank" class="flex items-center justify-center gap-3 w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-4 rounded-2xl shadow-lg hover:shadow-emerald-100 transition">
                                    WhatsApp ile Canlı Sohbet
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Right Map / Visual (7 Cols) -->
                <div class="lg:col-span-7">
                    <div class="bg-white rounded-3xl p-6 border border-slate-100 shadow-sm h-full flex flex-col justify-between min-h-[450px]">
                        <div class="w-full h-full min-h-[380px] flex-grow relative overflow-hidden rounded-2xl border border-slate-100">
                            <iframe class="absolute inset-0 w-full h-full border-0" 
                                    src="https://maps.google.com/maps?q={{ urlencode($address) }}&t=&z=15&ie=UTF8&iwloc=&output=embed" 
                                    allowfullscreen 
                                    loading="lazy" 
                                    referrerpolicy="no-referrer-when-downgrade">
                            </iframe>
                        </div>
                        <div class="mt-4 flex items-center justify-between gap-4">
                            <div class="text-left">
                                <h3 class="text-sm font-bold text-slate-800">Atölye Yol Tarifi</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Diclekent Bulvarı, Kayapınar / Diyarbakır</p>
                            </div>
                            <a href="https://maps.google.com/?q={{ urlencode($address) }}" target="_blank" class="inline-flex items-center bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition">
                                Google Haritalar'da Aç
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
@endsection
