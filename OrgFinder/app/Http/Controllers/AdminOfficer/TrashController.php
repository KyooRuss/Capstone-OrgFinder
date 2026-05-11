<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\OrganizationAccess;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class TrashController extends Controller
{
    private function myOrganization()
    {
        return Auth::user()?->organizations()->first();
    }

    public function members(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return view('admin-officer.trash.members', ['members' => collect(), 'org' => null]);
        }

        $query = User::onlyTrashed()
            ->whereHas('organizationAccess', fn($q) => $q->where('organization_id', $org->id))
            ->where('role', 'student');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'blocked'])) {
            $query->where('status', $request->status);
        }

        $members = $query->orderBy('last_name')->get();

        return view('admin-officer.trash.members', compact('members', 'org'));
    }

    public function officers(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return view('admin-officer.trash.officers', ['officers' => collect(), 'org' => null]);
        }

        $query = User::onlyTrashed()
            ->whereHas('organizationAccess', fn($q) => $q->where('organization_id', $org->id))
            ->where('role', 'admin_officer');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('status') && in_array($request->status, ['active', 'blocked'])) {
            $query->where('status', $request->status);
        }

        $officers = $query->orderBy('last_name')->get();

        return view('admin-officer.trash.officers', compact('officers', 'org'));
    }

    public function restoreUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json(['success' => true, 'message' => 'User restored.']);
    }

    public function forceDeleteUser($id)
    {
        $user = User::onlyTrashed()->findOrFail($id);

        $org = $this->myOrganization();
        if ($org) {
            OrganizationAccess::where('organization_id', $org->id)
                ->where('user_id', $user->id)
                ->delete();
        }

        $user->forceDelete();

        return response()->json(['success' => true, 'message' => 'User permanently deleted.']);
    }
}
