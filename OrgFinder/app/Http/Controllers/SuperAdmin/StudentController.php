<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'student');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('filter') && in_array($request->filter, ['active', 'blocked'])) {
            $query->where('status', $request->filter);
        }

        $students = $query->latest()->get()->map(function ($user, $index) {
            return [
                'id'             => $user->id,
                'student_number' => $user->student_number ?? 'S' . str_pad($index + 1, 4, '0', STR_PAD_LEFT),
                'name'           => $user->name,
                'year_level'     => $user->year_level ?? '—',
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
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('student_number', 'like', "%{$q}%");
            });

        if ($excludeOrgId) {
            $query->whereDoesntHave('organizationAccess', function ($q) use ($excludeOrgId) {
                $q->where('organization_id', $excludeOrgId);
            });
        }

        $students = $query->limit(8)->get()->map(fn($u) => [
            'id'             => $u->id,
            'name'           => $u->name,
            'email'          => $u->email,
            'student_number' => $u->student_number ?? '—',
            'year_level'     => $u->year_level ?? '—',
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
