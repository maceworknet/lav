@extends('frontend.layouts.app')

@section('content')
    <div class="py-16 bg-rose-50/20">
        <div class="max-w-3xl mx-auto px-4">
            
            <h1 class="text-3xl font-extrabold text-slate-900 font-serif mb-8 text-center">Sipariş Takip</h1>

            <!-- Lookup Form Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4 mb-10">
                <form action="{{ route('tracking') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                    <input type="text" name="order_number" value="{{ $orderNumber ?? '' }}" placeholder="LAV-YYYYMMDD-XXXXX" class="flex-grow px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500 font-semibold tracking-wider text-center uppercase" required>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-8 py-3 rounded-xl transition shrink-0">Sorgula</button>
                </form>
            </div>

            @if($order)
                @php
                    // Map statuses to Turkish labels and styles
                    $statusMap = [
                        'pending_payment' => ['title' => 'Ödeme Bekleniyor', 'color' => 'bg-amber-100 text-amber-800 border-amber-200'],
                        'payment_failed' => ['title' => 'Ödeme Başarısız', 'color' => 'bg-rose-100 text-rose-800 border-rose-200'],
                        'paid' => ['title' => 'Ödeme Yapıldı', 'color' => 'bg-sky-100 text-sky-800 border-sky-200'],
                        'preparing' => ['title' => 'Hazırlanıyor', 'color' => 'bg-violet-100 text-violet-800 border-violet-200'],
                        'approval_waiting' => ['title' => 'Onay Bekliyor', 'color' => 'bg-indigo-100 text-indigo-800 border-indigo-200'],
                        'approved' => ['title' => 'Onaylandı', 'color' => 'bg-teal-100 text-teal-800 border-teal-200'],
                        'assigned_to_courier' => ['title' => 'Kuryeye Atandı', 'color' => 'bg-blue-100 text-blue-800 border-blue-200'],
                        'on_delivery' => ['title' => 'Dağıtımda', 'color' => 'bg-cyan-100 text-cyan-800 border-cyan-200'],
                        'delivered' => ['title' => 'Teslim Edildi', 'color' => 'bg-emerald-100 text-emerald-800 border-emerald-200'],
                        'cancelled' => ['title' => 'İptal Edildi', 'color' => 'bg-slate-100 text-slate-500 border-slate-200'],
                        'refunded' => ['title' => 'İade Edildi', 'color' => 'bg-pink-100 text-pink-800 border-pink-200'],
                    ];
                    $currentStatus = $statusMap[$order->status] ?? ['title' => $order->status, 'color' => 'bg-slate-50 text-slate-600 border-slate-150'];
                @endphp

                <!-- Order Detail Card -->
                <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center border-b border-slate-50 pb-4 gap-4">
                        <div>
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Sipariş Kodu</span>
                            <h2 class="text-xl font-extrabold text-slate-800 tracking-wider uppercase mt-0.5">{{ $order->order_number }}</h2>
                        </div>
                        <span class="inline-flex px-4 py-1.5 rounded-full text-xs font-black border uppercase tracking-wider {{ $currentStatus['color'] }}">
                            {{ $currentStatus['title'] }}
                        </span>
                    </div>

                    <!-- Items Summary -->
                    <div class="space-y-3">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Sipariş Detayları</h3>
                        <div class="divide-y divide-slate-50">
                            @foreach($order->items as $item)
                                <div class="py-3 flex justify-between gap-4 text-sm items-center">
                                    <div>
                                        <span class="font-bold text-slate-700">{{ $item->product_name }}</span>
                                        <span class="text-slate-400 font-medium ml-1">x{{ $item->quantity }}</span>
                                        @if(is_array($item->options) && count($item->options) > 0)
                                            <div class="mt-1 flex gap-1">
                                                @foreach($item->options as $opt)
                                                    <span class="text-[9px] bg-slate-50 text-slate-500 border border-slate-200 px-2 py-0.5 rounded-full font-bold">
                                                        {{ $opt['label'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                    <span class="font-bold text-slate-800">₺{{ number_format($item->total, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Delivery Summary -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 border-t border-slate-50 pt-5 text-sm">
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-400 uppercase block tracking-wider">Alıcı Bilgisi</span>
                            <p class="font-bold text-slate-700">{{ $order->recipient_name }}</p>
                            <p class="text-xs text-slate-500">{{ $order->recipient_phone }}</p>
                            <p class="text-xs text-slate-500 mt-1 max-w-xs leading-relaxed">
                                {{ $order->recipient_address }}, {{ $order->recipient_neighborhood }}, {{ $order->recipient_district }} / Diyarbakır
                            </p>
                        </div>
                        <div class="space-y-1">
                            <span class="text-xs font-bold text-slate-400 uppercase block tracking-wider">Teslimat Zamanı</span>
                            <p class="font-bold text-slate-700">{{ \Carbon\Carbon::parse($order->delivery_date)->format('d.m.Y') }}</p>
                            <p class="text-xs text-slate-500">{{ $order->delivery_slot }}</p>
                        </div>
                    </div>

                    <!-- Visual Timeline -->
                    <div class="border-t border-slate-50 pt-8">
                        <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-6">Sipariş Geçmişi</h3>
                        <div class="relative border-l border-rose-100 ml-4 space-y-6">
                            @foreach($order->statusHistories as $history)
                                @php
                                    $histMap = $statusMap[$history->status] ?? ['title' => $history->status, 'color' => ''];
                                @endphp
                                <div class="relative pl-6">
                                    <!-- Timeline Dot -->
                                    <div class="absolute -left-1.5 top-1.5 w-3 h-3 rounded-full bg-rose-500 border border-white"></div>
                                    
                                    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-1">
                                        <h4 class="font-bold text-slate-800 text-sm">
                                            {{ $histMap['title'] }}
                                        </h4>
                                        <span class="text-[10px] text-slate-400 font-semibold">{{ $history->created_at->timezone('Europe/Istanbul')->format('d M Y - H:i') }}</span>
                                    </div>
                                    @if($history->note)
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $history->note }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
            @elseif($orderNumber)
                <!-- Not Found Message -->
                <div class="py-12 bg-white rounded-2xl border border-slate-100 shadow-sm text-center p-6">
                    <p class="text-rose-600 font-bold text-sm">Sipariş bulunamadı.</p>
                    <p class="text-slate-500 text-xs mt-1">Girdiğiniz sipariş kodunu kontrol edip lütfen tekrar deneyiniz.</p>
                </div>
            @endif

        </div>
    </div>
@endsection
