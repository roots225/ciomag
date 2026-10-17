@extends('layouts.admin')

@section('title', 'Modération des Commentaires')
@section('subtitle', 'Gestion des réactions des lecteurs (pending, approved, spam, trash)')

@section('content')
<div class="space-y-6">

    <!-- Filtres par statut -->
    <div class="flex flex-wrap items-center gap-1.5">
        <a href="{{ route('admin.comments.index') }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1.5 {{ !$status ? 'bg-slate-900 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
            <i data-lucide="list-filter" class="w-3.5 h-3.5"></i>
            Tous
        </a>
        @foreach(\App\Enums\CommentStatus::cases() as $cs)
            <a href="{{ route('admin.comments.index', ['status' => $cs->value]) }}" class="px-3.5 py-1.5 rounded-lg text-xs font-medium inline-flex items-center gap-1.5 {{ $status === $cs->value ? 'bg-red-700 text-white' : 'bg-white border border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                <i data-lucide="{{ match($cs->value) { 'approved' => 'check-circle-2', 'pending' => 'clock', 'spam' => 'shield-alert', 'trash' => 'trash-2', default => 'message-square' } }}" class="w-3.5 h-3.5"></i>
                {{ $cs->label() }}
            </a>
        @endforeach
    </div>

    <div class="admin-card divide-y divide-slate-100 overflow-hidden">
        @forelse($comments as $comment)
            <div class="p-5 flex flex-col sm:flex-row items-start justify-between gap-4 hover:bg-slate-50/70 transition-colors">
                <div class="space-y-1.5 min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 text-xs">
                        <strong class="font-semibold text-slate-900 inline-flex items-center gap-1">
                            <i data-lucide="user" class="w-3 h-3 text-slate-400"></i>
                            {{ $comment->authorName() }}
                        </strong>
                        @if($comment->guest_email)
                            <span class="text-slate-400">({{ $comment->guest_email }})</span>
                        @endif
                        <span class="admin-badge badge-neutral text-[10px]">
                            {{ $comment->status->label() }}
                        </span>
                        <span class="text-slate-400 text-[11px] inline-flex items-center gap-1">
                            <i data-lucide="globe" class="w-3 h-3"></i>
                            {{ $comment->ip_address ?? 'N/A' }}
                        </span>
                        <span class="text-slate-400 text-[11px] inline-flex items-center gap-1">
                            <i data-lucide="calendar" class="w-3 h-3"></i>
                            {{ $comment->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <p class="text-xs text-slate-700 leading-relaxed">{{ $comment->content }}</p>

                    @if($comment->post)
                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-0.5">
                            <i data-lucide="file-text" class="w-3 h-3 text-slate-400"></i>
                            <span>Sur l'article :</span>
                            <a href="{{ route('magazine.post', $comment->post->slug) }}" target="_blank" class="font-medium text-red-700 hover:underline inline-flex items-center gap-1">
                                <span>{{ $comment->post->title }}</span>
                                <i data-lucide="external-link" class="w-2.5 h-2.5"></i>
                            </a>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-1.5 shrink-0 self-end sm:self-center">
                    @foreach([
                        'approved' => ['label' => 'Approuver', 'icon' => 'check', 'class' => 'btn-dark'],
                        'pending' => ['label' => 'En attente', 'icon' => 'clock', 'class' => 'btn-secondary'],
                        'spam' => ['label' => 'Spam', 'icon' => 'shield-alert', 'class' => 'btn-ghost text-amber-700 hover:bg-amber-50'],
                        'trash' => ['label' => 'Corbeille', 'icon' => 'trash-2', 'class' => 'btn-danger-ghost'],
                    ] as $stKey => $stMeta)
                        @if($comment->status->value !== $stKey)
                            <form action="{{ route('admin.comments.status', $comment) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $stKey }}">
                                <button type="submit" class="btn btn-sm {{ $stMeta['class'] }}">
                                    <i data-lucide="{{ $stMeta['icon'] }}" class="w-3.5 h-3.5"></i>
                                    <span>{{ $stMeta['label'] }}</span>
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>
        @empty
            <div class="p-8 text-center text-xs text-slate-400">Aucun commentaire trouvé.</div>
        @endforelse
    </div>

    <div>
        {{ $comments->links() }}
    </div>

</div>
@endsection
