<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    public function organizations(Request $request)
    {
        $query = Organization::onlyTrashed()
            ->withCount(['accessUsers as members_count', 'events as events_count']);

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $organizations = $query->latest('deleted_at')->get();

        return view('super-admin.trash.organizations', compact('organizations'));
    }

    public function events(Request $request)
    {
        $query = Event::onlyTrashed()->with('organization');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $events = $query->latest('deleted_at')->get();

        return view('super-admin.trash.events', compact('events'));
    }

    public function adminOfficers(Request $request)
    {
        $query = User::onlyTrashed()
            ->where('role', 'admin_officer')
            ->with(['organizationAccess.organization']);

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        $officers = $query->latest('deleted_at')->get()->map(function ($user, $index) {
            $access = $user->organizationAccess->first();
            return (object) [
                'id'           => $user->id,
                'name'         => $user->name,
                'last_name'    => $user->last_name,
                'first_name'   => $user->first_name,
                'email'        => $user->email,
                'admin_number' => 'A' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'organization' => $access?->organization?->org_name ?? '—',
                'position'     => $access?->position ?? '—',
            ];
        });

        return view('super-admin.trash.admin-officers', compact('officers'));
    }

    public function students(Request $request)
    {
        $query = User::onlyTrashed()->where('role', 'student');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $students = $query->latest('deleted_at')->get();

        return view('super-admin.trash.students', compact('students'));
    }

    public function restoreOrganization($id)
    {
        Organization::onlyTrashed()->findOrFail($id)->restore();

        return response()->json(['message' => 'Organization restored successfully.']);
    }

    public function forceDeleteOrganization($id)
    {
        $org = Organization::onlyTrashed()->findOrFail($id);
        $org->photos()->each(fn($p) => \Storage::disk('public')->delete($p->photo_path));
        if ($org->logo) \Storage::disk('public')->delete($org->logo);
        $org->forceDelete();

        return response()->json(['message' => 'Organization permanently deleted.']);
    }

    public function restoreEvent($id)
    {
        Event::onlyTrashed()->findOrFail($id)->restore();

        return response()->json(['message' => 'Event restored successfully.']);
    }

    public function forceDeleteEvent($id)
    {
        $e = Event::onlyTrashed()->findOrFail($id);
        if ($e->event_poster) \Storage::disk('public')->delete($e->event_poster);
        $e->forceDelete();

        return response()->json(['message' => 'Event permanently deleted.']);
    }

    public function restoreUser($id)
    {
        User::onlyTrashed()->findOrFail($id)->restore();

        return response()->json(['message' => 'User restored successfully.']);
    }

    public function forceDeleteUser($id)
    {
        User::onlyTrashed()->findOrFail($id)->forceDelete();

        return response()->json(['message' => 'User permanently deleted.']);
    }
}
