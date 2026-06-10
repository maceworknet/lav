@extends('frontend.layouts.app')

@section('content')
    @if(trim($page->content ?? '') !== '')
    <div class="py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4">

            <!-- Breadcrumbs -->
            <nav class="flex text-xs text-slate-400 font-semibold mb-6 uppercase tracking-wider gap-2">
                <a href="/" class="hover:text-rose-600 transition">Ana Sayfa</a>
                <span>/</span>
                <span class="text-slate-600">{{ $page->title }}</span>
            </nav>

            <h1 class="text-4xl font-extrabold text-slate-900 font-serif leading-tight border-b border-rose-100 pb-6 mb-8">
                {{ $page->title }}
            </h1>

            <div class="prose prose-rose max-w-none text-slate-600 leading-relaxed space-y-6 text-sm sm:text-base">
                {!! nl2br(e($page->content)) !!}
            </div>

        </div>
    </div>
    @endif

    <!-- Panelden yönetilen dinamik sayfa blokları -->
    @include('frontend.partials.page_blocks')

    @if(trim($page->content ?? '') === '' && $page->pageBlocks->count() === 0)
    <div class="py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4">
            <h1 class="text-4xl font-extrabold text-slate-900 font-serif leading-tight border-b border-rose-100 pb-6 mb-8">
                {{ $page->title }}
            </h1>
            <p class="text-slate-500">Bu sayfanın içeriği henüz hazırlanmadı.</p>
        </div>
    </div>
    @endif
@endsection
