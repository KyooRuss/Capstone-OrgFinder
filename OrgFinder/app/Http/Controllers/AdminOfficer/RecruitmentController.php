<?php

namespace App\Http\Controllers\AdminOfficer;

use App\Http\Controllers\Controller;
use App\Models\MembershipRequest;
use App\Models\OrganizationAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecruitmentController extends Controller
{
    private function myOrganization()
    {
        return Auth::user()?->organizations()->first();
    }

    public function toggle(Request $request)
    {
        $org = $this->myOrganization();

        if (!$org) {
            return back()->with('error', 'No organization found.');
        }

        $org->update(['is_recruiting' => !$org->is_recruiting]);

        $status = $org->is_recruiting ? 'opened' : 'closed';

        return back()->with('success', "Recruitment has been {$status}.");
    }

    public function applications()
    {
        $org = $this->myOrganization();

        if (!$org) {
            return redirect()->route('admin-officer.organization.index');
        }

        $requests = MembershipRequest::with(['user.profile'])
            ->where('organization_id', $org->id)
            ->latest()
            ->get();

        return view('admin-officer.recruitment.applications', compact('org', 'requests'));
    }

    public function updateStatus(Request $request, MembershipRequest $membershipRequest)
    {
        $org = $this->myOrganization();

        if (!$org || $membershipRequest->organization_id !== $org->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $request->validate(['status' => ['required', 'in:accepted,declined']]);

        $membershipRequest->update(['status' => $request->status]);

        if ($request->status === 'accepted') {
            OrganizationAccess::firstOrCreate(
                [
                    'organization_id' => $org->id,
                    'user_id'         => $membershipRequest->user_id,
                ],
                ['position' => 'Member']
            );
        }

        return back()->with('success', 'Application status updated.');
    }
}
