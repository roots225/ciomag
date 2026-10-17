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
        <div class="admin-card p-4 flex flex-wrap items-center justify-between gap-3">
            <form action="{{ route('admin.posts.index') }}" method="GET" class="flex flex-wrap items-center gap-2 flex-1">
                <div class="relative w-60">
                    <i data-lucide="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input type="search" name="q" value="{{ $search }}" placeholder="Recherche plein texte..."
                        class="w-full pl-8 pr-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:border-slate-800">
                </div>

                <div class="w-44">
                    <x-ui.select name="type" :options="$typeFilterOptions" :value="$type ?? ''" placeholder="Tous les types"
                        :searchable="false" />
                </div>

                <div class="w-48">
                    <x-ui.select name="status" :options="$statusFilterOptions" :value="$status ?? ''"
                        placeholder="Tous les statuts" :searchable="false" />
                </div>

                <button type="submit" class="btn btn-md btn-dark">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Filtrer</span>
                </button>
            </form>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.posts.create', ['type' => 'page']) }}" class="btn btn-md btn-secondary">
                    <i data-lucide="layout-template" class="w-3.5 h-3.5"></i>
                    <span>Nouvelle Page</span>
                </a>
                <a href="{{ route('admin.posts.create', ['type' => 'post']) }}" class="btn btn-md btn-primary">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Nouvel Article</span>
                </a>
            </div>
        </div>

        <!-- Tableau des contenus -->
        <div class="admin-table-container">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Titre & Slug</th>
                        <th>Type / Statut</th>
                        <th>Rubrique Principale</th>
                        <th>Auteur</th>
                        <th>Métriques</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($posts as $post)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td>
                                <div class="flex items-center gap-2">
                                    @if($post->is_featured)
                                        <i data-lucide="star" class="w-3.5 h-3.5 text-amber-500 fill-amber-400 shrink-0"
                                            title="Épinglé À la Une"></i>
                                    @endif
                                    @if($post->is_premium)
                                        <span
                                            class="admin-badge badge-warning text-[9px] py-0 px-1.5 inline-flex items-center gap-1 shrink-0"
                                            title="Article Exclusif Abonnés">
                                            <i data-lucide="crown" class="w-2.5 h-2.5"></i>
                                            <span>Premium</span>
                                        </span>
                                    @endif
                                    <a href="{{ route('admin.posts.edit', $post) }}"
                                        class="font-semibold text-slate-900 hover:text-red-700 transition">
                                        {{ $post->title }}
                                    </a>
                                </div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    /{{ $post->type->value === 'page' ? 'page' : 'blog' }}/{{ $post->slug }}</div>
                            </td>
                            <td>
                                <span class="admin-badge badge-neutral">
                                    {{ $post->status->label() }}
                                </span>
                                <span class="block text-[11px] text-slate-400 mt-0.5">{{ $post->type->label() }}</span>
                            </td>
                            <td class="text-xs">
                                @if($primary = $post->primaryCategory())
                                    <span class="font-medium text-slate-800">{{ $primary->name }}</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="text-xs text-slate-600">
                                {{ $post->author->display_name }}
                            </td>
                            <td class="text-xs text-slate-500">
                                <div class="flex items-center gap-1.5">
                                    <i data-lucide="eye" class="w-3.5 h-3.5 text-slate-400"></i>
                                    <span>{{ number_format($post->view_count, 0, ',', ' ') }} •
                                        {{ $post->reading_time_minutes }} min</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">{{ $post->revisions_count }} rév. •
                                    {{ $post->comments_count }} comm.
                                </div>
                            </td>
                            <td class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    @if($post->status === \App\Enums\PostStatus::Published)
                                        <a href="{{ $post->url() }}" target="_blank"
                                            class="p-1.5 text-slate-400 hover:text-slate-700 rounded-lg transition"
                                            title="Voir l'article">
                                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-secondary">
                                        <i data-lucide="pencil" class="w-3 h-3"></i>
                                        <span>Éditer</span>
                                    </a>
                                    <form action="{{ route('admin.posts.destroy', $post) }}" method="POST" class="inline"
                                        onsubmit="return confirm('Placer ce contenu dans la corbeille ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition cursor-pointer"
                                            title="Corbeille">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-400 text-xs">
                                Aucun article ou page trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $posts->links() }}
        </div>

    </div>
@endsection