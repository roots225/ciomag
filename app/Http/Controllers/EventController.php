<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min(50, max(1, (int) $request->query('per_page', 6)));

        $featuredEvent = Event::query()
            ->where('status', 'published')
            ->where('starts_at', '>=', now())
            ->orderByDesc('is_featured')
            ->orderBy('starts_at')
            ->first();

        $events = Event::query()
            ->where('status', 'published')
            ->withCount('confirmedRegistrations')
            ->orderBy('starts_at', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('magazine.events.index', compact('events', 'featuredEvent'));
    }

    public function show(string $slug): View
    {
        $event = Event::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->withCount('confirmedRegistrations')
            ->firstOrFail();

        $otherEvents = Event::query()
            ->where('status', 'published')
            ->where('id', '!=', $event->id)
            ->orderBy('starts_at')
            ->limit(3)
            ->get();

        return view('magazine.events.show', compact('event', 'otherEvents'));
    }

    public function register(Request $request, string $slug): RedirectResponse
    {
        $event = Event::query()
            ->where('status', 'published')
            ->where('slug', $slug)
            ->firstOrFail();

        if (! $event->registration_open) {
            return back()->with('error', 'Les inscriptions pour cet événement sont actuellement closes.');
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'company' => ['required', 'string', 'max:150'],
            'job_title' => ['required', 'string', 'max:150'],
            'country' => ['nullable', 'string', 'max:100'],
            'attendance_mode' => ['required', 'in:in_person,online'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $ticketCode = 'CIO-EVT-' . now()->format('Y') . '-' . strtoupper(Str::random(4));

        EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $request->user()?->id,
            'ticket_code' => $ticketCode,
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'],
            'job_title' => $validated['job_title'],
            'country' => $validated['country'] ?? null,
            'attendance_mode' => $validated['attendance_mode'],
            'status' => 'confirmed',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->to(route('magazine.events.show', $event->slug) . '#event-registration')
            ->with('success', "Votre accréditation pour « {$event->title} » est confirmée ! Code Pass : {$ticketCode}");
    }
}
