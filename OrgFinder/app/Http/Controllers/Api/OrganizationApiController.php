<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\Request;

class OrganizationApiController extends Controller
{
    private const INTEREST_MAP = [
        'Technology'              => ['Technology', 'Information Technology', 'Programming', 'Software Development', 'Systems & Networking', 'Information Systems', 'Business & Technology Integration', 'Research', 'Innovation', 'Academic Organization'],
        'Programming'             => ['Programming', 'Software Development', 'Technology', 'Information Technology', 'Systems & Networking', 'Academic Organization'],
        'Networking'              => ['Systems & Networking', 'Information Technology', 'Technology', 'Information Systems'],
        'Arts'                    => ['Arts & Design', 'Creative', 'Creative Services', 'Multimedia', 'Performing Arts', 'Photography', 'Photo & Video Editing', 'Media Production', 'Entertainment'],
        'Gaming'                  => ['Gaming', 'E-Sports', 'Competition', 'Team Strategy', 'Entertainment'],
        'Design'                  => ['Arts & Design', 'Creative', 'Creative Services', 'Multimedia', 'Photography', 'Photo & Video Editing'],
        'Animation'               => ['Multimedia', 'Creative Services', 'Arts & Design', 'Creative', 'Media Production', 'Recording & Production', 'Audio & Audiovisual Media'],
        'Music'                   => ['Music Publishing', 'Singing / Vocal Performance', 'Music Collaboration', 'Recording & Production', 'Performing Arts', 'Audio & Audiovisual Media', 'Entertainment', 'Creative Services', 'Media Production'],
        'Cyber Security'          => ['Information Technology', 'Systems & Networking', 'Technology', 'Information Systems'],
        'Artificial Intelligence' => ['Technology', 'Research', 'Information Technology', 'Academic Organization', 'Innovation'],
        'Analytics'               => ['Research', 'Academic Organization', 'Technology', 'Information Technology'],
        'Machine Learning'        => ['Technology', 'Research', 'Academic Organization', 'Innovation'],
        'Innovation'              => ['Innovation', 'Research', 'Technology', 'Business & Technology Integration', 'Academic Organization'],
        'Leadership'              => ['Leadership', 'Communication', 'Service', 'Community', 'Discipline', 'Academic Organization', 'Educational'],
        'Sports'                  => ['Competition', 'Team Strategy', 'E-Sports', 'Gaming', 'Service', 'Discipline'],
    ];

    public function index(Request $request)
    {
        $user  = $request->user();
        $query = Organization::with(['photos', 'reasons', 'testimonials'])
            ->whereNull('deleted_at');

        if ($search = $request->query('search')) {
            $query->where('org_name', 'like', "%{$search}%");
        }

        if ($category = $request->query('category')) {
            $query->whereJsonContains('category', $category);
        }

        $userProgram = $user->profile?->program;

        $orgs = $query->orderBy('org_name')->get()->filter(function ($org) use ($userProgram) {
            $eligible = $org->eligible_programs;
            return empty($eligible) || in_array($userProgram, $eligible);
        })->values();

        return response()->json(['organizations' => $orgs->map(fn($o) => $this->orgResource($o))]);
    }

    public function show(int $id)
    {
        $org = Organization::with(['photos', 'reasons', 'testimonials', 'events' => function ($q) {
            $q->where('status', 'approved')
              ->orderByDesc('date')
              ->limit(12);
        }])->findOrFail($id);

        return response()->json(['organization' => $this->orgDetailResource($org)]);
    }

    private function orgResource(Organization $org): array
    {
        return [
            'id'       => $org->id,
            'name'     => $org->org_name,
            'category' => $org->category,
            'president'=> $org->president,
            'mission'  => $org->mission,
            'logo'     => $org->logo ? asset('storage/' . $org->logo) : null,
        ];
    }

    private function orgDetailResource(Organization $org): array
    {
        return [
            'id'               => $org->id,
            'name'             => $org->org_name,
            'category'         => $org->category,
            'president'        => $org->president,
            'vision'           => $org->vision,
            'mission'          => $org->mission,
            'room_number'      => $org->room_number,
            'contact_telegram' => $org->contact_telegram,
            'contact_facebook' => $org->contact_facebook,
            'logo'             => $org->logo ? asset('storage/' . $org->logo) : null,
            'photos'           => $org->photos->map(fn($p) => asset('storage/' . $p->photo_path))->values(),
            'event_photos'     => $org->events->filter(fn($e) => $e->event_poster)->map(fn($e) => asset('storage/' . $e->event_poster))->values(),
            'reasons'          => $org->reasons->pluck('reason')->values(),
            'testimonials'     => $org->testimonials->map(fn($t) => [
                'text'   => $t->testimonial,
                'author' => $t->author,
            ])->values(),
            'upcoming_events'  => $org->events->filter(fn($e) => $e->date >= now()->toDateString())->sortBy('date')->map(fn($e) => [
                'id'     => $e->id,
                'title'  => $e->title,
                'date'   => $e->date instanceof \Carbon\Carbon ? $e->date->format('M j, Y') : \Carbon\Carbon::parse($e->date)->format('M j, Y'),
                'venue'  => $e->venue,
                'poster' => $e->event_poster ? asset('storage/' . $e->event_poster) : null,
            ])->values(),
        ];
    }
}
