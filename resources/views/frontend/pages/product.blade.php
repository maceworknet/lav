@extends('frontend.layouts.app')

@section('content')
    <div class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Breadcrumbs -->
            <nav class="flex text-xs text-slate-400 font-semibold mb-8 uppercase tracking-wider gap-2">
                <a href="/" class="hover:text-rose-600 transition">Ana Sayfa</a>
                <span>/</span>
                @if($product->categories->first())
                    <a href="{{ route('category', $product->categories->first()->slug) }}" class="hover:text-rose-600 transition">
                        {{ $product->categories->first()->name }}
                    </a>
                    <span>/</span>
                @endif
                <span class="text-slate-600 truncate max-w-[150px] sm:max-w-xs">{{ $product->name }}</span>
            </nav>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-16">
                
                <!-- Product Gallery (Left - 5 Cols) -->
                <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-8 self-start">
                    <div class="relative aspect-square overflow-hidden rounded-3xl border border-slate-200 bg-slate-50 flex items-center justify-center group">
                        @if($product->mainImage && $product->mainImage->url)
                            <img src="{{ $product->mainImage->url }}" alt="{{ $product->name }}" id="main-product-image" class="w-full h-full object-cover transition-all duration-300">
                        @else
                            <div class="absolute inset-0 bg-gradient-to-tr from-rose-50 to-rose-100 flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-32 h-32 text-rose-500/20">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" />
                                </svg>
                            </div>
                        @endif

                        <!-- Slider Navigation Arrows -->
                        <button type="button" onclick="changeImage(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/95 border border-slate-200 text-slate-700 hover:text-rose-600 flex items-center justify-center transition focus:outline-none z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                        </button>
                        <button type="button" onclick="changeImage(1)" class="absolute right-4 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/95 border border-slate-200 text-slate-700 hover:text-rose-600 flex items-center justify-center transition focus:outline-none z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Thumbnails list -->
                    @if($product->images->count() > 1)
                        <div class="flex flex-wrap gap-3">
                            @foreach($product->images as $index => $img)
                                @if($img->url)
                                    <button onclick="selectImage({{ $index }})" type="button" class="gallery-thumb w-20 aspect-square rounded-xl bg-white border {{ $index === 0 ? 'border-rose-600 ring-2 ring-rose-500/20' : 'border-slate-200' }} overflow-hidden shrink-0 focus:outline-none transition hover:border-rose-600">
                                        <img src="{{ $img->url }}" alt="Ürün Görseli" class="w-full h-full object-cover">
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Product Details Column (Right - 7 Cols) -->
                <div class="lg:col-span-7">
                    <form action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <input type="hidden" name="buy_now" id="buy_now_input" value="1">

                        <!-- delivery badge and favorite button -->
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-xs font-bold text-rose-600 bg-rose-50/50 border border-rose-200/60">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.321-5.128a2.5 2.5 0 0 0-2.485-2.344H13.5V4.688A1.125 1.125 0 0 0 12.375 3.562h-.75a1.125 1.125 0 0 0-1.125 1.125V8.25m6.75 3h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173L17.25 18.75"/>
                                </svg>
                                Aynı Gün Teslimat
                            </span>

                            <button type="button" class="favorite-toggle-btn p-3 rounded-full bg-white hover:bg-slate-50 text-slate-400 hover:text-rose-600 border border-slate-200 hover:border-rose-300 transition duration-300 focus:outline-none" data-product-id="{{ $product->id }}" title="{{ $product->isFavoritedByCurrentUser() ? 'Favorilerden Çıkar' : 'Favorilere Ekle' }}">
                                @if($product->isFavoritedByCurrentUser())
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-6 h-6 text-rose-600">
                                        <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                    </svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                    </svg>
                                @endif
                            </button>
                        </div>

                        <!-- Product Name & SKU -->
                        <div class="mt-4">
                            <h1 class="text-3xl font-extrabold text-slate-900 font-serif leading-tight">
                                {{ $product->name }}
                            </h1>
                            <p class="text-xs text-slate-400 font-bold mt-1.5 uppercase tracking-wider">Ürün Kodu: {{ $product->sku }}</p>
                        </div>

                        <!-- Price display & Quantity & Stock status -->
                        @php
                            $basePrice = (float)($product->discount_price ?? $product->price);
                            $priceParts = explode('.', number_format($basePrice, 2, '.', ''));
                            $integerPart = $priceParts[0] ?? '0';
                            $decimalPart = $priceParts[1] ?? '00';
                        @endphp
                        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 py-5 border-y border-slate-100">
                            <!-- Custom mockup formatted Price -->
                            <div class="flex items-baseline" id="display-price-container" data-base-price="{{ $basePrice }}">
                                <span class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight" id="price-integer">{{ $integerPart }}</span>
                                <span class="text-lg font-bold text-slate-800 align-super" id="price-decimal">,{{ $decimalPart }}</span>
                                <span class="text-2xl font-bold text-slate-800 ml-1">₺</span>
                                <span class="text-[10px] text-slate-400 font-bold ml-2 uppercase tracking-wide block">(KDV Dahil)</span>
                            </div>

                            <!-- Stock & Qty counter -->
                            <div class="flex items-center gap-3">
                                <span class="px-3.5 py-1.5 text-xs font-bold text-emerald-600 bg-emerald-50 rounded-full border border-emerald-200">
                                    Stokta
                                </span>
                                
                                <div class="flex items-center border border-slate-200 rounded-full bg-slate-50/50 p-1">
                                    <button type="button" onclick="changeQty(-1)" class="w-8 h-8 flex items-center justify-center text-slate-500 font-bold hover:text-rose-600 hover:bg-white rounded-full transition duration-200 focus:outline-none">-</button>
                                    <input type="number" id="qty-input" name="quantity" value="1" min="1" class="w-8 text-center font-black text-sm bg-transparent border-none focus:outline-none focus:ring-0 p-0" readonly>
                                    <button type="button" onclick="changeQty(1)" class="w-8 h-8 flex items-center justify-center text-slate-500 font-bold hover:text-rose-600 hover:bg-white rounded-full transition duration-200 focus:outline-none">+</button>
                                </div>
                            </div>
                        </div>

                        <!-- Short Description -->
                        @if($product->short_description)
                            <p class="mt-5 text-sm text-slate-500 leading-relaxed">{{ $product->short_description }}</p>
                        @endif

                        <!-- Product Options -->
                        @if($product->options->count() > 0)
                            <div class="mt-6 space-y-5">
                                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Ürün Seçenekleri</h3>
                                
                                @foreach($product->options as $option)
                                    <div class="space-y-2">
                                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">
                                            {{ $option->name }} @if($option->is_required)<span class="text-rose-600">*</span>@endif
                                        </label>
                                        
                                        @if($option->type === 'select')
                                            <select name="options[{{ $option->id }}]" class="option-modifier block w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition" @if($option->is_required) required @endif>
                                                @foreach($option->values as $val)
                                                    <option value="{{ $val->id }}" data-price="{{ (float)$val->price_modifier }}">
                                                        {{ $val->label }} @if($val->price_modifier > 0) (+ ₺{{ number_format($val->price_modifier, 2) }}) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        @elseif($option->type === 'checkbox')
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                @foreach($option->values as $val)
                                                    <label class="flex items-center gap-3 bg-slate-50/50 hover:bg-rose-50/20 p-3 rounded-xl border border-slate-100 cursor-pointer transition">
                                                        <input type="checkbox" name="options[{{ $option->id }}][]" value="{{ $val->id }}" data-price="{{ (float)$val->price_modifier }}" class="option-modifier rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                                                        <span class="text-sm font-medium text-slate-700">{{ $val->label }}</span>
                                                        @if($val->price_modifier > 0)
                                                            <span class="text-xs font-bold text-rose-600 ml-auto">+ ₺{{ number_format($val->price_modifier, 2) }}</span>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- Ekstra Hediyeler -->
                        @if(isset($extraGifts) && $extraGifts->count() > 0)
                            <div class="mt-6 space-y-4">
                                <h3 class="text-xs font-bold text-slate-500 uppercase tracking-widest">Ekstra Hediyeler (Opsiyonel)</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @foreach($extraGifts as $gift)
                                        <label class="flex items-center gap-3 bg-white hover:bg-rose-50/10 p-3.5 rounded-2xl border border-slate-200 hover:border-rose-200 cursor-pointer transition duration-300 shadow-sm relative group">
                                            <input type="checkbox" name="extra_gifts[]" value="{{ $gift->id }}" data-price="{{ (float)$gift->final_price }}" class="extra-gift-modifier rounded border-slate-300 text-rose-600 focus:ring-rose-500 w-5 h-5 transition duration-300">
                                            @if($gift->mainImage && $gift->mainImage->url)
                                                <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-50 border border-slate-100 shrink-0">
                                                    <img src="{{ $gift->mainImage->url }}" alt="{{ $gift->name }}" class="w-full h-full object-cover">
                                                </div>
                                            @endif
                                            <div class="flex flex-col min-w-0">
                                                <a href="{{ route('product', $gift->slug) }}" target="_blank" onclick="event.stopPropagation();" class="text-sm font-semibold text-slate-800 hover:text-rose-600 transition truncate">{{ $gift->name }}</a>
                                                @if($gift->short_description)
                                                    <span class="text-[11px] text-slate-400 line-clamp-1 mt-0.5">{{ $gift->short_description }}</span>
                                                @endif
                                                <span class="text-xs font-black text-rose-600 mt-1">₺{{ number_format($gift->final_price, 2) }}</span>
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Personal Message Note -->
                        <div class="mt-6 space-y-2">
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Kişisel Kart Notu (Opsiyonel)</label>
                            <textarea name="card_note" rows="2" placeholder="Çiçeğinizle birlikte gidecek sevgi dolu mesajınızı buraya yazabilirsiniz..." class="block w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500 focus:border-rose-500 transition"></textarea>
                        </div>

                        <!-- Address Selector Module -->
                        <div class="mt-6 space-y-4 p-5 rounded-2xl border border-slate-200 bg-slate-50/30">
                            <h3 class="text-xs font-black text-slate-500 uppercase tracking-widest">Göndereceğiniz Adresi Seçin</h3>
                            
                            <!-- District & Neighborhood selection grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <!-- İlçe Seçimi -->
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase">Teslimat İlçesi <span class="text-rose-600">*</span></label>
                                    <select id="delivery-district-select" class="block w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-rose-500 bg-white transition">
                                        <option value="">İlçe Seçiniz</option>
                                        @foreach($deliveryZones as $zone)
                                            <option value="{{ $zone->district }}">{{ $zone->district }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Mahalle Seçimi -->
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-bold text-slate-400 uppercase">Teslimat Mahallesi <span class="text-rose-600">*</span></label>
                                    <select id="delivery-neighborhood-select" class="block w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-rose-500 bg-white transition" disabled>
                                        <option value="">İlçe seçiniz</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Hidden inputs for cart addition -->
                            <input type="hidden" name="delivery_district" id="hidden-delivery-district" value="">
                            <input type="hidden" name="delivery_neighborhood" id="hidden-delivery-neighborhood" value="">
                            <input type="hidden" name="delivery_date" id="hidden-delivery-date" value="">
                            <input type="hidden" name="delivery_slot" id="hidden-delivery-slot" value="">

                            <!-- Date & Time Picker Container (Shown when neighborhood is selected) -->
                            <div id="date-time-module" class="hidden space-y-4 pt-4 border-t border-slate-200/80">
                                <h4 class="text-sm font-bold text-slate-800">Teslimat Tarihi ve Saati</h4>
                                
                                <!-- Date Buttons -->
                                <div class="grid grid-cols-3 gap-3">
                                    <!-- Bugün Button -->
                                    <button type="button" id="date-btn-today" onclick="selectDeliveryDate('today')" class="flex flex-col items-center justify-center p-3 rounded-2xl border border-slate-200 bg-white text-slate-700 hover:border-slate-300 transition duration-200 focus:outline-none">
                                        <svg class="w-6 h-6 text-rose-600 mb-1" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-.321-5.128a2.5 2.5 0 0 0-2.485-2.344H13.5V4.688A1.125 1.125 0 0 0 12.375 3.562h-.75a1.125 1.125 0 0 0-1.125 1.125V8.25m6.75 3h1.372c.516 0 .966.351 1.091.852l1.106 4.423c.11.44-.054.902-.417 1.173L17.25 18.75" />
                                        </svg>
                                        <span class="text-xs font-bold">Bugün</span>
                                        <span class="text-[9px] text-slate-400 font-semibold mt-0.5" id="label-date-today"></span>
                                    </button>

                                    <!-- Yarın Button -->
                                    <button type="button" id="date-btn-tomorrow" onclick="selectDeliveryDate('tomorrow')" class="flex flex-col items-center justify-center p-3 rounded-2xl border border-slate-200 bg-white text-slate-700 hover:border-slate-300 transition duration-200 focus:outline-none">
                                        <svg class="w-6 h-6 text-rose-600 mb-1" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                                        </svg>
                                        <span class="text-xs font-bold">Yarın</span>
                                        <span class="text-[9px] text-slate-400 font-semibold mt-0.5" id="label-date-tomorrow"></span>
                                    </button>

                                    <!-- Tarih Seç Button -->
                                    <button type="button" id="date-btn-custom" onclick="toggleCustomCalendar()" class="relative flex flex-col items-center justify-center p-3 rounded-2xl border border-slate-200 bg-white text-slate-700 hover:border-slate-300 transition duration-200 focus:outline-none">
                                        <svg class="w-6 h-6 text-rose-600 mb-1" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75" />
                                        </svg>
                                        <span class="text-xs font-bold">Tarih Seç</span>
                                        <span class="text-[9px] text-slate-400 font-semibold mt-0.5" id="label-date-custom">Seçiniz</span>
                                    </button>
                                </div>

                                <!-- Custom Calendar Container -->
                                <div id="custom-calendar-container" class="hidden mt-3 p-4 bg-white border border-slate-200 rounded-2xl shadow-xl space-y-3 max-w-sm mx-auto">
                                    <!-- Header with Month/Year and Prev/Next buttons -->
                                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                        <button type="button" onclick="changeMonth(-1)" class="p-1.5 rounded-lg hover:bg-rose-50 hover:text-rose-600 text-slate-500 transition duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                                            </svg>
                                        </button>
                                        <span id="calendar-month-year" class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Haziran 2026</span>
                                        <button type="button" onclick="changeMonth(1)" class="p-1.5 rounded-lg hover:bg-rose-50 hover:text-rose-600 text-slate-500 transition duration-200">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- Week Days -->
                                    <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-black text-slate-400 uppercase tracking-wider">
                                        <div>Pt</div>
                                        <div>Sa</div>
                                        <div>Ça</div>
                                        <div>Pe</div>
                                        <div>Cu</div>
                                        <div>Ct</div>
                                        <div>Pz</div>
                                    </div>
                                    <!-- Days Grid -->
                                    <div id="calendar-days-grid" class="grid grid-cols-7 gap-1">
                                        <!-- Generated by JS -->
                                    </div>
                                </div>

                                <!-- Time Slot Dropdown -->
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-xl border border-slate-200 bg-white text-slate-400 flex items-center justify-center shrink-0">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0" />
                                        </svg>
                                    </div>
                                    <div class="flex-grow">
                                        <select id="delivery-slot-select" class="block w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-rose-500 bg-white transition">
                                            <option value="">Teslimat saati seçin</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Purchase Buttons (Side-by-side) -->
                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Sipariş Ver (Solid brand button) -->
                            <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 px-6 rounded-xl transition duration-300 text-sm tracking-wider uppercase focus:outline-none">
                                SİPARİŞ VER
                            </button>

                            <!-- WhatsApp Sipariş (Outline button) -->
                            @php
                                $whatsapp = \App\Models\Setting::where('key', 'site_whatsapp')->value('value') ?? '';
                                $whatsappText = "Merhaba, *" . $product->name . "* (Ürün Kodu: " . $product->sku . ") ürünü hakkında bilgi almak ve sipariş vermek istiyorum. Fiyat: " . number_format($basePrice, 2) . " ₺";
                                $whatsappUrl = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $whatsapp) . '?text=' . urlencode($whatsappText);
                            @endphp
                            <a href="{{ $whatsappUrl }}" id="whatsapp-order-btn" target="_blank" class="w-full flex items-center justify-center gap-2 border border-slate-200 hover:border-emerald-500 hover:text-emerald-600 text-slate-700 font-bold py-4 px-6 rounded-xl transition duration-300 text-sm tracking-wider uppercase">
                                <svg class="w-5 h-5 text-emerald-500 fill-current" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.472 14.382c-.022-.08-.085-.161-.26-.247-.174-.086-1.03-.509-1.19-.567-.16-.058-.276-.086-.391.086-.115.172-.448.567-.55.681-.102.115-.205.129-.38.043-.174-.086-.737-.272-1.405-.87-5.185-4.62-5.755-4.887-5.892-4.99-.137-.103-.014-.158.072-.244.086-.086.172-.201.258-.302a.684.684 0 0 0 .085-.144c.058-.115.029-.215-.014-.302-.043-.086-.391-.941-.536-1.292-.14-.339-.282-.293-.388-.293-.102-.004-.218-.004-.335-.004a.64.64 0 0 0-.466.216C7.23 6.309 6.5 7.02 6.5 8.441c0 1.42.103 2.793.246 2.986.143.193 2.007 3.065 4.863 4.298.68.293 1.21.468 1.62.597.683.218 1.306.187 1.8.113.55-.083 1.692-.692 1.93-1.36.237-.669.237-1.24.166-1.36M12.003 21c-1.624 0-3.21-.424-4.602-1.229L3 21l1.252-4.404C3.447 15.176 3 13.56 3 11.997 3 7.032 7.035 3 12.003 3c4.97 0 9.002 4.032 9.002 9.003C21 16.97 16.97 21 12.003 21m0-19C6.489 2 2 6.489 2 12c0 1.768.463 3.493 1.343 5.011L2 22l5.132-1.347c1.468.802 3.125 1.226 4.871 1.226 5.511 0 10-4.489 10-10C22 6.489 17.511 2 12.003 2"/>
                                </svg>
                                WHATSAPP SİPARİŞ
                            </a>
                        </div>

                        <!-- Camera confirmation box -->
                        <div class="mt-6 flex items-start gap-4 p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100 text-emerald-800">
                            <div class="p-2.5 bg-emerald-100 text-emerald-700 rounded-xl shrink-0">
                                <svg class="w-6 h-6 fill-none stroke-current" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="text-sm font-bold text-slate-800">Görsel Onayı ile Kontrol Sizde!</h4>
                                <p class="text-xs text-slate-500 mt-1 leading-relaxed">Siparişiniz teslimattan önce size gösterilir, onayınızı aldıktan sonra yola çıkar.</p>
                            </div>
                        </div>

                        <!-- Trust badges grid -->
                        <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 text-center">
                                <svg class="w-5 h-5 text-slate-400 mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0" />
                                </svg>
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Hızlı Teslimat</span>
                            </div>

                            <div class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 text-center">
                                <svg class="w-5 h-5 text-slate-400 mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                </svg>
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Güvenli Ödeme</span>
                            </div>

                            <div class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 text-center">
                                <svg class="w-5 h-5 text-slate-400 mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18M3 12h18M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4Zm0 0c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4Z" />
                                </svg>
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">Taze Çiçekler</span>
                            </div>

                            <div class="flex flex-col items-center justify-center p-3.5 rounded-xl bg-slate-50/50 border border-slate-100 text-center">
                                <svg class="w-5 h-5 text-slate-400 mb-1.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 0 1 5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-5 2.9h.01" />
                                </svg>
                                <span class="text-[10px] font-bold text-slate-600 uppercase tracking-wider">7/24 Destek</span>
                            </div>
                        </div>

                    </form>
                </div>

            </div>

            <!-- Tabs Section (Below Column Grid) -->
            <div class="mt-20 border-t border-slate-100 pt-12">
                <!-- Tab Headers (Pills) -->
                <div class="flex flex-wrap items-center justify-center gap-3 mb-10">
                    <button type="button" onclick="switchTab('details')" id="tab-btn-details" class="tab-btn px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition border border-rose-600 bg-rose-600 text-white shadow-sm focus:outline-none">
                        ÜRÜN DETAYLARI
                    </button>
                    <button type="button" onclick="switchTab('installments')" id="tab-btn-installments" class="tab-btn px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition border border-slate-200 bg-white text-slate-600 hover:border-slate-300 focus:outline-none">
                        TAKSİT SEÇENEKLERİ
                    </button>
                    <button type="button" onclick="switchTab('faq')" id="tab-btn-faq" class="tab-btn px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition border border-slate-200 bg-white text-slate-600 hover:border-slate-300 focus:outline-none">
                        SIK SORULAN SORULAR
                    </button>
                </div>

                <!-- Tab Contents Container -->
                <div class="bg-white rounded-3xl border border-slate-200 p-8 min-h-[220px]">
                    <!-- Tab: Details -->
                    <div id="tab-content-details" class="tab-content space-y-6">
                        @if($product->description)
                            <div class="space-y-2">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Ürün Açıklaması</h3>
                                <p class="text-sm text-slate-500 leading-relaxed">{{ $product->description }}</p>
                            </div>
                        @endif

                        @if($product->care_instructions)
                            <div class="space-y-2 pt-4 border-t border-slate-100">
                                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest">Çiçek Bakım Önerisi</h3>
                                <p class="text-sm text-slate-500 leading-relaxed">{{ $product->care_instructions }}</p>
                            </div>
                        @endif

                        @if(!$product->description && !$product->care_instructions)
                            <p class="text-sm text-slate-400 text-center">Bu ürün için henüz detaylı açıklama eklenmemiştir.</p>
                        @endif
                    </div>

                    <!-- Tab: Installments -->
                    <div id="tab-content-installments" class="tab-content hidden">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-6">Taksit Seçenekleri</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs sm:text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 bg-slate-50">
                                        <th class="p-4 font-bold text-slate-600">Banka / Kart</th>
                                        <th class="p-4 font-bold text-slate-600">Tek Çekim</th>
                                        <th class="p-4 font-bold text-slate-600">3 Taksit</th>
                                        <th class="p-4 font-bold text-slate-600">6 Taksit</th>
                                        <th class="p-4 font-bold text-slate-600">9 Taksit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <tr>
                                        <td class="p-4 font-semibold text-slate-800">Axess (Akbank)</td>
                                        <td class="p-4" id="inst-axess-1">₺0,00</td>
                                        <td class="p-4" id="inst-axess-3">3 x ₺0,00</td>
                                        <td class="p-4" id="inst-axess-6">6 x ₺0,00</td>
                                        <td class="p-4" id="inst-axess-9">9 x ₺0,00</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 font-semibold text-slate-800">Bonus (Garanti)</td>
                                        <td class="p-4" id="inst-bonus-1">₺0,00</td>
                                        <td class="p-4" id="inst-bonus-3">3 x ₺0,00</td>
                                        <td class="p-4" id="inst-bonus-6">6 x ₺0,00</td>
                                        <td class="p-4" id="inst-bonus-9">9 x ₺0,00</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 font-semibold text-slate-800">Maximum (İş Bankası)</td>
                                        <td class="p-4" id="inst-maximum-1">₺0,00</td>
                                        <td class="p-4" id="inst-maximum-3">3 x ₺0,00</td>
                                        <td class="p-4" id="inst-maximum-6">6 x ₺0,00</td>
                                        <td class="p-4" id="inst-maximum-9">9 x ₺0,00</td>
                                    </tr>
                                    <tr>
                                        <td class="p-4 font-semibold text-slate-800">World (Yapı Kredi)</td>
                                        <td class="p-4" id="inst-world-1">₺0,00</td>
                                        <td class="p-4" id="inst-world-3">3 x ₺0,00</td>
                                        <td class="p-4" id="inst-world-6">6 x ₺0,00</td>
                                        <td class="p-4" id="inst-world-9">9 x ₺0,00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-4">* Taksitli tutarlar sepet toplamına göre ödeme adımında banka komisyonlarına bağlı olarak değişiklik gösterebilir.</p>
                    </div>

                    <!-- Tab: FAQ -->
                    <div id="tab-content-faq" class="tab-content hidden space-y-4">
                        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-widest mb-4">Sık Sorulan Sorular</h3>
                        <div class="space-y-4 divide-y divide-slate-100">
                            <div class="pt-4 first:pt-0">
                                <h4 class="text-sm font-bold text-slate-800">Aynı gün teslimat yapıyor musunuz?</h4>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed">Evet, Diyarbakır genelinde teslimat bölgelerimiz dahilindeki tüm ilçelere seçtiğiniz saat diliminde aynı gün teslimat gerçekleştiriyoruz.</p>
                            </div>
                            <div class="pt-4">
                                <h4 class="text-sm font-bold text-slate-800">Görsel onayı nasıl çalışıyor?</h4>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed">Hazırlanan çiçek tasarımının fotoğrafı kuryemiz yola çıkmadan önce size WhatsApp üzerinden gönderilir. Siz onay verdikten sonra siparişiniz teslimata çıkar.</p>
                            </div>
                            <div class="pt-4">
                                <h4 class="text-sm font-bold text-slate-800">Ödemeler güvenli mi?</h4>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed">Tüm ödemeleriniz 256-bit SSL güvenlik sertifikalı iyzico altyapısı ile güvence altına alınmaktadır. Kart bilgileriniz asla sunucumuzda barındırılmaz.</p>
                            </div>
                            <div class="pt-4">
                                <h4 class="text-sm font-bold text-slate-800">Sipariş sonrasında çiçeğimi takip edebilir miyim?</h4>
                                <p class="text-xs sm:text-sm text-slate-500 mt-1.5 leading-relaxed">Siparişiniz tamamlandıktan sonra size iletilen Sipariş Takip Numarası ile sitemiz üzerinden çiçeğinizin anlık durumunu izleyebilirsiniz.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            @if($relatedProducts->count() > 0)
                <div class="mt-24 border-t border-slate-100 pt-16">
                    <h2 class="text-2xl font-extrabold text-slate-900 font-serif mb-8 text-center">Benzer Ürünler</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                        @foreach($relatedProducts as $rel)
                            <div class="group relative bg-white rounded-2xl overflow-hidden border border-slate-100 hover:border-rose-600 transition duration-300 flex flex-col justify-between product-card-container">
                                <div>
                                    <div class="relative aspect-square overflow-hidden bg-rose-50/50">
                                        <!-- Favorite Toggle Button -->
                                        <button type="button" class="favorite-toggle-btn absolute top-4 right-4 z-20 p-2 rounded-full bg-white/80 hover:bg-white text-slate-500 hover:text-rose-600 shadow-sm transition duration-300 focus:outline-none" data-product-id="{{ $rel->id }}" title="{{ $rel->isFavoritedByCurrentUser() ? 'Favorilerden Çıkar' : 'Favorilere Ekle' }}">
                                            @if($rel->isFavoritedByCurrentUser())
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24" class="w-5 h-5 text-rose-600">
                                                    <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                                </svg>
                                            @else
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                                                </svg>
                                            @endif
                                        </button>

                                        <a href="{{ route('product', $rel->slug) }}">
                                            @if($rel->mainImage && $rel->mainImage->url)
                                                <img src="{{ $rel->mainImage->url }}" alt="{{ $rel->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                                            @else
                                                <div class="absolute inset-0 bg-gradient-to-br from-rose-100 to-rose-200 flex items-center justify-center group-hover:scale-105 transition duration-500">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor" class="w-12 h-12 text-rose-500/20">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </a>
                                    </div>
                                    <div class="p-6">
                                        <h3 class="font-serif text-lg font-bold text-slate-800 hover:text-rose-600 transition truncate">
                                            <a href="{{ route('product', $rel->slug) }}">{{ $rel->name }}</a>
                                        </h3>
                                    </div>
                                </div>
                                <div class="p-6 pt-0 border-t border-slate-50 flex items-center justify-between mt-auto">
                                    <span class="text-lg font-black text-slate-800">₺{{ number_format($rel->price, 2) }}</span>
                                    <a href="{{ route('product', $rel->slug) }}" class="p-3 bg-slate-900 text-white rounded-xl hover:bg-rose-600 transition">
                                        <!-- gift-shop.svg icon as requested -->
                                        <svg class="w-5 h-5 fill-current" viewBox="0 0 19.3 21">
                                            <path d="M13.4,9v-4.5c0-2.1-1.7-3.8-3.8-3.8s-3.8,1.7-3.8,3.8v4.5M17.2,7l1.3,12c0,.7-.5,1.2-1.1,1.2H1.9c-.6,0-1.1-.5-1.1-1.1,0,0,0,0,0-.1l1.3-12c0-.6.5-1.1,1.1-1.1h13c.6,0,1.1.4,1.1,1ZM6.3,9c0,.2-.2.4-.4.4s-.4-.2-.4-.4.2-.4.4-.4.4.2.4.4ZM13.8,9c0,.2-.2.4-.4.4s-.4-.2-.4-.4.2-.4.4-.4.4.2.4.4Z"/>
                                            <path d="M13.3,13.2c0-1-.8-1.8-1.9-1.8s-1.4.4-1.7,1.1c-.3-.6-.9-1.1-1.7-1.1-1,0-1.9.8-1.9,1.8,0,2.9,3.6,4.8,3.6,4.8,0,0,3.6-1.9,3.6-4.8Z"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Live calculations & gallery scripts -->
    <script>
        // Gallery Slider Data
        const productImages = [
            @if($product->mainImage && $product->mainImage->url)
                '{{ $product->mainImage->url }}',
            @endif
            @foreach($product->images as $img)
                @if($img->url && ($product->mainImage ? $img->url !== $product->mainImage->url : true))
                    '{{ $img->url }}',
                @endif
            @endforeach
        ];

        let currentImageIndex = 0;

        function changeImage(direction) {
            if (productImages.length <= 1) return;
            currentImageIndex += direction;
            if (currentImageIndex < 0) {
                currentImageIndex = productImages.length - 1;
            } else if (currentImageIndex >= productImages.length) {
                currentImageIndex = 0;
            }
            document.getElementById('main-product-image').src = productImages[currentImageIndex];
            updateActiveThumbnail();
        }

        function selectImage(index) {
            currentImageIndex = index;
            document.getElementById('main-product-image').src = productImages[currentImageIndex];
            updateActiveThumbnail();
        }

        function updateActiveThumbnail() {
            document.querySelectorAll('.gallery-thumb').forEach((thumb, idx) => {
                if (idx === currentImageIndex) {
                    thumb.className = 'gallery-thumb w-20 aspect-square rounded-xl bg-white border border-rose-600 ring-2 ring-rose-500/20 overflow-hidden shrink-0 focus:outline-none transition';
                } else {
                    thumb.className = 'gallery-thumb w-20 aspect-square rounded-xl bg-white border border-slate-200 overflow-hidden shrink-0 focus:outline-none transition hover:border-rose-600';
                }
            });
        }

        // Quantity counter change
        function changeQty(amount) {
            const input = document.getElementById('qty-input');
            let val = parseInt(input.value) + amount;
            if (val < 1) val = 1;
            input.value = val;
            updateTotalPrice();
        }

        // Tabs switching
        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.className = 'tab-btn px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition border border-slate-200 bg-white text-slate-600 hover:border-slate-300 focus:outline-none';
            });

            document.getElementById('tab-content-' + tabId).classList.remove('hidden');
            
            const activeBtn = document.getElementById('tab-btn-' + tabId);
            activeBtn.className = 'tab-btn px-6 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition border border-rose-600 bg-rose-600 text-white shadow-sm focus:outline-none';
        }

        // Live pricing format update & Installment tables update
        function updateTotalPrice() {
            const priceElContainer = document.getElementById('display-price-container');
            const qty = parseInt(document.getElementById('qty-input').value) || 1;
            const basePrice = parseFloat(priceElContainer.getAttribute('data-base-price'));
            
            let optionsModifier = 0.00;
            
            // Collect all selected options price modifiers
            document.querySelectorAll('.option-modifier').forEach(el => {
                if (el.tagName === 'SELECT') {
                    const selectedOpt = el.options[el.selectedIndex];
                    if (selectedOpt) {
                        optionsModifier += parseFloat(selectedOpt.getAttribute('data-price')) || 0;
                    }
                } else if (el.tagName === 'INPUT' && el.type === 'checkbox' && el.checked) {
                    optionsModifier += parseFloat(el.getAttribute('data-price')) || 0;
                }
            });

            let extraGiftsModifier = 0.00;
            document.querySelectorAll('.extra-gift-modifier').forEach(el => {
                if (el.type === 'checkbox' && el.checked) {
                    extraGiftsModifier += parseFloat(el.getAttribute('data-price')) || 0;
                }
            });

            const unitTotal = basePrice + optionsModifier;
            const finalTotal = (unitTotal * qty) + extraGiftsModifier;
            
            // Formatted parts
            const formatted = finalTotal.toFixed(2);
            const parts = formatted.split('.');
            const integerPart = parseInt(parts[0]).toLocaleString('tr-TR');
            const decimalPart = parts[1];
            
            document.getElementById('price-integer').innerText = integerPart;
            document.getElementById('price-decimal').innerText = ',' + decimalPart;

            // Update Installment tab table rows
            updateInstallments(finalTotal);
        }

        let activeInstallmentRequest = null;
        function updateInstallments(totalPrice) {
            if (activeInstallmentRequest) {
                activeInstallmentRequest.abort();
            }

            const tbody = document.querySelector('#tab-content-installments tbody');
            if (!tbody) return;

            activeInstallmentRequest = new AbortController();
            const signal = activeInstallmentRequest.signal;

            fetch('/taksit-secenekleri?price=' + totalPrice, { signal })
                .then(response => response.json())
                .then(res => {
                    if (res.success && res.data) {
                        tbody.innerHTML = '';
                        res.data.forEach(item => {
                            const tr = document.createElement('tr');
                            tr.className = 'border-b border-slate-100 hover:bg-slate-50/30';
                            
                            const tdName = document.createElement('td');
                            tdName.className = 'p-4 font-semibold text-slate-800';
                            tdName.innerText = item.cardFamily || 'Diğer';
                            tr.appendChild(tdName);

                            const formatPrice = (val) => '₺' + parseFloat(val).toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                            
                            const getVal = (num) => {
                                const opt = item.installmentPrices.find(p => p.installmentNumber === num);
                                if (opt) {
                                    if (num === 1) return formatPrice(opt.installmentPrice);
                                    return num + ' x ' + formatPrice(opt.installmentPrice);
                                }
                                return '-';
                            };

                            [1, 3, 6, 9].forEach(num => {
                                const td = document.createElement('td');
                                td.className = 'p-4';
                                td.innerText = getVal(num);
                                tr.appendChild(td);
                            });

                            tbody.appendChild(tr);
                        });
                    }
                })
                .catch(err => {
                    if (err.name !== 'AbortError') {
                        console.error('Taksit seçenekleri yüklenirken hata oluştu:', err);
                    }
                });
        }

        // Add event listeners on options change
        document.querySelectorAll('.option-modifier').forEach(el => {
            el.addEventListener('change', updateTotalPrice);
        });

        document.querySelectorAll('.extra-gift-modifier').forEach(el => {
            el.addEventListener('change', updateTotalPrice);
            el.addEventListener('change', updateWhatsAppLink);
        });

        // Initialize pricing on page load
        updateTotalPrice();

        // Delivery Address & Scheduling Module Logic
        const zonesData = @json($deliveryZones->values());
        
        const activeBtnClass = 'flex flex-col items-center justify-center p-3.5 rounded-2xl border-2 border-rose-600 bg-rose-50/20 text-rose-700 transition duration-200 focus:outline-none';
        const inactiveBtnClass = 'flex flex-col items-center justify-center p-3.5 rounded-2xl border border-slate-200 bg-white text-slate-700 hover:border-slate-300 transition duration-200 focus:outline-none';

        // Pre-fill button dates on page load
        const todayDateObj = new Date();
        const tomorrowDateObj = new Date();
        tomorrowDateObj.setDate(todayDateObj.getDate() + 1);

        function formatTurkishDate(dateObj) {
            const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
            const days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
            return `${dateObj.getDate()} ${months[dateObj.getMonth()]} ${days[dateObj.getDay()]}`;
        }

        document.getElementById('label-date-today').innerText = formatTurkishDate(todayDateObj);
        document.getElementById('label-date-tomorrow').innerText = formatTurkishDate(tomorrowDateObj);

        // District change listener
        const districtSelect = document.getElementById('delivery-district-select');
        const neighborhoodSelect = document.getElementById('delivery-neighborhood-select');
        const dateTimeModule = document.getElementById('date-time-module');

        districtSelect.addEventListener('change', function() {
            const selectedDistrict = this.value;
            
            // Clear inputs
            neighborhoodSelect.innerHTML = '<option value="">Mahalle Seçiniz</option>';
            neighborhoodSelect.disabled = true;
            dateTimeModule.classList.add('hidden');
            
            document.getElementById('hidden-delivery-district').value = '';
            document.getElementById('hidden-delivery-neighborhood').value = '';
            document.getElementById('hidden-delivery-date').value = '';
            document.getElementById('hidden-delivery-slot').value = '';

            if (!selectedDistrict) {
                updateWhatsAppLink();
                return;
            }

            document.getElementById('hidden-delivery-district').value = selectedDistrict;

            // Find matching zone in zonesData (exact match)
            const zone = Object.values(zonesData).find(z => z.district === selectedDistrict);
            if (zone && zone.neighborhoods && zone.neighborhoods.length > 0) {
                neighborhoodSelect.disabled = false;
                zone.neighborhoods.forEach(neigh => {
                    const opt = document.createElement('option');
                    opt.value = neigh.name;
                    opt.textContent = neigh.name;
                    neighborhoodSelect.appendChild(opt);
                });
            } else {
                // If there are no neighborhoods in database, enable input or show fallback
                const opt = document.createElement('option');
                opt.value = "Merkez";
                opt.textContent = "Merkez Mahallesi";
                neighborhoodSelect.appendChild(opt);
                neighborhoodSelect.disabled = false;
            }
            updateWhatsAppLink();
        });

        // Neighborhood change listener
        neighborhoodSelect.addEventListener('change', function() {
            const selectedNeigh = this.value;
            
            if (!selectedNeigh) {
                dateTimeModule.classList.add('hidden');
                document.getElementById('hidden-delivery-neighborhood').value = '';
                document.getElementById('hidden-delivery-date').value = '';
                document.getElementById('hidden-delivery-slot').value = '';
                updateWhatsAppLink();
                return;
            }

            document.getElementById('hidden-delivery-neighborhood').value = selectedNeigh;
            dateTimeModule.classList.remove('hidden');

            // Automatically select "Bugün" (today) by default
            selectDeliveryDate('today');
        });

        // Custom calendar current month state
        let currentCalendarDate = new Date();

        function renderCalendar() {
            const monthsTr = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
            document.getElementById('calendar-month-year').innerText = monthsTr[currentCalendarDate.getMonth()] + ' ' + currentCalendarDate.getFullYear();
            
            const daysGrid = document.getElementById('calendar-days-grid');
            daysGrid.innerHTML = '';
            
            const firstDayOfMonth = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth(), 1);
            const lastDayOfMonth = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth() + 1, 0);
            const numDays = lastDayOfMonth.getDate();
            
            let firstDayIndex = firstDayOfMonth.getDay();
            let startOffset = firstDayIndex === 0 ? 6 : firstDayIndex - 1;
            
            for (let i = 0; i < startOffset; i++) {
                const emptyDiv = document.createElement('div');
                emptyDiv.className = 'w-full aspect-square';
                daysGrid.appendChild(emptyDiv);
            }
            
            const today = new Date();
            today.setHours(0,0,0,0);
            
            const selectedDateStr = document.getElementById('hidden-delivery-date').value;
            let selectedDateObj = null;
            if (selectedDateStr) {
                selectedDateObj = new Date(selectedDateStr);
                selectedDateObj.setHours(0,0,0,0);
            }
            
            for (let day = 1; day <= numDays; day++) {
                const dateOfIndex = new Date(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth(), day);
                dateOfIndex.setHours(0,0,0,0);
                
                const dayBtn = document.createElement('button');
                dayBtn.type = 'button';
                dayBtn.innerText = day;
                
                const isPast = dateOfIndex < today;
                const isToday = dateOfIndex.getTime() === today.getTime();
                const isSelected = selectedDateObj && dateOfIndex.getTime() === selectedDateObj.getTime();
                
                let baseClass = 'w-full aspect-square rounded-xl flex items-center justify-center text-xs font-bold transition duration-200 focus:outline-none';
                
                if (isPast) {
                    dayBtn.className = baseClass + ' text-slate-300 bg-slate-50 cursor-not-allowed pointer-events-none';
                    dayBtn.disabled = true;
                } else if (isSelected) {
                    dayBtn.className = baseClass + ' bg-rose-600 text-white shadow-md hover:bg-rose-700';
                } else if (isToday) {
                    dayBtn.className = baseClass + ' border border-rose-500 text-rose-600 bg-rose-50/20 hover:bg-rose-50';
                } else {
                    dayBtn.className = baseClass + ' text-slate-700 hover:bg-rose-50 hover:text-rose-600';
                }
                
                if (!isPast) {
                    dayBtn.addEventListener('click', function() {
                        selectCustomDate(currentCalendarDate.getFullYear(), currentCalendarDate.getMonth(), day);
                    });
                }
                
                daysGrid.appendChild(dayBtn);
            }
        }

        window.changeMonth = function(dir) {
            currentCalendarDate.setMonth(currentCalendarDate.getMonth() + dir);
            renderCalendar();
        };

        window.toggleCustomCalendar = function() {
            const calendarContainer = document.getElementById('custom-calendar-container');
            const isHidden = calendarContainer.classList.contains('hidden');
            
            if (isHidden) {
                calendarContainer.classList.remove('hidden');
                const selectedDateStr = document.getElementById('hidden-delivery-date').value;
                if (selectedDateStr) {
                    currentCalendarDate = new Date(selectedDateStr);
                } else {
                    currentCalendarDate = new Date();
                }
                renderCalendar();
            } else {
                calendarContainer.classList.add('hidden');
            }
        };

        window.selectCustomDate = function(year, month, day) {
            const formattedMonth = String(month + 1).padStart(2, '0');
            const formattedDay = String(day).padStart(2, '0');
            const dateVal = `${year}-${formattedMonth}-${formattedDay}`;
            
            const selectedDate = new Date(year, month, day);
            const shortMonths = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
            const shortDays = ['Paz', 'Pzt', 'Sal', 'Çar', 'Per', 'Cum', 'Cmt'];
            const labelStr = `${day} ${shortMonths[month]} ${shortDays[selectedDate.getDay()]}`;
            
            document.getElementById('label-date-custom').innerText = labelStr;
            
            const todayBtn = document.getElementById('date-btn-today');
            const tomorrowBtn = document.getElementById('date-btn-tomorrow');
            const customBtn = document.getElementById('date-btn-custom');
            
            todayBtn.className = inactiveBtnClass;
            tomorrowBtn.className = inactiveBtnClass;
            customBtn.className = activeBtnClass;
            
            document.getElementById('hidden-delivery-date').value = dateVal;
            document.getElementById('custom-calendar-container').classList.add('hidden');
            fetchSlotsForDate(dateVal);
        };

        // Select Date handler
        window.selectDeliveryDate = function(type) {
            const todayBtn = document.getElementById('date-btn-today');
            const tomorrowBtn = document.getElementById('date-btn-tomorrow');
            const customBtn = document.getElementById('date-btn-custom');
            
            const formatDateISO = (d) => {
                const year = d.getFullYear();
                const month = String(d.getMonth() + 1).padStart(2, '0');
                const day = String(d.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            };
            
            let selectedDateStr = '';
            
            todayBtn.className = inactiveBtnClass;
            tomorrowBtn.className = inactiveBtnClass;
            customBtn.className = inactiveBtnClass;
            
            if (type === 'today') {
                todayBtn.className = activeBtnClass;
                selectedDateStr = formatDateISO(todayDateObj);
                document.getElementById('label-date-custom').innerText = 'Seçiniz';
            } else if (type === 'tomorrow') {
                tomorrowBtn.className = activeBtnClass;
                selectedDateStr = formatDateISO(tomorrowDateObj);
                document.getElementById('label-date-custom').innerText = 'Seçiniz';
            }
            
            document.getElementById('hidden-delivery-date').value = selectedDateStr;
            document.getElementById('custom-calendar-container').classList.add('hidden');
            fetchSlotsForDate(selectedDateStr);
        };

        // Fetch Slots helper
        function fetchSlotsForDate(dateStr) {
            const slotSelect = document.getElementById('delivery-slot-select');
            slotSelect.innerHTML = '<option value="">Yükleniyor...</option>';
            document.getElementById('hidden-delivery-slot').value = '';
            
            fetch(`/teslimat-saatleri?date=${dateStr}`)
                .then(response => response.json())
                .then(slots => {
                    slotSelect.innerHTML = '<option value="">Teslimat saati seçin</option>';
                    if (slots.length === 0) {
                        slotSelect.innerHTML = '<option value="">Bu tarihte müsait saat bulunamadı</option>';
                        updateWhatsAppLink();
                        return;
                    }
                    slots.forEach(slot => {
                        const opt = document.createElement('option');
                        opt.value = slot.name;
                        opt.textContent = slot.name + (slot.is_available ? '' : ' (Dolu/Kapalı)');
                        if (!slot.is_available) opt.disabled = true;
                        slotSelect.appendChild(opt);
                    });
                    updateWhatsAppLink();
                })
                .catch(err => {
                    console.error("Slots fetch error: ", err);
                    slotSelect.innerHTML = '<option value="">Hata oluştu</option>';
                    updateWhatsAppLink();
                });
        }

        // Time slot change listener
        document.getElementById('delivery-slot-select').addEventListener('change', function() {
            document.getElementById('hidden-delivery-slot').value = this.value;
            updateWhatsAppLink();
        });

        // Dynamically update WhatsApp Order link text & URL
        function updateWhatsAppLink() {
            const productTitle = "{{ $product->name }}";
            const productSku = "{{ $product->sku }}";
            
            // Get total price
            const qty = parseInt(document.getElementById('qty-input').value) || 1;
            const priceContainer = document.getElementById('display-price-container');
            const basePrice = parseFloat(priceContainer.getAttribute('data-base-price'));
            
            let optionsModifier = 0.00;
            document.querySelectorAll('.option-modifier').forEach(el => {
                if (el.tagName === 'SELECT') {
                    const selectedOpt = el.options[el.selectedIndex];
                    if (selectedOpt) {
                        optionsModifier += parseFloat(selectedOpt.getAttribute('data-price')) || 0;
                    }
                } else if (el.tagName === 'INPUT' && el.type === 'checkbox' && el.checked) {
                    optionsModifier += parseFloat(el.getAttribute('data-price')) || 0;
                }
            });
            
            let extraGiftsModifier = 0.00;
            document.querySelectorAll('.extra-gift-modifier').forEach(el => {
                if (el.type === 'checkbox' && el.checked) {
                    extraGiftsModifier += parseFloat(el.getAttribute('data-price')) || 0;
                }
            });
            
            const totalPrice = ((basePrice + optionsModifier) * qty) + extraGiftsModifier;
            const formattedPrice = totalPrice.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ₺';

            // Get scheduling data
            const district = document.getElementById('hidden-delivery-district').value;
            const neighborhood = document.getElementById('hidden-delivery-neighborhood').value;
            const date = document.getElementById('hidden-delivery-date').value;
            const slot = document.getElementById('hidden-delivery-slot').value;

            let message = `Merhaba, *${productTitle}* (Ürün Kodu: ${productSku}) ürünü için sipariş vermek istiyorum.\n\n`;
            message += `*Adet:* ${qty}\n`;
            message += `*Toplam Tutar:* ${formattedPrice}\n`;

            if (district && neighborhood) {
                message += `*Teslimat Adresi:* ${district} - ${neighborhood} Mahallesi\n`;
            }
            if (date) {
                const d = new Date(date);
                message += `*Teslimat Tarihi:* ${formatTurkishDate(d)}\n`;
            }
            if (slot) {
                message += `*Teslimat Saati:* ${slot}\n`;
            }

            const whatsappNumber = "{{ preg_replace('/[^0-9]/', '', $whatsapp) }}";
            const url = 'https://wa.me/' + whatsappNumber + '?text=' + encodeURIComponent(message);
            
            const whatsappBtn = document.getElementById('whatsapp-order-btn');
            if (whatsappBtn) {
                whatsappBtn.href = url;
            }
        }

        // Validate Form Submission (Sipariş Ver)
        document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
            const district = document.getElementById('hidden-delivery-district').value;
            const neighborhood = document.getElementById('hidden-delivery-neighborhood').value;
            const date = document.getElementById('hidden-delivery-date').value;
            const slot = document.getElementById('hidden-delivery-slot').value;

            if (!district || !neighborhood || !date || !slot) {
                e.preventDefault();
                alert('Lütfen teslimat adresi (ilçe, mahalle) ve teslimat tarihi/saatini seçiniz.');
                return false;
            }
        });

        // Validate WhatsApp Order Button click
        const whatsappBtn = document.getElementById('whatsapp-order-btn');
        if (whatsappBtn) {
            whatsappBtn.addEventListener('click', function(e) {
                const district = document.getElementById('hidden-delivery-district').value;
                const neighborhood = document.getElementById('hidden-delivery-neighborhood').value;
                const date = document.getElementById('hidden-delivery-date').value;
                const slot = document.getElementById('hidden-delivery-slot').value;

                if (!district || !neighborhood || !date || !slot) {
                    e.preventDefault();
                    alert('Lütfen teslimat adresi (ilçe, mahalle) ve teslimat tarihi/saatini seçiniz.');
                    return false;
                }
            });
        }

        // Hook WhatsApp update on quantity changes
        document.getElementById('qty-input').addEventListener('change', updateWhatsAppLink);
    </script>
@endsection
