<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'student');

        if ($request->filled('search')) {
            $query->where('last_name', 'like', '%' . $request->search . '%')
                  ->orWhere('first_name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('filter') && in_array($request->filter, ['active', 'blocked'])) {
            $query->where('status', $request->filter);
        }

        $students = $query->with('profile')->latest()->get()->map(function ($user, $index) {
            return [
                'id'             => $user->id,
                'student_number' => $user->profile?->student_number ?? 'S' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'first_name'     => $user->first_name,
                'last_name'      => $user->last_name,
                'year_level'     => $user->profile?->year_level ?? '—',
                'email'          => $user->email,
                'status'         => $user->status,
            ];
        });


        return view('super-admin.students.index', compact('students'));
    }

    public function search(Request $request)
    {
        $q = $request->input('q', '');
        $excludeOrgId = $request->input('org_id');

        $query = User::where('role', 'student')
            ->where('status', 'active')
            ->where(function ($query) use ($q) {
                $query->where('first_name', 'like', "%{$q}%")
                      ->orWhere('last_name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            });

        if ($excludeOrgId) {
            $query->whereDoesntHave('organizationAccess', function ($sub) use ($excludeOrgId) {
                $sub->where('organization_id', $excludeOrgId);
            });
        }

        $students = $query->with('profile')->limit(8)->get()->map(fn($u) => [
            'id'         => $u->id,
            'name'       => trim($u->first_name . ' ' . $u->last_name),
            'email'      => $u->email,
            'year_level' => $u->profile?->year_level ?? '—',
        ]);

        return response()->json($students);
    }

    public function makeAdmin(User $user)
    {
        $user->update(['role' => 'admin_officer']);

        return response()->json(['message' => 'Student promoted to admin officer.']);
    }

    public function block(User $user)
    {
        $user->update(['status' => 'blocked']);

        return response()->json(['message' => 'Student blocked successfully.']);
    }

    public function unblock(User $user)
    {
        $user->update(['status' => 'active']);

        return response()->json(['message' => 'Student unblocked successfully.']);
    }

    public function destroy(User $user)
    {
        $user->delete();

        return response()->json(['message' => 'Student moved to trash.']);
    }
}
