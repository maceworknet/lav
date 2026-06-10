@extends('frontend.layouts.app')

@section('content')
    <div class="py-12 bg-rose-50/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <h1 class="text-3xl font-extrabold text-slate-900 font-serif mb-10">Güvenli Ödeme</h1>

            <!-- Error Notifications -->
            @if(session('error'))
                <div class="mb-8 p-4 bg-rose-50 text-rose-800 rounded-xl border border-rose-100 text-sm font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('checkout.process') }}" method="POST" id="checkout-form">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
                    
                    <!-- Left: Forms (8 Cols) -->
                    <div class="lg:col-span-8 space-y-8">
                        
                        <!-- 1. Sender Information -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
                            <div class="flex items-center gap-3 border-b border-slate-50 pb-3">
                                <span class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 text-xs font-black flex items-center justify-center">1</span>
                                <h3 class="font-serif text-lg font-bold text-slate-800">Gönderici Bilgileri</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Ad Soyad <span class="text-rose-600">*</span></label>
                                    <input type="text" name="sender_name" value="{{ old('sender_name', auth('customer')->check() ? auth('customer')->user()->first_name . ' ' . auth('customer')->user()->last_name : '') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Telefon <span class="text-rose-600">*</span></label>
                                    <input type="tel" name="sender_phone" placeholder="05xx xxx xx xx" value="{{ old('sender_phone', auth('customer')->check() ? auth('customer')->user()->phone : '') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>
                                <div class="space-y-1.5 sm:col-span-2">
                                    <label class="text-xs font-bold text-slate-500 uppercase">E-Posta <span class="text-rose-600">*</span></label>
                                    <input type="email" name="sender_email" placeholder="example@email.com" value="{{ old('sender_email', auth('customer')->check() ? auth('customer')->user()->email : '') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Recipient & Delivery Details -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
                            <div class="flex items-center gap-3 border-b border-slate-50 pb-3">
                                <span class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 text-xs font-black flex items-center justify-center">2</span>
                                <h3 class="font-serif text-lg font-bold text-slate-800">Alıcı ve Teslimat Bilgileri</h3>
                            </div>
                            
                            @if(auth('customer')->check() && auth('customer')->user()->addresses->count() > 0)
                                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4 mb-4">
                                    <label for="saved-address-select" class="block text-xs font-bold text-slate-500 uppercase mb-2">Kayıtlı Adreslerimden Seç</label>
                                    <select id="saved-address-select" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-rose-500">
                                        <option value="">-- Yeni bir adres girin veya listeden seçin --</option>
                                        @foreach(auth('customer')->user()->addresses as $addr)
                                            <option value="{{ $addr->id }}" 
                                                data-first-name="{{ $addr->first_name }}"
                                                data-last-name="{{ $addr->last_name }}"
                                                data-phone="{{ $addr->phone }}"
                                                data-city="{{ $addr->city }}"
                                                data-district="{{ $addr->district }}"
                                                data-neighborhood="{{ $addr->neighborhood }}"
                                                data-address-line="{{ $addr->address_line }}"
                                                data-company="{{ $addr->company }}">
                                                {{ $addr->title }} ({{ $addr->first_name }} {{ $addr->last_name }} - {{ $addr->district }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Alıcı Ad Soyad <span class="text-rose-600">*</span></label>
                                    <input type="text" name="recipient_name" id="recipient_name" value="{{ old('recipient_name') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Alıcı Telefon <span class="text-rose-600">*</span></label>
                                    <input type="tel" name="recipient_phone" id="recipient_phone" placeholder="05xx xxx xx xx" value="{{ old('recipient_phone') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>

                                <!-- Dynamic District / Neighborhood selections -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Teslimat İlçesi <span class="text-rose-600">*</span></label>
                                    <select name="recipient_district" id="district-select" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                        <option value="">Seçiniz</option>
                                        @foreach($deliveryZones as $zone)
                                            <option value="{{ $zone->district }}">{{ $zone->district }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Teslimat Mahallesi <span class="text-rose-600">*</span></label>
                                    <select name="recipient_neighborhood" id="neighborhood-select" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-rose-500" required disabled>
                                        <option value="">İlçe seçiniz</option>
                                    </select>
                                </div>

                                <div class="space-y-1.5 sm:col-span-2">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Açık Adres <span class="text-rose-600">*</span></label>
                                    <textarea name="recipient_address" id="recipient_address" rows="2" placeholder="Bina adı, daire no, kat, tarif..." class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500" required>{{ old('recipient_address') }}</textarea>
                                </div>

                                <!-- Date & Time Scheduler -->
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Teslimat Tarihi <span class="text-rose-600">*</span></label>
                                    <input type="date" name="delivery_date" id="delivery-date" min="{{ date('Y-m-d') }}" value="{{ old('delivery_date', date('Y-m-d')) }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>

                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Teslimat Saat Aralığı <span class="text-rose-600">*</span></label>
                                    <select name="delivery_slot" id="delivery-slot" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm font-semibold focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                        @foreach($initialSlots as $slot)
                                            <option value="{{ $slot['name'] }}" {{ !$slot['is_available'] ? 'disabled' : '' }}>
                                                {{ $slot['name'] }} @if(!$slot['is_available']) (Dolu/Kapalı) @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Message Card Note Details -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
                            <div class="flex items-center gap-3 border-b border-slate-50 pb-3">
                                <span class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 text-xs font-black flex items-center justify-center">3</span>
                                <h3 class="font-serif text-lg font-bold text-slate-800">Kart Notu Detayları</h3>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-1.5 sm:col-span-2">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Kart Notu Mesajı</label>
                                    <textarea name="card_note" rows="2" placeholder="Sevdiklerinize göndermek istediğiniz mesaj..." class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500">{{ old('card_note') }}</textarea>
                                </div>
                                <div class="space-y-1.5 sm:col-span-2">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Kart Notu İmzası / İsim</label>
                                    <input type="text" name="card_note_signature" placeholder="İsminizin kart üzerinde görünmesini istiyorsanız yazın (boş bırakılırsa isimsiz gider)" value="{{ old('card_note_signature') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500">
                                </div>
                            </div>
                        </div>

                        <!-- 4. Billing & Invoicing Details -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
                            <div class="flex items-center gap-3 border-b border-slate-50 pb-3">
                                <span class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 text-xs font-black flex items-center justify-center">4</span>
                                <h3 class="font-serif text-lg font-bold text-slate-800">Fatura Türü</h3>
                            </div>
                            <div class="space-y-3">
                                <div class="flex gap-4">
                                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-sm">
                                        <input type="radio" name="invoice_type" value="personal" checked onclick="toggleInvoiceFields('personal')" class="text-rose-600 focus:ring-rose-500">
                                        Bireysel Fatura
                                    </label>
                                    <label class="flex items-center gap-2 cursor-pointer font-semibold text-sm">
                                        <input type="radio" name="invoice_type" value="corporate" onclick="toggleInvoiceFields('corporate')" class="text-rose-600 focus:ring-rose-500">
                                        Kurumsal Fatura
                                    </label>
                                </div>
                                
                                <!-- Corporate fields (hidden initially) -->
                                <div id="corporate-fields" class="hidden grid grid-cols-1 sm:grid-cols-3 gap-4 border-t border-slate-50 pt-4">
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-500 uppercase">Şirket Unvanı</label>
                                        <input type="text" name="company_name" value="{{ old('company_name') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-500 uppercase">Vergi Dairesi</label>
                                        <input type="text" name="tax_office" value="{{ old('tax_office') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500">
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-xs font-bold text-slate-500 uppercase">Vergi Numarası</label>
                                        <input type="text" name="tax_number" value="{{ old('tax_number') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 5. Payment details (iyzico Credit Card) -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-4">
                            <div class="flex items-center gap-3 border-b border-slate-50 pb-3">
                                <span class="w-6 h-6 rounded-full bg-rose-50 text-rose-600 text-xs font-black flex items-center justify-center">5</span>
                                <h3 class="font-serif text-lg font-bold text-slate-800">Kart ile Ödeme</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                                <div class="space-y-1.5 sm:col-span-4">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Kart Üzerindeki İsim <span class="text-rose-600">*</span></label>
                                    <input type="text" name="card_holder_name" value="{{ old('card_holder_name') }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>
                                <div class="space-y-1.5 sm:col-span-4">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Kart Numarası <span class="text-rose-600">*</span></label>
                                    <input type="text" name="card_number" id="card-number-input" placeholder="0000 0000 0000 0000" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Ay <span class="text-rose-600">*</span></label>
                                    <input type="text" name="expire_month" placeholder="AA" maxlength="2" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-center focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-xs font-bold text-slate-500 uppercase">Yıl <span class="text-rose-600">*</span></label>
                                    <input type="text" name="expire_year" placeholder="YY" maxlength="2" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-center focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>
                                <div class="space-y-1.5 sm:col-span-2">
                                    <label class="text-xs font-bold text-slate-500 uppercase">CVC Kodu <span class="text-rose-600">*</span></label>
                                    <input type="text" name="cvc" placeholder="123" maxlength="4" class="w-full px-4 py-3 border border-slate-200 rounded-xl text-sm text-center focus:outline-none focus:ring-1 focus:ring-rose-500" required>
                                </div>
                            </div>

                            <div class="mt-6 pt-4 border-t border-slate-50 text-xs text-slate-400 space-y-4">
                                <label class="flex items-start gap-3 cursor-pointer">
                                    <input type="checkbox" name="agreements" class="rounded text-rose-600 focus:ring-rose-500 mt-0.5" required>
                                    <span>
                                        <a href="/sayfa/kvkk-aydinlatma-metni" target="_blank" class="underline text-slate-600 hover:text-rose-600">KVKK Metni</a> ve 
                                        <a href="/sayfa/mesafeli-satis-sozlesmesi" target="_blank" class="underline text-slate-600 hover:text-rose-600">Mesafeli Satış Sözleşmesi</a> şartlarını okudum, kabul ediyorum.
                                    </span>
                                </label>
                            </div>
                        </div>

                    </div>

                    <!-- Right: Summary (4 Cols) -->
                    <div class="lg:col-span-4 space-y-6">
                        
                        <!-- Order Summary Card -->
                        <div class="bg-white rounded-2xl p-6 border border-slate-100 shadow-sm space-y-5">
                            <h3 class="font-serif text-lg font-bold text-slate-800">Sipariş Detayı</h3>
                            
                            <div class="max-h-60 overflow-y-auto divide-y divide-slate-50 pr-2">
                                @foreach($cart->items as $item)
                                    <div class="py-3 text-xs border-b border-slate-50 last:border-b-0">
                                        <div class="flex justify-between gap-4">
                                            <div>
                                                <span class="font-bold text-slate-800">{{ $item->product->name }}</span>
                                                <span class="text-slate-400 font-medium ml-1">x{{ $item->quantity }}</span>
                                            </div>
                                            @php
                                                $unitPrice = (float)($item->product->discount_price ?? $item->product->price);
                                                $optionsModifier = 0.00;
                                                if(is_array($item->options)) {
                                                    foreach($item->options as $opt) {
                                                        $optionsModifier += (float)($opt['price_modifier'] ?? 0);
                                                    }
                                                }
                                                $giftsTotal = 0.00;
                                                if ($item->extraGifts) {
                                                    foreach ($item->extraGifts as $gift) {
                                                        $giftsTotal += (float)$gift->price_snapshot * $gift->quantity;
                                                    }
                                                }
                                                $itemTotal = (($unitPrice + $optionsModifier) * $item->quantity) + $giftsTotal;
                                            @endphp
                                            <span class="font-bold text-slate-700">₺{{ number_format($itemTotal, 2) }}</span>
                                        </div>
                                        
                                        <!-- Selected Options -->
                                        @if(is_array($item->options) && count($item->options) > 0)
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach($item->options as $opt)
                                                    <span class="inline-flex items-center text-[9px] font-bold bg-rose-50 text-rose-600 px-1.5 py-0.25 rounded-full border border-rose-100/50">
                                                        {{ $opt['label'] }} @if(($opt['price_modifier'] ?? 0) > 0) (+ ₺{{ number_format($opt['price_modifier'], 2) }}) @endif
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <!-- Selected Extra Gifts -->
                                        @if($item->extraGifts && $item->extraGifts->count() > 0)
                                            <div class="mt-1 flex flex-wrap gap-1">
                                                @foreach($item->extraGifts as $gift)
                                                    <span class="inline-flex items-center text-[9px] font-bold bg-rose-50/50 text-rose-700 px-1.5 py-0.25 rounded-md border border-rose-100">
                                                        🎁 {{ $gift->name_snapshot }} (+ ₺{{ number_format($gift->price_snapshot, 2) }})
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <div class="border-t border-slate-100 pt-5 space-y-3 text-sm text-slate-600">
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
                                    <span class="font-bold text-slate-800" id="delivery-fee-display">₺0.00</span>
                                </div>
                            </div>

                            <div class="border-t border-slate-100 pt-5 flex justify-between items-baseline">
                                <span class="text-base font-bold text-slate-800 font-serif">Genel Toplam</span>
                                <span class="text-2xl font-black text-rose-600 font-serif" id="grand-total-display" data-subtotal-net="{{ $totals['subtotal'] - $totals['discount_amount'] }}">
                                    ₺{{ number_format($totals['total'], 2) }}
                                </span>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="block w-full bg-rose-600 hover:bg-rose-700 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-rose-100 transition duration-300">
                                    Ödemeyi Tamamla
                                </button>
                            </div>
                        </div>

                    </div>

                </div>

            </form>
        </div>
    </div>

    <!-- Nested districts data mapping for dynamic JS updates -->
    <script>
        // Dynamic neighborhood data loaded from controller
        const zonesData = @json($deliveryZones);
        const subtotalNet = parseFloat(document.getElementById('grand-total-display').getAttribute('data-subtotal-net'));

        const districtSelect = document.getElementById('district-select');
        const neighborhoodSelect = document.getElementById('neighborhood-select');
        const deliveryFeeDisplay = document.getElementById('delivery-fee-display');
        const grandTotalDisplay = document.getElementById('grand-total-display');

        // Toggle billing fields
        function toggleInvoiceFields(type) {
            const fields = document.getElementById('corporate-fields');
            if (type === 'corporate') {
                fields.classList.remove('hidden');
                document.querySelectorAll('#corporate-fields input').forEach(input => input.required = true);
            } else {
                fields.classList.add('hidden');
                document.querySelectorAll('#corporate-fields input').forEach(input => {
                    input.required = false;
                    input.value = '';
                });
            }
        }

        // Space auto formatting for Credit Card Number
        document.getElementById('card-number-input').addEventListener('input', function (e) {
            e.target.value = e.target.value.replace(/[^\d]/g, '').replace(/(.{4})/g, '$1 ').trim();
        });

        // Dynamic Slots loader on Date Change
        document.getElementById('delivery-date').addEventListener('change', function(e) {
            const date = e.target.value;
            const slotSelect = document.getElementById('delivery-slot');

            // Reset and show loading state
            slotSelect.innerHTML = '<option value="">Yükleniyor...</option>';

            fetch(`/teslimat-saatleri?date=${date}`)
                .then(response => response.json())
                .then(slots => {
                    slotSelect.innerHTML = '';
                    if (slots.length === 0) {
                        slotSelect.innerHTML = '<option value="">Bu tarihte müsait saat bulunamadı</option>';
                        return;
                    }
                    slots.forEach(slot => {
                        const opt = document.createElement('option');
                        opt.value = slot.name;
                        opt.textContent = slot.name + (slot.is_available ? '' : ' (Dolu/Kapalı)');
                        if (!slot.is_available) opt.disabled = true;
                        slotSelect.appendChild(opt);
                    });
                })
                .catch(err => {
                    console.error("Slots loading failure: ", err);
                    slotSelect.innerHTML = '<option value="">Hata oluştu</option>';
                });
        });

        // Districts change listener
        districtSelect.addEventListener('change', function() {
            const selectedDistrict = this.value;
            neighborhoodSelect.innerHTML = '<option value="">Seçiniz</option>';
            neighborhoodSelect.disabled = true;
            
            // Reset fee
            updateDeliveryFee(0, null, null);

            if (!selectedDistrict) return;

            const zone = zonesData.find(z => z.district === selectedDistrict);
            if (zone && zone.neighborhoods) {
                neighborhoodSelect.disabled = false;
                zone.neighborhoods.forEach(neigh => {
                    const opt = document.createElement('option');
                    opt.value = neigh.name;
                    opt.textContent = neigh.name;
                    opt.setAttribute('data-id', neigh.id);
                    opt.setAttribute('data-fee', neigh.delivery_fee);
                    opt.setAttribute('data-free-limit', neigh.free_delivery_threshold);
                    neighborhoodSelect.appendChild(opt);
                });
            }
        });

        // Neighborhoods change listener
        neighborhoodSelect.addEventListener('change', function() {
            const selectedOpt = this.options[this.selectedIndex];
            if (!selectedOpt || !this.value) {
                updateDeliveryFee(0, null, null);
                return;
            }

            const neighborhoodId = selectedOpt.getAttribute('data-id');
            if (!neighborhoodId) {
                // Fallback static calculation if no ID
                const fee = parseFloat(selectedOpt.getAttribute('data-fee')) || 0;
                const freeLimit = parseFloat(selectedOpt.getAttribute('data-free-limit')) || null;

                let deliveryFee = fee;
                if (freeLimit && subtotalNet >= freeLimit) {
                    deliveryFee = 0;
                }
                updateDeliveryFee(deliveryFee, null, null);
                return;
            }

            // Fetch calculated delivery fee details from backend
            fetch(`/api/calculate-delivery-fee?neighborhood_id=${neighborhoodId}`)
                .then(response => response.json())
                .then(data => {
                    const fee = parseFloat(data.fee) || 0;
                    const message = data.customer_message || null;
                    const campaignName = data.campaign_name || null;
                    updateDeliveryFee(fee, message, campaignName);
                })
                .catch(err => {
                    console.error("Delivery fee calculation failure: ", err);
                    const fee = parseFloat(selectedOpt.getAttribute('data-fee')) || 0;
                    updateDeliveryFee(fee, null, null);
                });
        });

        // Recalculates total
        function updateDeliveryFee(fee, campaignMessage, campaignName) {
            deliveryFeeDisplay.textContent = '₺' + fee.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const total = subtotalNet + fee;
            grandTotalDisplay.textContent = '₺' + total.toLocaleString('tr-TR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            let campaignBanner = document.getElementById('delivery-campaign-banner');
            if (!campaignBanner) {
                campaignBanner = document.createElement('div');
                campaignBanner.id = 'delivery-campaign-banner';
                campaignBanner.className = 'mt-3 p-3 text-xs rounded-xl font-semibold transition-all duration-300';
                
                // Insert it inside the summary card, right before the checkout button wrapper
                const summaryCard = grandTotalDisplay.closest('.bg-white');
                const btnContainer = summaryCard.querySelector('.pt-2');
                if (btnContainer) {
                    summaryCard.insertBefore(campaignBanner, btnContainer);
                }
            }

            if (campaignMessage) {
                campaignBanner.style.display = 'block';
                if (campaignName) {
                    campaignBanner.className = 'mt-3 p-3 text-xs bg-emerald-50 text-emerald-800 border border-emerald-100 rounded-xl font-semibold';
                    campaignBanner.innerHTML = `🎉 <strong>${campaignName}</strong>: ${campaignMessage}`;
                } else {
                    campaignBanner.className = 'mt-3 p-3 text-xs bg-rose-50 text-rose-800 border border-rose-100 rounded-xl font-semibold';
                    campaignBanner.innerHTML = `💡 ${campaignMessage}`;
                }
            } else {
                campaignBanner.style.display = 'none';
            }
        }

        // Saved Address selection listener
        const savedAddressSelect = document.getElementById('saved-address-select');
        if (savedAddressSelect) {
            savedAddressSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (!opt || !this.value) {
                    // Reset fields
                    document.getElementById('recipient_name').value = '';
                    document.getElementById('recipient_phone').value = '';
                    document.getElementById('district-select').value = '';
                    document.getElementById('district-select').dispatchEvent(new Event('change'));
                    document.getElementById('recipient_address').value = '';
                    return;
                }

                const firstName = opt.getAttribute('data-first-name') || '';
                const lastName = opt.getAttribute('data-last-name') || '';
                const phone = opt.getAttribute('data-phone') || '';
                const district = opt.getAttribute('data-district') || '';
                const neighborhood = opt.getAttribute('data-neighborhood') || '';
                const addressLine = opt.getAttribute('data-address-line') || '';

                document.getElementById('recipient_name').value = firstName + ' ' + lastName;
                document.getElementById('recipient_phone').value = phone;
                document.getElementById('recipient_address').value = addressLine;
                
                // Select District
                const districtSelect = document.getElementById('district-select');
                districtSelect.value = district;
                
                // Trigger change to load neighborhoods
                districtSelect.dispatchEvent(new Event('change'));
                
                // Wait briefly for neighborhoods to populate, then select neighborhood
                setTimeout(() => {
                    const neighborhoodSelect = document.getElementById('neighborhood-select');
                    neighborhoodSelect.value = neighborhood;
                    neighborhoodSelect.dispatchEvent(new Event('change'));
                }, 100);
            });
        }

        // Prefill delivery details if stored in session
        window.addEventListener('DOMContentLoaded', () => {
            const prefDistrict = "{{ session('prefilled_delivery_district') }}";
            const prefNeighborhood = "{{ session('prefilled_delivery_neighborhood') }}";
            const prefDate = "{{ session('prefilled_delivery_date') }}";
            const prefSlot = "{{ session('prefilled_delivery_slot') }}";

            if (prefDistrict) {
                const districtSelect = document.getElementById('district-select');
                if (districtSelect) {
                    districtSelect.value = prefDistrict;
                    districtSelect.dispatchEvent(new Event('change'));

                    // Wait for neighborhoods to load
                    setTimeout(() => {
                        const neighborhoodSelect = document.getElementById('neighborhood-select');
                        if (neighborhoodSelect && prefNeighborhood) {
                            neighborhoodSelect.value = prefNeighborhood;
                            neighborhoodSelect.dispatchEvent(new Event('change'));
                        }
                    }, 300);
                }
            }

            if (prefDate) {
                const dateInput = document.getElementById('delivery-date');
                if (dateInput) {
                    dateInput.value = prefDate;
                    dateInput.dispatchEvent(new Event('change'));

                    // Wait for slots to load
                    setTimeout(() => {
                        const slotSelect = document.getElementById('delivery-slot');
                        if (slotSelect && prefSlot) {
                            slotSelect.value = prefSlot;
                        }
                    }, 800);
                }
            }
        });
    </script>
@endsection
