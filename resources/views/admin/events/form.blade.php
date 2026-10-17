@extends('layouts.admin')

@section('title', $event->exists ? 'Modifier l\'événement' : 'Nouvel événement')
@section('header_title', $event->exists ? 'Éditer : ' . $event->title : 'Programmer un nouvel événement CIOMAG')

@section('top_actions')
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.events.index') }}" class="btn btn-sm btn-secondary">
            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
            <span>Retour aux événements</span>
        </a>
        @if($event->exists)
            <a href="{{ route('magazine.events.show', $event->slug) }}" target="_blank" class="btn btn-sm btn-secondary">
                <i data-lucide="external-link" class="w-3.5 h-3.5 text-slate-500"></i>
                <span>Voir la page publique</span>
            </a>
        @endif
    </div>
@endsection

@section('content')
<form
    action="{{ $event->exists ? route('admin.events.update', $event) : route('admin.events.store') }}"
    method="POST"
    x-data="{
        program: @js(old('program_json') ? json_decode(old('program_json'), true) : ($event->program ?? [])),
        committee: @js(old('committee_json') ? json_decode(old('committee_json'), true) : ($event->committee ?? [])),
        partners: @js(old('partners_json') ? json_decode(old('partners_json'), true) : ($event->partners ?? [])),
        addProgramSlot() {
            this.program.push({
                time: '14:00 - 15:00',
                title: '',
                speaker: '',
                role: 'Panel',
                track: 'Salle Plénière',
                description: ''
            });
            this.$nextTick(() => window.refreshLucideIcons && window.refreshLucideIcons());
        },
        removeProgramSlot(index) {
            this.program.splice(index, 1);
        },
        addCommitteeMember() {
            this.committee.push({
                name: '',
                role: 'Membre du Comité d\'Organisation',
                company: 'CIOMAG',
                photo_url: '',
                linkedin: '',
                twitter: '',
                website: ''
            });
            this.$nextTick(() => window.refreshLucideIcons && window.refreshLucideIcons());
        },
        removeCommitteeMember(index) {
            this.committee.splice(index, 1);
        },
        pickCommitteePhoto(index) {
            if (typeof window.openMediaManager === 'function') {
                window.openMediaManager({
                    onSelect: (media) => {
                        this.committee[index].photo_url = media.url;
                    }
                });
            }
        },
        addPartner() {
            this.partners.push({
                name: '',
                tier: 'Sponsor Gold',
                logo_url: '',
                website: ''
            });
            this.$nextTick(() => window.refreshLucideIcons && window.refreshLucideIcons());
        },
        removePartner(index) {
            this.partners.splice(index, 1);
        },
        pickPartnerLogo(index) {
            if (typeof window.openMediaManager === 'function') {
                window.openMediaManager({
                    onSelect: (media) => {
                        this.partners[index].logo_url = media.url;
                    }
                });
            }
        }
    }"
    class="grid grid-cols-1 lg:grid-cols-12 gap-6"
>
    @csrf
    @if($event->exists)
        @method('PUT')
    @endif

    {{-- Champs JSON cachés synchronisés par Alpine.js --}}
    <input type="hidden" name="program_json" :value="JSON.stringify(program)">
    <input type="hidden" name="committee_json" :value="JSON.stringify(committee)">
    <input type="hidden" name="partners_json" :value="JSON.stringify(partners)">

    {{-- Colonne principale --}}
    <div class="lg:col-span-8 space-y-6">

        {{-- Informations générales --}}
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="sparkles" class="w-4 h-4 text-red-700"></i>
                <span>Présentation de l'événement</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="admin-label">Titre de l'événement *</label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}" required placeholder="Ex: Assises de la Transformation Digitale en Afrique (ATDA 2026)" class="admin-input text-base font-semibold">
                </div>

                <div>
                    <label class="admin-label">Sous-titre / Thème central</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $event->subtitle) }}" placeholder="Ex: 15e Édition — Souveraineté Cloud & IA" class="admin-input">
                </div>

                <div>
                    <label class="admin-label">Slug URL</label>
                    <input type="text" name="slug" value="{{ old('slug', $event->slug) }}" placeholder="Généré automatiquement si vide" class="admin-input font-mono">
                </div>
            </div>

            <div>
                <label class="admin-label">Chapô / Résumé exécutif</label>
                <textarea name="excerpt" rows="2" class="admin-input" placeholder="Courte accroche affichée dans la carte événement et le hero...">{{ old('excerpt', $event->excerpt) }}</textarea>
            </div>

            <div>
                <label class="admin-label">Présentation détaillée de l'événement (HTML autorisé)</label>
                <textarea name="description" rows="6" class="admin-input font-mono text-xs" placeholder="<p>Décrivez les enjeux, les thématiques majeures et le public cible...</p>">{{ old('description', $event->description) }}</textarea>
            </div>
        </div>

        {{-- Programme de l'événement --}}
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="calendar-range" class="w-4 h-4 text-red-700"></i>
                        <span>Programme Officiel de l'Événement</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Sessions horaires, intervenants, rôles et salles.</p>
                </div>
                <button type="button" @click="addProgramSlot()" class="btn btn-sm btn-secondary">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Ajouter une session</span>
                </button>
            </div>

            <div class="space-y-3">
                <template x-for="(slot, index) in program" :key="index">
                    <div class="p-4 rounded-lg border border-slate-200 bg-slate-50/70 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-red-700" x-text="'Session #' + (index + 1)"></span>
                            <button type="button" @click="removeProgramSlot(index)" class="text-xs text-rose-600 hover:text-rose-800 font-medium flex items-center gap-1 cursor-pointer">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Supprimer
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="admin-label text-[11px]">Horaire</label>
                                <input type="text" x-model="slot.time" placeholder="09:00 - 10:30" class="admin-input">
                            </div>
                            <div class="md:col-span-2">
                                <label class="admin-label text-[11px]">Titre de l'intervention / Panel</label>
                                <input type="text" x-model="slot.title" placeholder="Ex: Table Ronde : IA Générative & Banques Africaines" class="admin-input font-medium">
                            </div>
                            <div>
                                <label class="admin-label text-[11px]">Intervenant(s)</label>
                                <input type="text" x-model="slot.speaker" placeholder="Nom ou liste des speakers" class="admin-input">
                            </div>
                            <div>
                                <label class="admin-label text-[11px]">Type / Format</label>
                                <input type="text" x-model="slot.role" placeholder="Keynote / Panel / Atelier" class="admin-input">
                            </div>
                            <div>
                                <label class="admin-label text-[11px]">Salle / Track</label>
                                <input type="text" x-model="slot.track" placeholder="Grand Auditorium" class="admin-input">
                            </div>
                            <div class="md:col-span-3">
                                <label class="admin-label text-[11px]">Description de la session</label>
                                <textarea x-model="slot.description" rows="2" placeholder="Points clés abordés durant cette séquence..." class="admin-input"></textarea>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Membres du Comité d'Organisation --}}
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-red-700"></i>
                        <span>Comité d'Organisation & Scientifique</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Photos et profils des membres du comité.</p>
                </div>
                <button type="button" @click="addCommitteeMember()" class="btn btn-sm btn-secondary">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    <span>Ajouter un membre</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-for="(member, index) in committee" :key="index">
                    <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/70 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <template x-if="member.photo_url">
                                    <img :src="member.photo_url" alt="" class="w-9 h-9 rounded-full object-cover border border-slate-200">
                                </template>
                                <template x-if="!member.photo_url">
                                    <div class="w-9 h-9 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                        CIO
                                    </div>
                                </template>
                                <button type="button" @click="pickCommitteePhoto(index)" class="btn btn-sm btn-secondary text-[11px] py-1 px-2">
                                    Photo
                                </button>
                            </div>
                            <button type="button" @click="removeCommitteeMember(index)" class="text-xs text-rose-600 hover:text-rose-800 font-medium cursor-pointer">
                                Retirer
                            </button>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <label class="admin-label text-[11px]">Nom complet</label>
                                <input type="text" x-model="member.name" placeholder="Ex: Mohamadou Diallo" class="admin-input">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="admin-label text-[11px]">Rôle</label>
                                    <input type="text" x-model="member.role" placeholder="Président" class="admin-input">
                                </div>
                                <div>
                                    <label class="admin-label text-[11px]">Organisation</label>
                                    <input type="text" x-model="member.company" placeholder="CIOMAG" class="admin-input">
                                </div>
                            </div>
                            <div>
                                <label class="admin-label text-[11px]">URL Photo</label>
                                <input type="text" x-model="member.photo_url" placeholder="https://..." class="admin-input">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="admin-label text-[11px]">LinkedIn</label>
                                    <input type="url" x-model="member.linkedin" placeholder="https://linkedin.com/in/..." class="admin-input">
                                </div>
                                <div>
                                    <label class="admin-label text-[11px]">X / Twitter</label>
                                    <input type="url" x-model="member.twitter" placeholder="https://x.com/..." class="admin-input">
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Logos des Partenaires & Sponsors --}}
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                        <i data-lucide="handshake" class="w-4 h-4 text-red-700"></i>
                        <span>Partenaires & Sponsors</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Logos, rangs et liens web des partenaires.</p>
                </div>
                <button type="button" @click="addPartner()" class="btn btn-sm btn-secondary">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Ajouter un partenaire</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-for="(partner, index) in partners" :key="index">
                    <div class="p-3.5 rounded-lg border border-slate-200 bg-slate-50/70 space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <template x-if="partner.logo_url">
                                    <img :src="partner.logo_url" alt="" class="w-10 h-8 rounded object-contain bg-white p-1 border border-slate-200">
                                </template>
                                <button type="button" @click="pickPartnerLogo(index)" class="btn btn-sm btn-secondary text-[11px] py-1 px-2">
                                    Logo
                                </button>
                            </div>
                            <button type="button" @click="removePartner(index)" class="text-xs text-rose-600 hover:text-rose-800 font-medium cursor-pointer">
                                Retirer
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="admin-label text-[11px]">Nom</label>
                                <input type="text" x-model="partner.name" placeholder="Orange Business" class="admin-input">
                            </div>
                            <div>
                                <label class="admin-label text-[11px]">Rang</label>
                                <input type="text" x-model="partner.tier" placeholder="Sponsor Diamond" class="admin-input">
                            </div>
                        </div>

                        <div>
                            <label class="admin-label text-[11px]">URL Logo</label>
                            <input type="text" x-model="partner.logo_url" placeholder="https://..." class="admin-input">
                        </div>

                        <div>
                            <label class="admin-label text-[11px]">Site Web</label>
                            <input type="url" x-model="partner.website" placeholder="https://..." class="admin-input">
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    {{-- Colonne latérale --}}
    <div class="lg:col-span-4 space-y-6">

        {{-- Statut & Publication --}}
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <i data-lucide="settings-2" class="w-4 h-4 text-red-700"></i>
                <span>Statut & Inscriptions</span>
            </h3>

            <x-ui.select
                name="status"
                label="Statut de l'événement"
                :value="old('status', $event->status ?? 'published')"
                :options="[
                    ['value' => 'published', 'label' => 'Publié (Agenda & Décompte actifs)'],
                    ['value' => 'draft', 'label' => 'Brouillon'],
                    ['value' => 'completed', 'label' => 'Terminé'],
                    ['value' => 'cancelled', 'label' => 'Annulé'],
                ]"
            />

            <x-ui.select
                name="event_type"
                label="Format d'événement"
                :value="old('event_type', $event->event_type ?? 'Conférence & Forum')"
                :options="[
                    ['value' => 'Grand Sommet Annuel', 'label' => 'Grand Sommet Annuel'],
                    ['value' => 'Conférence & Forum', 'label' => 'Conférence & Forum'],
                    ['value' => 'Table Ronde Exécutive', 'label' => 'Table Ronde Exécutive'],
                    ['value' => 'Webinaire Live', 'label' => 'Webinaire Live'],
                ]"
            />

            <div class="pt-2 space-y-2 border-t border-slate-100 flex flex-wrap justify-between">
                <x-ui.checkbox
                    name="registration_open"
                    value="1"
                    :checked="(bool) old('registration_open', $event->registration_open ?? true)"
                    label="Inscriptions ouvertes"
                />

                <x-ui.checkbox
                    name="is_featured"
                    value="1"
                    :checked="(bool) old('is_featured', $event->is_featured ?? false)"
                    label="Événement majeur à la Une"
                />

                <x-ui.checkbox
                    name="is_online"
                    value="1"
                    :checked="(bool) old('is_online', $event->is_online ?? true)"
                    label="Format Hybride / Streaming"
                />
            </div>

            <button type="submit" class="w-auto btn btn-md btn-primary justify-center">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>{{ $event->exists ? 'Enregistrer les modifications' : 'Créer l\'événement' }}</span>
            </button>
        </div>

        {{-- Dates & Compte à rebours --}}
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <i data-lucide="timer" class="w-4 h-4 text-red-700"></i>
                <span>Dates & Décompte Jour-J</span>
            </h3>

            <div>
                <label class="admin-label">Date & heure de début *</label>
                <input
                    type="datetime-local"
                    name="starts_at"
                    value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}"
                    required
                    class="admin-input"
                >
            </div>

            <div>
                <label class="admin-label">Date & heure de clôture</label>
                <input
                    type="datetime-local"
                    name="ends_at"
                    value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}"
                    class="admin-input"
                >
            </div>
        </div>

        {{-- Lieu & Capacité --}}
        <div class="admin-card p-5 sm:p-6 space-y-4">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <i data-lucide="map-pin" class="w-4 h-4 text-red-700"></i>
                <span>Lieu & Billetterie</span>
            </h3>

            <div>
                <label class="admin-label">Lieu / Salle / Hôtel</label>
                <input type="text" name="venue" value="{{ old('venue', $event->venue) }}" placeholder="Ex: Sofitel Abidjan Hôtel Ivoire" class="admin-input">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="admin-label">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $event->city) }}" placeholder="Abidjan" class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Pays</label>
                    <input type="text" name="country" value="{{ old('country', $event->country) }}" placeholder="Côte d'Ivoire" class="admin-input">
                </div>
            </div>

            <div>
                <label class="admin-label">Lien Streaming / Visio</label>
                <input type="url" name="online_url" value="{{ old('online_url', $event->online_url) }}" placeholder="https://live.ciomag.com/..." class="admin-input font-mono">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="admin-label">Capacité max</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $event->capacity ?? 250) }}" min="1" class="admin-input">
                </div>
                <div>
                    <label class="admin-label">Tarif Pass (€)</label>
                    <input type="number" step="0.01" name="ticket_price" value="{{ old('ticket_price', $event->ticket_price ?? 0) }}" min="0" class="admin-input font-semibold">
                </div>
            </div>
        </div>

        {{-- Visuel Officiel --}}
        <div class="admin-card p-5 sm:p-6 space-y-3">
            <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-100 pb-2.5">
                <i data-lucide="image" class="w-4 h-4 text-red-700"></i>
                <span>Visuel Officiel de l'Événement</span>
            </h3>

            <x-media-picker
                name="cover_url"
                valueMode="url"
                :value="old('cover_url', $event->cover_url)"
                :previewUrl="old('cover_url', $event->cover_url)"
                label="Bannière de présentation"
            />
        </div>

    </div>
</form>
@endsection
