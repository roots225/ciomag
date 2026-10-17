@extends('layouts.admin')

@section('title', $event->exists ? 'Modifier l\'événement' : 'Nouvel événement')
@section('header_title', $event->exists ? 'Éditer : ' . $event->title : 'Programmer un nouvel événement CIOMAG')

@section('top_actions')
    <a href="{{ route('admin.events.index') }}" class="px-3.5 py-2 rounded-lg border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition flex items-center gap-1.5">
        <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
        <span>Retour aux événements</span>
    </a>
    @if($event->exists)
        <a href="{{ route('magazine.events.show', $event->slug) }}" target="_blank" class="px-3.5 py-2 rounded-lg border border-red-200 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition flex items-center gap-1.5">
            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
            <span>Voir la page avec décompte</span>
        </a>
    @endif
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
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-5">
            <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
                <i data-lucide="sparkles" class="w-4 h-4 text-red-700"></i>
                <span>Présentation de l'événement</span>
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Titre de l'événement *</label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}" required placeholder="Ex: Assises de la Transformation Digitale en Afrique (ATDA 2026)" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-red-700 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Sous-titre / Thème central</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $event->subtitle) }}" placeholder="Ex: 15e Édition — Souveraineté Cloud & IA" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-red-700 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Slug URL</label>
                    <input type="text" name="slug" value="{{ old('slug', $event->slug) }}" placeholder="Généré automatiquement si vide" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm font-mono focus:border-red-700 focus:outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Chapô / Résumé exécutif</label>
                <textarea name="excerpt" rows="2" class="w-full rounded-lg border border-slate-300 px-3.5 py-2 text-sm focus:border-red-700 focus:outline-none" placeholder="Courte accroche affichée dans la carte événement et le hero...">{{ old('excerpt', $event->excerpt) }}</textarea>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Présentation détaillée de l'événement (HTML autorisé)</label>
                <textarea name="description" rows="6" class="w-full rounded-lg border border-slate-300 px-3.5 py-2.5 text-sm focus:border-red-700 focus:outline-none" placeholder="<p>Décrivez les enjeux, les thématiques majeures et le public cible...</p>">{{ old('description', $event->description) }}</textarea>
            </div>
        </div>

        {{-- Programme de l'événement --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="calendar-range" class="w-4 h-4 text-red-700"></i>
                        <span>Programme Officiel de l'Événement</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Ajoutez les sessions horaires, les intervenants, leur rôle et la salle.</p>
                </div>
                <button type="button" @click="addProgramSlot()" class="px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-red-700 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Ajouter une session</span>
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(slot, index) in program" :key="index">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black uppercase tracking-wider text-red-700" x-text="'Session #' + (index + 1)"></span>
                            <button type="button" @click="removeProgramSlot(index)" class="text-xs text-rose-600 hover:text-rose-800 font-bold flex items-center gap-1 cursor-pointer">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i> Supprimer
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Horaire</label>
                                <input type="text" x-model="slot.time" placeholder="09:00 - 10:30" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Titre de l'intervention / Panel</label>
                                <input type="text" x-model="slot.title" placeholder="Ex: Table Ronde : IA Générative & Banques Africaines" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Intervenant(s)</label>
                                <input type="text" x-model="slot.speaker" placeholder="Nom ou liste des speakers" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Type / Format</label>
                                <input type="text" x-model="slot.role" placeholder="Keynote / Panel / Atelier" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Salle / Track</label>
                                <input type="text" x-model="slot.track" placeholder="Grand Auditorium" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs">
                            </div>
                            <div class="md:col-span-3">
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">Description de la session</label>
                                <textarea x-model="slot.description" rows="2" placeholder="Points clés abordés durant cette séquence..." class="w-full rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs"></textarea>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Membres du Comité d'Organisation (avec photo et liens réseaux sociaux) --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="users" class="w-4 h-4 text-red-700"></i>
                        <span>Membres du Comité d'Organisation & Scientifique</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Ajoutez la photo de chaque membre ainsi que les liens vers leurs profils LinkedIn, X/Twitter et site web.</p>
                </div>
                <button type="button" @click="addCommitteeMember()" class="px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-red-700 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                    <span>Ajouter un membre</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-for="(member, index) in committee" :key="index">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <template x-if="member.photo_url">
                                    <img :src="member.photo_url" alt="" class="w-11 h-11 rounded-full object-cover border-2 border-red-600">
                                </template>
                                <template x-if="!member.photo_url">
                                    <div class="w-11 h-11 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs">
                                        PHOTO
                                    </div>
                                </template>
                                <button type="button" @click="pickCommitteePhoto(index)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-300 hover:border-red-700 text-[11px] font-bold text-slate-700 cursor-pointer">
                                    Choisir via Médiathèque
                                </button>
                            </div>
                            <button type="button" @click="removeCommitteeMember(index)" class="text-xs text-rose-600 hover:text-rose-800 font-bold cursor-pointer">
                                Retirer
                            </button>
                        </div>

                        <div class="space-y-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Nom complet</label>
                                <input type="text" x-model="member.name" placeholder="Ex: Mohamadou Diallo" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Rôle au comité</label>
                                    <input type="text" x-model="member.role" placeholder="Président du Comité" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Organisation</label>
                                    <input type="text" x-model="member.company" placeholder="CIOMAG" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                                </div>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-0.5">URL Photo (ou sélection Médiathèque)</label>
                                <input type="text" x-model="member.photo_url" placeholder="https://..." class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Profil LinkedIn</label>
                                    <input type="url" x-model="member.linkedin" placeholder="https://linkedin.com/in/..." class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Profil X / Twitter</label>
                                    <input type="url" x-model="member.twitter" placeholder="https://x.com/..." class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Logos des Partenaires & Sponsors --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3">
                <div>
                    <h2 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                        <i data-lucide="handshake" class="w-4 h-4 text-red-700"></i>
                        <span>Logos des Partenaires & Sponsors Officiels</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Associez les logos des sponsors institutionnels et technologiques avec leur rang et site web.</p>
                </div>
                <button type="button" @click="addPartner()" class="px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-red-700 text-white text-xs font-bold transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="plus-circle" class="w-3.5 h-3.5"></i>
                    <span>Ajouter un partenaire</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-for="(partner, index) in partners" :key="index">
                    <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/70 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <template x-if="partner.logo_url">
                                    <img :src="partner.logo_url" alt="" class="w-12 h-10 rounded-lg object-contain bg-white p-1 border border-slate-200">
                                </template>
                                <button type="button" @click="pickPartnerLogo(index)" class="px-2.5 py-1 rounded-lg bg-white border border-slate-300 hover:border-red-700 text-[11px] font-bold text-slate-700 cursor-pointer">
                                    Choisir Logo
                                </button>
                            </div>
                            <button type="button" @click="removePartner(index)" class="text-xs text-rose-600 hover:text-rose-800 font-bold cursor-pointer">
                                Retirer
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Nom du Partenaire</label>
                                <input type="text" x-model="partner.name" placeholder="Ex: Orange Business" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs font-semibold">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Rang / Catégorie</label>
                                <input type="text" x-model="partner.tier" placeholder="Sponsor Diamond" class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">URL du Logo</label>
                            <input type="text" x-model="partner.logo_url" placeholder="https://..." class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 mb-0.5">Site web du Partenaire</label>
                            <input type="url" x-model="partner.website" placeholder="https://..." class="w-full rounded-lg border border-slate-300 bg-white px-2.5 py-1.5 text-xs">
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    {{-- Colonne latérale : Dates, Compte à rebours, Lieu, Billetterie, Image --}}
    <div class="lg:col-span-4 space-y-6">

        {{-- Statut & Publication --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
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

            <div class="pt-2 space-y-2.5 border-t border-slate-100">
                <x-ui.checkbox
                    name="registration_open"
                    value="1"
                    :checked="(bool) old('registration_open', $event->registration_open ?? true)"
                    label="Inscriptions ouvertes"
                    description="Autoriser le formulaire d'accréditation en ligne"
                />

                <x-ui.checkbox
                    name="is_featured"
                    value="1"
                    :checked="(bool) old('is_featured', $event->is_featured ?? false)"
                    label="Événement majeur à la Une"
                    description="Mettre en avant en tête de l'agenda CIOMAG"
                />

                <x-ui.checkbox
                    name="is_online"
                    value="1"
                    :checked="(bool) old('is_online', $event->is_online ?? true)"
                    label="Format Hybride / Streaming en ligne"
                    description="Permet de choisir Présentiel ou Distanciel"
                />
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-red-700 hover:bg-red-800 text-white font-bold text-xs uppercase tracking-wider transition shadow-sm flex items-center justify-center gap-2 cursor-pointer">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>{{ $event->exists ? 'Enregistrer les modifications' : 'Créer l\'événement' }}</span>
            </button>
        </div>

        {{-- Dates & Compte à rebours --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i data-lucide="timer" class="w-4 h-4 text-red-700"></i>
                <span>Dates & Décompte Jour-J</span>
            </h3>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Date & heure de début (cible du décompte) *</label>
                <input
                    type="datetime-local"
                    name="starts_at"
                    value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}"
                    required
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-red-700 focus:outline-none"
                >
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Date & heure de clôture</label>
                <input
                    type="datetime-local"
                    name="ends_at"
                    value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs focus:border-red-700 focus:outline-none"
                >
            </div>
        </div>

        {{-- Lieu & Capacité --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-4">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                <i data-lucide="map-pin" class="w-4 h-4 text-red-700"></i>
                <span>Lieu & Billetterie</span>
            </h3>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Lieu / Palais des Congrès / Hôtel</label>
                <input type="text" name="venue" value="{{ old('venue', $event->venue) }}" placeholder="Ex: Sofitel Abidjan Hôtel Ivoire" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ville</label>
                    <input type="text" name="city" value="{{ old('city', $event->city) }}" placeholder="Abidjan / Paris" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pays</label>
                    <input type="text" name="country" value="{{ old('country', $event->country) }}" placeholder="Côte d'Ivoire" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Lien Streaming / Visioconférence</label>
                <input type="url" name="online_url" value="{{ old('online_url', $event->online_url) }}" placeholder="https://live.ciomag.com/..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Capacité max</label>
                    <input type="number" name="capacity" value="{{ old('capacity', $event->capacity ?? 250) }}" min="1" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tarif Pass (€)</label>
                    <input type="number" step="0.01" name="ticket_price" value="{{ old('ticket_price', $event->ticket_price ?? 0) }}" min="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-xs">
                    <span class="text-[10px] text-slate-400">0 = Accréditation gratuite</span>
                </div>
            </div>
        </div>

        {{-- Bannière / Affiche de l'événement --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs space-y-3">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
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
