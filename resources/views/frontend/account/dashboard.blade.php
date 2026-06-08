@extends('frontend.layouts.app')

@section('content')
    <div class="py-12 bg-rose-50/20 min-h-[80vh]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="mb-8">
                <h1 class="text-3xl font-extrabold text-slate-900 font-serif">Hesabım</h1>
                <p class="text-slate-500 text-sm mt-1">Hoş geldiniz, {{ $customer->full_name }}. Hesabınızı buradan yönetebilirsiniz.</p>
            </div>

            @if(session('success'))
                <div class="mb-6 bg-emerald-50 border border-emerald-100 text-emerald-800 text-sm font-semibold p-4 rounded-xl flex items-center justify-between">
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 bg-rose-50 border border-rose-100 text-rose-800 text-sm font-semibold p-4 rounded-xl space-y-1">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                
                <!-- Sidebar Navigation -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-1">
                        <a href="{{ route('customer.account', ['tab' => 'overview']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition {{ $activeTab === 'overview' ? 'bg-rose-50 text-rose-600' : 'text-slate-600 hover:bg-slate-50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                            Genel Bakış
                        </a>
                        
                        <a href="{{ route('customer.account', ['tab' => 'orders']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition {{ $activeTab === 'orders' ? 'bg-rose-50 text-rose-600' : 'text-slate-600 hover:bg-slate-50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            Siparişlerim
                        </a>
                        
                        <a href="{{ route('customer.account', ['tab' => 'addresses']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition {{ $activeTab === 'addresses' ? 'bg-rose-50 text-rose-600' : 'text-slate-600 hover:bg-slate-50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            Adreslerim
                        </a>
                        
                        <a href="{{ route('customer.account', ['tab' => 'profile']) }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold transition {{ $activeTab === 'profile' ? 'bg-rose-50 text-rose-600' : 'text-slate-600 hover:bg-slate-50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            Profil ve Şifre
                        </a>

                        <div class="border-t border-slate-100 my-2 pt-2">
                            <form action="{{ route('customer.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-bold text-rose-600 hover:bg-rose-50 transition text-left">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
                                    </svg>
                                    Çıkış Yap
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Main Section Content -->
                <div class="lg:col-span-3">
                    
                    <!-- 1. OVERVIEW TAB -->
                    @if($activeTab === 'overview')
                        <div class="space-y-6">
                            <!-- Stats Cards -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Toplam Sipariş</span>
                                        <h3 class="text-3xl font-extrabold text-slate-800 mt-2">{{ $orders->count() }}</h3>
                                    </div>
                                    <div class="p-3.5 bg-rose-50 text-rose-600 rounded-2xl">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Kayıtlı Adres</span>
                                        <h3 class="text-3xl font-extrabold text-slate-800 mt-2">{{ $addresses->count() }}</h3>
                                    </div>
                                    <div class="p-3.5 bg-rose-50 text-rose-600 rounded-2xl">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                        </svg>
                                    </div>
                                </div>
                                
                                <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm flex items-center justify-between">
                                    <div>
                                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Üyelik Tarihi</span>
                                        <h3 class="text-lg font-extrabold text-slate-800 mt-3">{{ $customer->created_at->format('d.m.Y') }}</h3>
                                    </div>
                                    <div class="p-3.5 bg-rose-50 text-rose-600 rounded-2xl">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Orders -->
                            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-4">
                                <div class="flex justify-between items-center pb-3 border-b border-slate-50">
                                    <h3 class="font-serif text-lg font-bold text-slate-800">Son Siparişler</h3>
                                    <a href="{{ route('customer.account', ['tab' => 'orders']) }}" class="text-xs font-bold text-rose-600 hover:underline">Tümünü Gör</a>
                                </div>

                                @if($orders->count() > 0)
                                    <div class="divide-y divide-slate-50">
                                        @foreach($orders->take(3) as $order)
                                            <div class="py-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                                <div>
                                                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Kod: {{ $order->order_number }}</span>
                                                    <h4 class="font-bold text-slate-700 text-sm mt-0.5">
                                                        {{ $order->items->first()->product_name ?? 'Ürün' }} 
                                                        @if($order->items->count() > 1) 
                                                            <span class="text-slate-400 font-normal">ve {{ $order->items->count() - 1 }} diğer ürün</span>
                                                        @endif
                                                    </h4>
                                                    <p class="text-xs text-slate-500 mt-1">{{ $order->created_at->format('d.m.Y H:i') }}</p>
                                                </div>
                                                <div class="flex items-center gap-4">
                                                    <span class="text-sm font-extrabold text-slate-800">₺{{ number_format($order->total, 2) }}</span>
                                                    <a href="{{ route('tracking', ['order_number' => $order->order_number]) }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition">Detay ve Takip</a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500 py-4 text-center">Henüz bir siparişiniz bulunmuyor.</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- 2. ORDERS TAB -->
                    @if($activeTab === 'orders')
                        <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-6">
                            <div>
                                <h3 class="font-serif text-xl font-bold text-slate-800">Sipariş Geçmişim</h3>
                                <p class="text-xs text-slate-500 mt-1">Verdiğiniz tüm siparişleri ve teslimat durumlarını buradan inceleyebilirsiniz.</p>
                            </div>

                            @if($orders->count() > 0)
                                @php
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
                                @endphp

                                <div class="divide-y divide-slate-100">
                                    @foreach($orders as $order)
                                        @php
                                            $currentStatus = $statusMap[$order->status] ?? ['title' => $order->status, 'color' => 'bg-slate-50 text-slate-600 border-slate-150'];
                                        @endphp
                                        <div class="py-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                                            <div class="space-y-2">
                                                <div class="flex items-center gap-3">
                                                    <span class="text-[10px] font-black bg-rose-50 text-rose-600 border border-rose-100 px-2 py-0.5 rounded-full tracking-wider uppercase">
                                                        {{ $order->order_number }}
                                                    </span>
                                                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wider {{ $currentStatus['color'] }}">
                                                        {{ $currentStatus['title'] }}
                                                    </span>
                                                </div>
                                                
                                                <div class="space-y-1">
                                                    @foreach($order->items as $item)
                                                        <p class="font-bold text-slate-700 text-sm">
                                                            {{ $item->product_name }} 
                                                            <span class="text-slate-400 font-normal">x{{ $item->quantity }}</span>
                                                        </p>
                                                    @endforeach
                                                </div>
                                                
                                                <p class="text-xs text-slate-400 font-medium">Sipariş Tarihi: {{ $order->created_at->format('d.m.Y H:i') }}</p>
                                            </div>

                                            <div class="flex flex-col items-end gap-3 w-full md:w-auto">
                                                <span class="text-lg font-black text-slate-800">₺{{ number_format($order->total, 2) }}</span>
                                                <a href="{{ route('tracking', ['order_number' => $order->order_number]) }}" class="w-full md:w-auto text-center px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl transition shadow-sm">
                                                    Detayları Görüntüle
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-12 text-center text-slate-500">
                                    <p class="text-sm">Henüz kayıtlı bir siparişiniz bulunmuyor.</p>
                                    <a href="/" class="inline-block mt-4 text-xs font-bold text-rose-600 hover:underline">Alışverişe Başla &rarr;</a>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- 3. ADDRESSES TAB -->
                    @if($activeTab === 'addresses')
                        <div class="space-y-6">
                            
                            <!-- Address List -->
                            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-6">
                                <div>
                                    <h3 class="font-serif text-xl font-bold text-slate-800">Kayıtlı Adreslerim</h3>
                                    <p class="text-xs text-slate-500 mt-1">Ödeme sayfasında hızlı seçim yapabilmek için adreslerinizi kaydedin.</p>
                                </div>

                                @if($addresses->count() > 0)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        @foreach($addresses as $addr)
                                            <div class="border border-slate-100 rounded-2xl p-5 shadow-inner bg-slate-50/50 flex flex-col justify-between">
                                                <div>
                                                    <div class="flex justify-between items-start w-full">
                                                        <h4 class="font-bold text-slate-800 text-sm tracking-wide">{{ $addr->title }}</h4>
                                                        <div class="flex items-center gap-1.5">
                                                            <!-- Edit Button -->
                                                            <button type="button" 
                                                                class="edit-address-btn text-slate-400 hover:text-rose-600 transition p-1" 
                                                                title="Düzenle"
                                                                data-id="{{ $addr->id }}"
                                                                data-title="{{ $addr->title }}"
                                                                data-first-name="{{ $addr->first_name }}"
                                                                data-last-name="{{ $addr->last_name }}"
                                                                data-phone="{{ $addr->phone }}"
                                                                data-company="{{ $addr->company }}"
                                                                data-address-line="{{ $addr->address_line }}"
                                                                data-district="{{ $addr->district }}"
                                                                data-neighborhood="{{ $addr->neighborhood }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L6.83 20.84a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                                </svg>
                                                            </button>

                                                            <!-- Delete Form -->
                                                            <form action="{{ route('customer.account.delete_address', $addr->id) }}" method="POST" onsubmit="return confirm('Bu adresi silmek istediğinize emin misiniz?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="text-slate-400 hover:text-rose-600 transition p-1" title="Sil">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                                    </svg>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                    <p class="text-xs font-bold text-slate-700 mt-2">{{ $addr->full_name }}</p>
                                                    @if($addr->company)
                                                        <p class="text-xs text-slate-500 font-medium">{{ $addr->company }}</p>
                                                    @endif
                                                    <p class="text-xs text-slate-600 mt-2 leading-relaxed">{{ $addr->address_line }}</p>
                                                    <p class="text-xs text-slate-500 font-bold mt-1">{{ $addr->neighborhood }}, {{ $addr->district }} / {{ $addr->city }}</p>
                                                </div>
                                                <p class="text-xs text-slate-400 mt-4 font-semibold">Tel: {{ $addr->phone }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-slate-500 py-4 text-center">Henüz kayıtlı bir adresiniz bulunmuyor.</p>
                                @endif
                            </div>

                            <!-- Add/Edit Address Form -->
                            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-6" id="address-form-container">
                                <h3 class="font-serif text-lg font-bold text-slate-800" id="address-form-title">{{ old('_method') === 'PUT' ? 'Adresi Düzenle' : 'Yeni Adres Ekle' }}</h3>
                                <form action="{{ old('_method') === 'PUT' ? route('customer.account.update_address', old('address_id', 0)) : route('customer.account.add_address') }}" method="POST" id="address-form" class="space-y-4">
                                    @csrf
                                    <input type="hidden" name="_method" id="address-form-method" value="PUT" {{ old('_method') === 'PUT' ? '' : 'disabled' }}>
                                    <input type="hidden" name="address_id" id="address-id" value="{{ old('address_id') }}" {{ old('_method') === 'PUT' ? '' : 'disabled' }}>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label for="title" class="block text-xs font-bold text-slate-700 uppercase mb-2">Adres Başlığı (örn: Ev, İş)</label>
                                            <input type="text" name="title" id="title" value="{{ old('title') }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                        </div>
                                        <div>
                                            <label for="company" class="block text-xs font-bold text-slate-700 uppercase mb-2">Firma Adı (İsteğe bağlı)</label>
                                            <input type="text" name="company" id="company" value="{{ old('company') }}" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="addr_first_name" class="block text-xs font-bold text-slate-700 uppercase mb-2">Alıcı Adı</label>
                                            <input type="text" name="first_name" id="addr_first_name" value="{{ old('first_name') }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                        </div>
                                        <div>
                                            <label for="addr_last_name" class="block text-xs font-bold text-slate-700 uppercase mb-2">Alıcı Soyadı</label>
                                            <input type="text" name="last_name" id="addr_last_name" value="{{ old('last_name') }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                        </div>
                                        <div>
                                            <label for="addr_phone" class="block text-xs font-bold text-slate-700 uppercase mb-2">Alıcı Telefonu</label>
                                            <input type="text" name="phone" id="addr_phone" value="{{ old('phone') }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label for="city" class="block text-xs font-bold text-slate-700 uppercase mb-2">Şehir</label>
                                            <input type="text" name="city" id="city" value="Diyarbakır" readonly required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm bg-slate-50 font-bold text-slate-500">
                                        </div>
                                        <div>
                                            <label for="district" class="block text-xs font-bold text-slate-700 uppercase mb-2">İlçe</label>
                                            <input type="text" name="district" id="district" value="{{ old('district') }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                        </div>
                                        <div>
                                            <label for="neighborhood" class="block text-xs font-bold text-slate-700 uppercase mb-2">Mahalle</label>
                                            <input type="text" name="neighborhood" id="neighborhood" value="{{ old('neighborhood') }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="address_line" class="block text-xs font-bold text-slate-700 uppercase mb-2">Adres Satırı</label>
                                        <textarea name="address_line" id="address_line" rows="3" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">{{ old('address_line') }}</textarea>
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <button type="submit" id="address-form-submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-6 py-3 rounded-xl transition duration-300 shadow-md">Adresi Kaydet</button>
                                        <button type="button" id="address-form-cancel" class="{{ old('_method') === 'PUT' ? '' : 'hidden' }} bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-6 py-3 rounded-xl transition duration-300">Vazgeç</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif

                    <!-- 4. PROFILE TAB -->
                    @if($activeTab === 'profile')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            
                            <!-- Profile Info Form -->
                            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-4">
                                <h3 class="font-serif text-lg font-bold text-slate-800">Kişisel Bilgiler</h3>
                                
                                <form action="{{ route('customer.account.update_profile') }}" method="POST" class="space-y-4">
                                    @csrf
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label for="prof_first_name" class="block text-xs font-bold text-slate-700 uppercase mb-2">Ad</label>
                                            <input type="text" name="first_name" id="prof_first_name" value="{{ $customer->first_name }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                        </div>
                                        <div>
                                            <label for="prof_last_name" class="block text-xs font-bold text-slate-700 uppercase mb-2">Soyad</label>
                                            <input type="text" name="last_name" id="prof_last_name" value="{{ $customer->last_name }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                        </div>
                                    </div>

                                    <div>
                                        <label for="prof_email" class="block text-xs font-bold text-slate-700 uppercase mb-2">E-posta Adresi</label>
                                        <input type="email" name="email" id="prof_email" value="{{ $customer->email }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                    </div>

                                    <div>
                                        <label for="prof_phone" class="block text-xs font-bold text-slate-700 uppercase mb-2">Telefon</label>
                                        <input type="text" name="phone" id="prof_phone" value="{{ $customer->phone }}" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                    </div>

                                    <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-6 py-3 rounded-xl transition duration-300 shadow-md">Bilgileri Güncelle</button>
                                </form>
                            </div>

                            <!-- Password Update Form -->
                            <div class="bg-white rounded-2xl border border-slate-100 p-6 shadow-sm space-y-4">
                                <h3 class="font-serif text-lg font-bold text-slate-800">Şifre Değiştir</h3>
                                
                                <form action="{{ route('customer.account.update_password') }}" method="POST" class="space-y-4">
                                    @csrf
                                    
                                    <div>
                                        <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase mb-2">Mevcut Şifre</label>
                                        <input type="password" name="current_password" id="current_password" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                    </div>

                                    <div>
                                        <label for="new_password" class="block text-xs font-bold text-slate-700 uppercase mb-2">Yeni Şifre</label>
                                        <input type="password" name="password" id="new_password" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                    </div>

                                    <div>
                                        <label for="new_password_conf" class="block text-xs font-bold text-slate-700 uppercase mb-2">Yeni Şifre Tekrarı</label>
                                        <input type="password" name="password_confirmation" id="new_password_conf" required class="w-full px-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-rose-500/20 focus:border-rose-500 font-medium text-slate-800 transition">
                                    </div>

                                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold px-6 py-3 rounded-xl transition duration-300 shadow-md">Şifreyi Güncelle</button>
                                </form>
                            </div>

                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editButtons = document.querySelectorAll('.edit-address-btn');
            const addressForm = document.getElementById('address-form');
            const formTitle = document.getElementById('address-form-title');
            const formMethod = document.getElementById('address-form-method');
            const addressIdInput = document.getElementById('address-id');
            const cancelButton = document.getElementById('address-form-cancel');
            const formContainer = document.getElementById('address-form-container');

            const originalAction = "{{ route('customer.account.add_address') }}";

            // Fields
            const titleInput = document.getElementById('title');
            const companyInput = document.getElementById('company');
            const firstNameInput = document.getElementById('addr_first_name');
            const lastNameInput = document.getElementById('addr_last_name');
            const phoneInput = document.getElementById('addr_phone');
            const districtInput = document.getElementById('district');
            const neighborhoodInput = document.getElementById('neighborhood');
            const addressLineInput = document.getElementById('address_line');

            editButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const title = this.getAttribute('data-title');
                    const firstName = this.getAttribute('data-first-name');
                    const lastName = this.getAttribute('data-last-name');
                    const phone = this.getAttribute('data-phone');
                    const company = this.getAttribute('data-company') || '';
                    const addressLine = this.getAttribute('data-address-line');
                    const district = this.getAttribute('data-district');
                    const neighborhood = this.getAttribute('data-neighborhood');

                    // Populate form
                    titleInput.value = title;
                    companyInput.value = company;
                    firstNameInput.value = firstName;
                    lastNameInput.value = lastName;
                    phoneInput.value = phone;
                    districtInput.value = district;
                    neighborhoodInput.value = neighborhood;
                    addressLineInput.value = addressLine;

                    // Set update route and enable PUT & address id
                    addressForm.action = `/hesabim/adres-guncelle/${id}`;
                    formMethod.removeAttribute('disabled');
                    addressIdInput.value = id;
                    addressIdInput.removeAttribute('disabled');

                    // Update UI headers/buttons
                    formTitle.textContent = 'Adresi Düzenle';
                    cancelButton.classList.remove('hidden');

                    // Scroll to form
                    formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                });
            });

            cancelButton.addEventListener('click', function() {
                // Reset form to default values
                addressForm.reset();

                // Restore action and disable PUT method / address id
                addressForm.action = originalAction;
                formMethod.setAttribute('disabled', 'true');
                addressIdInput.value = '';
                addressIdInput.setAttribute('disabled', 'true');

                // Reset UI headers/buttons
                formTitle.textContent = 'Yeni Adres Ekle';
                cancelButton.classList.add('hidden');
            });

            // If we reloaded with validation errors during an edit, scroll to form
            @if(old('_method') === 'PUT')
                formContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            @endif
        });
    </script>
@endsection
