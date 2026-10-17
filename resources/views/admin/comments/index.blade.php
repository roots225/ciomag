@extends('layouts.admin')

@section('title', 'Modération des Commentaires')
@section('subtitle', 'Gestion des réactions des lecteurs (pending, approved, spam, trash)')

@section('content')
<div class="space-y-6">

    <!-- Filtres par statut -->
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.comments.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5 {{ !$status ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-700' }}">
            <i data-lucide="list-filter" class="w-3.5 h-3.5"></i>
            Tous
        </a>
        @foreach(\App\Enums\CommentStatus::cases() as $cs)
            <a href="{{ route('admin.comments.index', ['status' => $cs->value]) }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5 {{ $status === $cs->value ? 'bg-red-700 text-white' : 'bg-white border border-slate-200 text-slate-700' }}">
                <i data-lucide="{{ match($cs->value) { 'approved' => 'check-circle-2', 'pending' => 'clock', 'spam' => 'shield-alert', 'trash' => 'trash-2', default => 'message-square' } }}" class="w-3.5 h-3.5"></i>
                {{ $cs->label() }}
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 divide-y divide-slate-100">
        @forelse($comments as $comment)
            <div class="p-6 flex flex-col sm:flex-row items-start justify-between gap-4">
                <div class="space-y-2">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <strong class="text-sm text-slate-900 inline-flex items-center gap-1.5">
                            <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                            {{ $comment->authorName() }}
                        </strong>
                        @if($comment->guest_email)
                            <span class="text-slate-400">({{ $comment->guest_email }})</span>
                        @endif
                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase bg-slate-100 text-slate-700 rounded-xs">
                            {{ $comment->status->label() }}
                        </span>
                        <span class="text-slate-400 inline-flex items-center gap-1">
                            <i data-lucide="globe" class="w-3 h-3"></i>
                            IP: {{ $comment->ip_address ?? 'N/A' }}
                        </span>
                        <span class="text-slate-400 inline-flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3 h-3"></i>
                            {{ $comment->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <p class="text-sm text-slate-700 leading-relaxed">{{ $comment->content }}</p>

                    @if($comment->post)
                        <div class="text-xs text-slate-500 flex items-center gap-1.5">
                            <i data-lucide="file-text" class="w-3.5 h-3.5 text-slate-400"></i>
                            Sur l'article : <a href="{{ route('magazine.post', $comment->post->slug) }}" target="_blank" class="font-semibold text-red-700 hover:underline inline-flex items-center gap-1">
                                {{ $comment->post->title }}
                                <i data-lucide="external-link" class="w-3 h-3"></i>
                            </a>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    @foreach([
                        'approved' => ['label' => 'Approuver', 'icon' => 'check'],
                        'pending' => ['label' => 'En attente', 'icon' => 'clock'],
                        'spam' => ['label' => 'Spam', 'icon' => 'shield-alert'],
                        'trash' => ['label' => 'Corbeille', 'icon' => 'trash-2'],
                    ] as $stKey => $stMeta)
                        @if($comment->status->value !== $stKey)
                            <form action="{{ route('admin.comments.status', $comment) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $stKey }}">
                                <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded-lg cursor-pointer inline-flex items-center gap-1.5 {{ $stKey === 'approved' ? 'bg-emerald-600 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700' }}">
                                    <i data-lucide="{{ $stMeta['icon'] }}" class="w-3.5 h-3.5"></i>
                                    {{ $stMeta['label'] }}
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-sm text-slate-500">Aucun commentaire trouvé.</div>
        @endforelse
    </div>

    <div>
        {{ $comments->links() }}
    </div>

</div>
@endsection
