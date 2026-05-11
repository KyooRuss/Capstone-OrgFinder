<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipRequest;
use App\Models\Organization;
use Illuminate\Http\Request;

class MembershipRequestApiController extends Controller
{
    public function apply(Request $request, int $orgId)
    {
        $org = Organization::findOrFail($orgId);

        if (!$org->is_recruiting) {
            return response()->json(['message' => 'This organization is not currently recruiting.'], 422);
        }

        $user = $request->user();

        $exists = MembershipRequest::where('user_id', $user->id)
            ->where('organization_id', $orgId)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'You have already submitted an application.'], 422);
        }

        $request->validate([
            'social_media_link' => ['required', 'string', 'max:255'],
        ]);

        MembershipRequest::create([
            'user_id'          => $user->id,
            'organization_id'  => $orgId,
            'social_media_link'=> $request->social_media_link,
            'status'           => 'pending',
        ]);

        return response()->json(['message' => 'Application submitted successfully.']);
    }

    public function myStatus(Request $request, int $orgId)
    {
        $req = MembershipRequest::where('user_id', $request->user()->id)
            ->where('organization_id', $orgId)
            ->first();

        return response()->json(['status' => $req?->status ?? null]);
    }
}
