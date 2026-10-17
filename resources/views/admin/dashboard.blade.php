@extends('layouts.admin')

@section('title', 'Tableau de Bord')
@section('subtitle', 'Vue d\'ensemble des publications, de l\'audience et de la modération CIOMAG')

@section('content')
    <div class="space-y-8">

        <!-- KPI Cards avec icônes Lucide -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Articles Publiés</span>
                    <div class="w-8 h-8 rounded-lg bg-red-50 text-red-700 flex items-center justify-center">
                        <i data-lucide="newspaper" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-3xl font-black text-slate-900">{{ $stats['published_posts'] }}</span>
                    <span class="text-xs font-semibold text-amber-700 bg-amber-50 px-2 py-0.5 rounded-full">
                        + {{ $stats['draft_posts'] }} en rédaction
                    </span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Audience Cumulée</span>
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-700 flex items-center justify-center">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span
                        class="text-3xl font-black text-slate-900">{{ number_format($stats['total_views'], 0, ',', ' ') }}</span>
                    <span class="text-xs text-emerald-700 font-semibold">Lectures</span>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Commentaires à Modérer</span>
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                        <i data-lucide="message-square" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span
                        class="text-3xl font-black {{ $stats['pending_comments'] > 0 ? 'text-red-700' : 'text-slate-900' }}">
                        {{ $stats['pending_comments'] }}
                    </span>
                    <a href="{{ route('admin.comments.index', ['status' => 'pending']) }}"
                        class="text-xs font-semibold text-red-700 hover:underline inline-flex items-center gap-1">
                        <span>{{ $stats['approved_comments'] }} approuvés</span>
                        <i data-lucide="arrow-right" class="w-3 h-3"></i>
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Taxonomie & Médias</span>
                    <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                        <i data-lucide="folder-tree" class="w-4 h-4"></i>
                    </div>
                </div>
                <div class="mt-3 flex items-baseline justify-between">
                    <span class="text-3xl font-black text-slate-900">{{ $stats['categories'] }}</span>
                    <span class="text-xs text-slate-500">{{ $stats['tags'] }} tags • {{ $stats['media'] }} médias</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Derniers Contenus -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="file-text" class="w-4 h-4 text-red-700"></i>
                        <span>Derniers Articles & Pages</span>
                    </h2>
                    <a href="{{ route('admin.posts.index') }}"
                        class="text-xs font-semibold text-red-700 hover:underline inline-flex items-center gap-1">
                        <span>Tout voir</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
                <div class="divide-y divide-slate-100">
                    @foreach($recentPosts as $post)
                        <div class="px-6 py-4 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-0.5 text-[10px] font-bold uppercase rounded-xs {{ $post->status->badgeColor() }}">
                                        {{ $post->status->label() }}
                                    </span>
                                    <span
                                        class="text-xs text-slate-400 uppercase font-semibold">{{ $post->type->label() }}</span>
                                </div>
                                <a href="{{ route('admin.posts.edit', $post) }}"
                                    class="text-sm font-bold text-slate-900 hover:text-red-700 truncate block">
                                    {{ $post->title }}
                                </a>
                                <span class="text-xs text-slate-500">Par {{ $post->author->display_name }} •
                                    {{ number_format($post->view_count, 0, ',', ' ') }} vues</span>
                            </div>
                            <a href="{{ route('admin.posts.edit', $post) }}"
                                class="px-3 py-1.5 text-xs font-semibold bg-slate-100 hover:bg-slate-200 rounded-lg shrink-0 inline-flex items-center gap-1.5">
                                <i data-lucide="pen-square" class="w-3.5 h-3.5"></i>
                                <span>Éditer</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- File d'attente de modération -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="shield-alert" class="w-4 h-4 text-red-700"></i>
                        <span>Modération des Commentaires</span>
                    </h2>
                    <a href="{{ route('admin.comments.index') }}"
                        class="text-xs font-semibold text-red-700 hover:underline inline-flex items-center gap-1">
                        <span>Gérer</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($pendingComments as $comment)
                        <div class="p-5 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <strong class="text-slate-900">{{ $comment->authorName() }}</strong>
                                <span class="text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-slate-600 line-clamp-2">{{ $comment->content }}</p>
                            <div class="pt-1 flex items-center gap-2">
                                <form action="{{ route('admin.comments.status', $comment) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit"
                                        class="px-2.5 py-1 bg-emerald-600 text-white text-[11px] font-bold rounded-md inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        <span>Approuver</span>
                                    </button>
                                </form>
                                <form action="{{ route('admin.comments.status', $comment) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="spam">
                                    <button type="submit"
                                        class="px-2.5 py-1 bg-amber-100 text-amber-800 text-[11px] font-bold rounded-md inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="shield-off" class="w-3 h-3"></i>
                                        <span>Spam</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-500">
                            Aucun commentaire en attente de modération.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
@endsection