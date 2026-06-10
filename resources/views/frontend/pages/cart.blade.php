@extends('frontend.layouts.app')

@section('content')
    <div class="py-12 bg-rose-50/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <h1 class="text-3xl font-extrabold text-slate-900 font-serif mb-10">Alışveriş Sepetim</h1>

            <!-- Notification Messages -->
            @if(session('success'))
                <div class="mb-8 p-4 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-100 text-sm font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-8 p-4 bg-rose-50 text-rose-800 rounded-xl border border-rose-100 text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            @if($cart->items->count() > 0)
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                    
                    <!-- Left: Cart Items (8 Cols) -->
                    <div class="lg:col-span-8 space-y-6">
                        @foreach($cart->items as $item)
                            @php
                                $unitPrice = (float)($item->product->discount_price ?? $item->product->price);
                                $optionsModifier = 0.00;
                                if(is_array($item->options)) {
                                    foreach($item->options as $opt) {
                                        $optionsModifier += (float)($opt['price_modifier'] ?? 0);
                                    }
                                }
                                $itemPrice = $unitPrice + $optionsModifier;
                                
                                $giftsTotal = 0.00;
                                if ($item->extraGifts) {
                                    foreach ($item->extraGifts as $gift) {
                                        $giftsTotal += (float)$gift->price_snapshot * $gift->quantity;
                                    }
                                }
                                
                                $itemTotal = ($itemPrice * $item->quantity) + $giftsTotal;
                            @endphp

                            <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6 hover:shadow-md transition">
                                
                                <!-- Product details -->
                                <div class="flex gap-5 items-center">
                                    <div class="w-20 aspect-square rounded-xl bg-gradient-to-tr from-rose-50 to-rose-100 shrink-0 border border-rose-100 flex items-center justify-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-rose-300">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" />
                                        </svg>
                                    </div>
                                    <div>
                                        <a href="{{ route('product', $item->product->slug) }}" class="font-serif text-base font-bold text-slate-800 hover:text-rose-600 transition">{{ $item->product->name }}</a>
                                        <p class="text-[10px] text-slate-400 font-bold uppercase mt-1">SKU: {{ $item->product->sku }}</p>
                                        
                                        <!-- Selected Options -->
                                        @if(is_array($item->options) && count($item->options) > 0)
                                            <div class="mt-2 flex flex-wrap gap-1.5">
                                                @foreach($item->options as $opt)
                                                    <span class="inline-flex items-center text-[10px] font-bold bg-rose-50 text-rose-600 px-2 py-0.5 rounded-full border border-rose-100/50">
                                                        {{ $opt['label'] }} @if(($opt['price_modifier'] ?? 0) > 0) (+ ₺{{ number_format($opt['price_modifier'], 2) }}) @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Selected Extra Gifts -->
                                        @if($item->extraGifts && $item->extraGifts->count() > 0)
                                            <div class="mt-2.5 space-y-1">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase block tracking-wider">Ekstra Hediyeler:</span>
                                                <div class="flex flex-wrap gap-1.5">
                                                    @foreach($item->extraGifts as $gift)
                                                        <span class="inline-flex items-center text-[10px] font-bold bg-rose-50/50 text-rose-700 px-2 py-1 rounded-lg border border-rose-100">
                                                            🎁 {{ $gift->name_snapshot }} (+ ₺{{ number_format($gift->price_snapshot, 2) }})
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Card Note display -->
                                        @if($item->card_note)
                                            <p class="mt-3 text-xs text-slate-500 italic bg-rose-50/20 border-l-2 border-rose-400 pl-3">
                                                <strong>Kart Notu:</strong> "{{ $item->card_note }}"
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Quantity, pricing, remove -->
                                <div class="flex items-center justify-between sm:justify-end gap-6 w-full sm:w-auto border-t sm:border-t-0 pt-4 sm:pt-0 border-slate-50">
                                    
                                    <!-- Quantity picker form -->
                                    <form action="{{ route('cart.update', $item->id) }}" method="POST" class="flex items-center border border-slate-200 rounded-xl p-1 bg-slate-50/50 shrink-0">
                                        @csrf
                                        <button type="submit" name="quantity" value="{{ $item->quantity - 1 }}" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-rose-600 font-bold">-</button>
                                        <span class="w-8 text-center text-xs font-black text-slate-700">{{ $item->quantity }}</span>
                                        <button type="submit" name="quantity" value="{{ $item->quantity + 1 }}" class="w-8 h-8 flex items-center justify-center text-slate-500 hover:text-rose-600 font-bold">+</button>
                                    </form>

                                    <!-- Price -->
                                    <div class="text-right min-w-[90px]">
                                        <span class="text-xs text-slate-400 block">Toplam</span>
                                        <span class="text-base font-black text-slate-800">₺{{ number_format($itemTotal, 2) }}</span>
                                    </div>

                                    <!-- Remove button -->
                                    <form action="{{ route('cart.remove', $item->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="p-2.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>

                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Right: Summary & Coupon (4 Cols) -->
                    <div class="lg:col-span-4 space-y-6">
                        
                        <!-- Order Summary Card -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
                            <h3 class="font-serif text-lg font-bold text-slate-800">Sipariş Özeti</h3>
                            
                            <div class="space-y-3 text-sm text-slate-600">
                                <div class="flex justify-between">
                                    <span>Ara Toplam</span>
                                    <span class="font-bold text-slate-800">₺{{ number_format($totals['subtotal'], 2) }}</span>
                                </div>
                                @if($totals['discount_amount'] > 0)
                                    <div class="flex justify-between text-rose-600 font-bold">
                                        <span>İndirim (Kupon)</span>
                                        <span>- ₺{{ number_format($totals['discount_amount'], 2) }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between">
                                    <span>Teslimat Ücreti</span>
                                    @if(isset($totals['delivery_fee_details']) && $totals['delivery_fee_details']['base_fee'] > 0)
                                        <span class="font-bold text-slate-800">
                                            @if($totals['delivery_fee'] == 0)
                                                <span class="text-emerald-600">Ücretsiz</span>
                                            @else
                                                ₺{{ number_format($totals['delivery_fee'], 2) }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="font-medium text-slate-400 italic">Ödeme adımında hesaplanır</span>
                                    @endif
                                </div>
                                
                                @if(isset($totals['delivery_fee_details']) && $totals['delivery_fee_details']['customer_message'])
                                    <div class="p-3 bg-rose-50/50 rounded-xl border border-rose-100/60 text-xs text-rose-700 font-bold flex items-start gap-2 mt-2 leading-relaxed">
                                        <span class="shrink-0 text-sm">🔔</span>
                                        <span>{{ $totals['delivery_fee_details']['customer_message'] }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="border-t border-slate-100 pt-5 flex justify-between items-baseline">
                                <span class="text-base font-bold text-slate-800 font-serif">Toplam</span>
                                <span class="text-2xl font-black text-rose-600 font-serif">₺{{ number_format($totals['total'], 2) }}</span>
                            </div>

                            <div class="pt-2">
                                <a href="{{ route('checkout.index') }}" class="block text-center w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-rose-100 transition duration-300">
                                    Ödeme Sayfasına Git
                                </a>
                            </div>
                        </div>

                        <!-- Coupon Code Card -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
                            <h3 class="font-serif text-base font-bold text-slate-800">Kupon Kodu</h3>
                            
                            @if($totals['coupon_code'])
                                <div class="flex items-center justify-between bg-rose-50 text-rose-600 px-4 py-3 rounded-xl border border-rose-100">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                                        </svg>
                                        <span class="text-xs font-black uppercase tracking-wider">Aktif: {{ $totals['coupon_code'] }}</span>
                                    </div>
                                    <form action="{{ route('cart.coupon.remove') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold underline hover:text-rose-800">Kaldır</button>
                                    </form>
                                </div>
                            @else
                                <form action="{{ route('cart.coupon.apply') }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="code" placeholder="Kupon kodunuz" class="flex-grow px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition uppercase font-bold" required>
                                    <button type="submit" class="bg-slate-900 text-white font-bold px-5 rounded-xl hover:bg-slate-800 transition">Uygula</button>
                                </form>
                            @endif
                        </div>

                    </div>

                </div>
            @else
                <!-- Empty Cart -->
                <div class="py-24 text-center max-w-md mx-auto bg-white rounded-3xl border border-slate-100 shadow-sm p-12">
                    <div class="inline-flex p-6 bg-rose-50 text-rose-600 rounded-full mb-6">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900 font-serif">Sepetiniz Boş Görünüyor</h2>
                    <p class="text-slate-500 text-sm mt-3 leading-relaxed">Hemen alışverişe başlayıp sevdiklerinizi mutlu edecek en taze aranjmanlarımızı keşfedebilirsiniz.</p>
                    <a href="/" class="mt-8 inline-block bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm px-8 py-4 rounded-full transition shadow-lg hover:shadow-rose-100">
                        Çiçekleri Keşfet
                    </a>
                </div>
            @endif

        </div>
    </div>
@endsection
