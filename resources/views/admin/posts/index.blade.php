@extends('layouts.admin')

@section('title', 'Articles & Pages')
@section('subtitle', 'Gestion des contenus éditoriaux (TinyMCE & GrapesJS), SEO JSON et historique des révisions')

@section('content')
    @php
        $typeFilterOptions = [
            '' => 'Tous les types',
            'post' => 'Articles',
            'page' => 'Pages',
        ];

        $statusFilterOptions = ['' => 'Tous les statuts'];
        foreach (\App\Enums\PostStatus::cases() as $st) {
            $statusFilterOptions[$st->value] = $st->label();
        }
    @endphp

    <div class="space-y-6">

        <!-- Barre de filtres et actions rapides -->
        <div
            class="bg-white rounded-2xl border border-slate-200 p-4 flex flex-wrap items-center justify-between gap-4 shadow-2xs">
            <form action="{{ route('admin.posts.index') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-1">
                <div class="relative w-64">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-3"></i>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Recherche plein texte..."
                        class="w-full pl-9 pr-3.5 py-2 text-sm border border-slate-300 rounded-xl focus:outline-none focus:border-red-700">
                </div>

                <div class="w-48">
                    <x-ui.select name="type" :options="$typeFilterOptions" :value="$type ?? ''" placeholder="Tous les types"
                        :searchable="false" />
                </div>

                <div class="w-52">
                    <x-ui.select name="status" :options="$statusFilterOptions" :value="$status ?? ''"
                        placeholder="Tous les statuts" :searchable="false" />
                </div>

                <button type="submit"
                    class="px-4 py-2.5 bg-slate-900 hover:bg-red-700 text-white text-xs font-bold uppercase rounded-xl transition inline-flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filtrer</span>
                </button>
            </form>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.posts.create', ['type' => 'page']) }}"
                    class="px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-xl inline-flex items-center gap-1.5">
                    <i data-lucide="layout-template" class="w-3.5 h-3.5"></i>
                    <span>Nouvelle Page</span>
                </a>
                <a href="{{ route('admin.posts.create', ['type' => 'post']) }}"
                    class="px-3.5 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold rounded-xl inline-flex items-center gap-1.5">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Nouvel Article</span>
                </a>
            </div>
        </div>

        <!-- Tableau des contenus -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr
                        class="border-b border-slate-200 bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-500">
                        <th class="py-3.5 px-5">Titre & Slug</th>
                        <th class="py-3.5 px-4">Type / Statut</th>
                        <th class="py-3.5 px-4">Rubrique Principale</th>
                        <th class="py-3.5 px-4">Auteur</th>
                        <th class="py-3.5 px-4">Métriques</th>
                        <th class="py-3.5 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($posts as $post)
                        <tr class="hover:bg-slate-50/80">
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-2">
                                    @if($post->is_featured)
                                        <i data-lucide="star" class="w-4 h-4 text-amber-500 fill-amber-400 shrink-0"
                                            title="Épinglé À la Une"></i>
                                    @endif
                                    @if($post->is_premium)
                                        <span class="px-1.5 py-0.5 text-[10px] font-extrabold uppercase bg-slate-950 text-amber-400 rounded-xs inline-flex items-center gap-1 shrink-0" title="Article Exclusif Abonnés">
                                            <i data-lucide="crown" class="w-3 h-3"></i>
                                            <span>Premium</span>
                                        </span>
                                    @endif
                                    <a href="{{ route('admin.posts.edit', $post) }}"
                                        class="font-bold text-slate-900 hover:text-red-700">
                                        {{ $post->title }}
                                    </a>
                                </div>
                                <div class="text-xs text-slate-400 font-mono mt-0.5">
                                    /{{ $post->type->value === 'page' ? 'page' : 'blog' }}/{{ $post->slug }}</div>
                            </td>
                            <td class="py-4 px-4">
                                <span
                                    class="px-2 py-0.5 text-[11px] font-bold uppercase rounded-xs {{ $post->status->badgeColor() }}">
                                    {{ $post->status->label() }}
                                </span>
                                <span class="block text-[11px] text-slate-400 mt-1">{{ $post->type->label() }}</span>
                            </td>
                            <td class="py-4 px-4 text-xs">
                                @if($primary = $post->primaryCategory())
                                    <span class="font-semibold text-slate-800">{{ $primary->name }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-600">
                                {{ $post->author->display_name }}
                            </td>
                            <td class="py-4 px-4 text-xs text-slate-500">
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>{{ number_format($post->view_count, 0, ',', ' ') }} •
                                        {{ $post->reading_time_minutes }} min</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $post->revisions_count }} rév. •
                                    {{ $post->comments_count }} comm.</div>
                            </td>
                            <td class="py-4 px-5 text-right space-x-1.5">
                                @if($post->status === \App\Enums\PostStatus::Published)
                                    <a href="{{ $post->url() }}" target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-semibold text-slate-600 hover:text-slate-900">
                                        <span>Voir</span>
                                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                    </a>
                                @endif
                                <a href="{{ route('admin.posts.edit', $post) }}"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-lg">
                                    <i data-lucide="pen-square" class="w-3.5 h-3.5"></i>
                                    <span>Éditer</span>
                                </a>
                                <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline"
                                    onsubmit="return confirm('Placer ce contenu dans la corbeille ?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 text-rose-600 hover:bg-rose-50 text-xs font-semibold rounded-lg cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>
            {{ $posts->links() }}
        </div>

    </div>
@endsection