@extends('layouts.magazine')

@section('meta_title', $page->seoTitle())
@section('meta_description', $page->seoDescription())

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    @if($page->parent)
        <div class="mb-4 text-xs text-slate-500">
            <a href="{{ route('magazine.page', $page->parent->slug) }}" class="text-ciomag-teal font-semibold hover:underline inline-flex items-center gap-1">
                <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                {{ $page->parent->title }}
            </a>
        </div>
    @endif

    <div class="bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 shadow-2xs">
        <h1 class="text-3xl sm:text-4xl font-black text-slate-950 mb-4">{{ $page->title }}</h1>

        @if($page->excerpt)
            <p class="text-lg text-slate-600 mb-8 pb-6 border-b border-slate-100">{{ $page->excerpt }}</p>
        @endif

        <div class="article-content">
            {!! $page->content !!}
        </div>

        @if($page->children->isNotEmpty())
            <div class="mt-10 pt-6 border-t border-slate-200">
                <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500 mb-3">Pages associées</h2>
                <div class="flex flex-wrap gap-3">
                    @foreach($page->children as $child)
                        <a href="{{ route('magazine.page', $child->slug) }}" class="px-4 py-2 bg-slate-100 hover:bg-ciomag-teal hover:text-white text-sm font-semibold rounded-lg transition inline-flex items-center gap-1.5">
                            {{ $child->title }}
                            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
