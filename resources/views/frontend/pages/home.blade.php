@extends('frontend.layouts.app')

@section('content')
    <!-- Panelden yönetilen dinamik sayfa blokları -->
    @include('frontend.partials.page_blocks')

    @if(!$page || $page->pageBlocks->count() === 0)
        <!-- Fallback if blocks are missing -->
        <div class="py-24 text-center">
            <h1 class="text-2xl font-bold">Mağaza Yapılandırılıyor</h1>
            <p class="text-slate-500 mt-2">Lütfen Filament panelden 'ana-sayfa' sluglı bir sayfa oluşturup blokları ekleyin.</p>
        </div>
    @endif
@endsection
