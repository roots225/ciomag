<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventAdminController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $events = Event::query()
            ->withCount('registrations')
            ->orderByDesc('starts_at')
            ->paginate($perPage)
            ->withQueryString();

        $registrations = EventRegistration::query()
            ->with('event')
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'registrations_page')
            ->withQueryString();

        return view('admin.events.index', compact('events', 'registrations'));
    }

    public function create(): View
    {
        $event = new Event([
            'event_type' => 'Conférence & Forum',
            'status' => 'published',
            'starts_at' => now()->addDays(30)->setTime(9, 0),
            'ends_at' => now()->addDays(30)->setTime(18, 0),
            'capacity' => 250,
            'ticket_price' => 0,
            'currency' => 'EUR',
            'is_online' => true,
            'is_featured' => false,
            'registration_open' => true,
            'program' => [
                [
                    'time' => '09:00 - 10:30',
                    'title' => 'Plénière d\'ouverture',
                    'speaker' => '',
                    'role' => 'Keynote',
                    'track' => 'Auditorium Principal',
                    'description' => '',
                ],
            ],
            'committee' => [
                [
                    'name' => '',
                    'role' => 'Membre du Comité d\'Organisation',
                    'company' => 'CIOMAG',
                    'photo_url' => '',
                    'linkedin' => '',
                    'twitter' => '',
                    'website' => '',
                ],
            ],
            'partners' => [
                [
                    'name' => '',
                    'tier' => 'Sponsor Officiel',
                    'logo_url' => '',
                    'website' => '',
                ],
            ],
        ]);

        return view('admin.events.form', compact('event'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateEvent($request);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        Event::create([
            'title' => $validated['title'],
            'slug' => $slug,
            'subtitle' => $validated['subtitle'] ?? null,
            'event_type' => $validated['event_type'] ?? 'Conférence & Forum',
            'excerpt' => $validated['excerpt'] ?? null,
            'description' => $validated['description'] ?? null,
            'venue' => $validated['venue'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'is_online' => $request->boolean('is_online'),
            'online_url' => $validated['online_url'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'cover_url' => $validated['cover_url'] ?? null,
            'capacity' => (int) ($validated['capacity'] ?? 250),
            'ticket_price' => (float) ($validated['ticket_price'] ?? 0),
            'currency' => $validated['currency'] ?? 'EUR',
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'registration_open' => $request->boolean('registration_open'),
            'program' => $this->parseJsonField($request->input('program_json')),
            'committee' => $this->parseJsonField($request->input('committee_json')),
            'partners' => $this->parseJsonField($request->input('partners_json')),
            'seo_meta' => [],
        ]);

        return redirect()->route('admin.events.index')
            ->with('success', 'Événement créé avec son programme, son comité d\'organisation et ses partenaires.');
    }

    public function show(Request $request, Event $event): View
    {
        $status = $request->query('status');
        $search = trim((string) $request->query('q', ''));
        $perPage = min(50, max(1, (int) $request->query('per_page', 10)));

        $event->loadCount(['registrations', 'confirmedRegistrations']);

        $registrations = $event->registrations()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%")
                        ->orWhere('ticket_code', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.events.show', compact('event', 'registrations', 'status', 'search'));
    }

    public function edit(Event $event): View
    {
        return view('admin.events.form', compact('event'));
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $validated = $this->validateEvent($request, $event);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['title']);

        $event->update([
            'title' => $validated['title'],
            'slug' => $slug,
            'subtitle' => $validated['subtitle'] ?? null,
            'event_type' => $validated['event_type'] ?? 'Conférence & Forum',
            'excerpt' => $validated['excerpt'] ?? null,
            'description' => $validated['description'] ?? null,
            'venue' => $validated['venue'] ?? null,
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'is_online' => $request->boolean('is_online'),
            'online_url' => $validated['online_url'] ?? null,
            'starts_at' => $validated['starts_at'],
            'ends_at' => $validated['ends_at'] ?? null,
            'cover_url' => $validated['cover_url'] ?? null,
            'capacity' => (int) ($validated['capacity'] ?? 250),
            'ticket_price' => (float) ($validated['ticket_price'] ?? 0),
            'currency' => $validated['currency'] ?? 'EUR',
            'status' => $validated['status'],
            'is_featured' => $request->boolean('is_featured'),
            'registration_open' => $request->boolean('registration_open'),
            'program' => $this->parseJsonField($request->input('program_json')),
            'committee' => $this->parseJsonField($request->input('committee_json')),
            'partners' => $this->parseJsonField($request->input('partners_json')),
        ]);

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Événement mis à jour avec succès.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Événement supprimé.');
    }

    public function storeRegistration(Request $request, Event $event): RedirectResponse
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'company' => ['nullable', 'string', 'max:150'],
            'job_title' => ['nullable', 'string', 'max:150'],
            'country' => ['nullable', 'string', 'max:100'],
            'attendance_mode' => ['nullable', 'in:in_person,online'],
            'status' => ['nullable', 'in:confirmed,pending,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $ticketCode = 'CIO-EVT-'.now()->format('Y').'-'.strtoupper(Str::random(4));

        EventRegistration::create([
            'event_id' => $event->id,
            'ticket_code' => $ticketCode,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'country' => $validated['country'] ?? null,
            'attendance_mode' => $validated['attendance_mode'] ?? 'in_person',
            'status' => $validated['status'] ?? 'confirmed',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', "Participant « {$validated['full_name']} » inscrit avec le badge {$ticketCode}.");
    }

    public function updateRegistrationStatus(Request $request, EventRegistration $registration): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:confirmed,pending,cancelled'],
        ]);

        $registration->update(['status' => $validated['status']]);

        return back()->with('success', "Statut de l'accréditation {$registration->ticket_code} mis à jour.");
    }

    public function destroyRegistration(EventRegistration $registration): RedirectResponse
    {
        $code = $registration->ticket_code;
        $registration->delete();

        return back()->with('success', "Accréditation {$code} supprimée.");
    }

    private function validateEvent(Request $request, ?Event $event = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('events', 'slug')->ignore($event?->id),
            ],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'event_type' => ['nullable', 'string', 'max:80'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'description' => ['nullable', 'string'],
            'venue' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'online_url' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date'],
            'cover_url' => ['nullable', 'string', 'max:1000'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'ticket_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:10'],
            'status' => ['required', 'in:draft,published,completed,cancelled'],
        ]);
    }

    private function parseJsonField(?string $json): array
    {
        if (! $json) {
            return [];
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? array_values($decoded) : [];
    }
}
