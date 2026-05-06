<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\OrganizationPhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrganizationController extends Controller
{
    private function myOrganization()
    {
        return auth()->user()->organizations()->first();
    }

    public function index()
    {
        $organization = $this->myOrganization();

        $eventPosters = $organization
            ? $organization->events()
                ->whereNotNull('poster')
                ->where('status', 'approved')
                ->orderByDesc('date')
                ->get(['id', 'title', 'date', 'poster'])
            : collect();

        $testimonials = $organization
            ? $organization->testimonials
            : collect();

        $photos = $organization
            ? $organization->photos
            : collect();

        return view('admin-officer.organization.index', compact('organization', 'eventPosters', 'testimonials', 'photos'));
    }

    public function edit()
    {
        $organization = $this->myOrganization();

        if (!$organization) {
            return redirect()->route('admin-officer.organization.index');
        }

        $organization->load(['photos', 'testimonials']);

        return view('admin-officer.organization.edit', compact('organization'));
    }

    public function update(Request $request)
    {
        $organization = $this->myOrganization();

        if (!$organization) {
            return redirect()->route('admin-officer.organization.index');
        }

        $validated = $request->validate([
            'vision'           => ['nullable', 'string'],
            'mission'          => ['nullable', 'string'],
            'room_number'      => ['nullable', 'string', 'max:100'],
            'contact_telegram' => ['nullable', 'string', 'max:255'],
            'contact_facebook' => ['nullable', 'string', 'max:255'],
            'president'        => ['nullable', 'string', 'max:255'],
            'logo'             => ['nullable', 'image', 'max:2048'],
            'photos'           => ['nullable', 'array'],
            'photos.*'         => ['image', 'max:2048'],
            'testimonials'     => ['nullable', 'array'],
            'testimonials.*'   => ['nullable', 'string'],
        ]);

        $logoPath = $organization->logo;
        if ($request->hasFile('logo')) {
            if ($logoPath) Storage::disk('public')->delete($logoPath);
            $logoPath = $request->file('logo')->store('organizations/logos', 'public');
        }

        $organization->update([
            'vision'           => $validated['vision'] ?? null,
            'mission'          => $validated['mission'] ?? null,
            'room_number'      => $validated['room_number'] ?? null,
            'contact_telegram' => $validated['contact_telegram'] ?? null,
            'contact_facebook' => $validated['contact_facebook'] ?? null,
            'president'        => $validated['president'] ?? null,
            'logo'             => $logoPath,
        ]);

        // Replace testimonials
        $organization->testimonials()->delete();
        $authors = $request->input('testimonial_authors', []);
        foreach (($validated['testimonials'] ?? []) as $index => $testimonial) {
            if (!empty(trim($testimonial))) {
                $organization->testimonials()->create([
                    'testimonial' => $testimonial,
                    'author'      => $authors[$index] ?? null,
                    'order_index' => $index,
                ]);
            }
        }

        // Add new photos
        if ($request->hasFile('photos')) {
            $existingCount = $organization->photos()->count();
            foreach ($request->file('photos') as $index => $photo) {
                $path = $photo->store('organizations/photos', 'public');
                $organization->photos()->create(['photo_path' => $path, 'order_index' => $existingCount + $index]);
            }
        }

        return redirect()->route('admin-officer.organization.index')
            ->with('success', 'Organization profile updated successfully.');
    }

    public function deletePhoto(OrganizationPhoto $photo)
    {
        $organization = $this->myOrganization();

        if (!$organization || $photo->organization_id !== $organization->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        Storage::disk('public')->delete($photo->photo_path);
        $photo->delete();

        return response()->json(['message' => 'Photo deleted.']);
    }
}
