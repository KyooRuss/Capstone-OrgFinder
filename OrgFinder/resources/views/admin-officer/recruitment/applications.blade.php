@extends('admin-officer.layouts.app')

@section('title', 'Recruitment Applications')
@section('page-title', 'Recruitment')
@section('page-subtitle', 'Manage student applications for your organization')

@section('content')

@php
    $filter = request('filter', 'all');
    $counts = [
        'all'      => $requests->count(),
        'pending'  => $requests->where('status', 'pending')->count(),
        'accepted' => $requests->where('status', 'accepted')->count(),
        'declined' => $requests->where('status', 'declined')->count(),
    ];
    $filtered = $filter === 'all' ? $requests : $requests->where('status', $filter);
@endphp

{{-- Recruitment status banner --}}
<div style="display:flex;align-items:center;justify-content:space-between;gap:16px;
            background:#fff;border-radius:14px;padding:20px 24px;margin-bottom:20px;
            box-shadow:0 1px 4px rgba(0,0,0,.06);flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;
                    background:{{ $org->is_recruiting ? '#dcfce7' : '#f1f5f9' }};">
            @if($org->is_recruiting)
                <svg width="22" height="22" viewBox="0 0 24 24" fill="#16a34a"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            @else
                <svg width="22" height="22" viewBox="0 0 24 24" fill="#94a3b8"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            @endif
        </div>
        <div>
            <div style="font-size:14px;font-weight:700;color:#1e2f6e;">{{ $org->org_name }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">
                Recruitment is currently
                <span style="font-weight:700;color:{{ $org->is_recruiting ? '#16a34a' : '#94a3b8' }};">
                    {{ $org->is_recruiting ? 'OPEN' : 'CLOSED' }}
                </span>
                &nbsp;·&nbsp; {{ $counts['pending'] }} pending {{ Str::plural('application', $counts['pending']) }}
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin-officer.recruitment.toggle') }}">
        @csrf
        <button type="submit" style="display:inline-flex;align-items:center;gap:8px;
               padding:10px 22px;border-radius:10px;font-size:13px;font-weight:600;border:none;cursor:pointer;
               background:{{ $org->is_recruiting ? '#4361EE' : '#f1f5f9' }};
               color:{{ $org->is_recruiting ? '#fff' : '#64748b' }};
               transition:all .15s;">
            @if($org->is_recruiting)
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11H7v-2h10v2z"/></svg>
                Close Recruitment
            @else
                <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/></svg>
                Open Recruitment
            @endif
        </button>
    </form>
</div>

{{-- Filter tabs --}}
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
    @foreach(['all' => 'All', 'pending' => 'Pending', 'accepted' => 'Accepted', 'declined' => 'Declined'] as $key => $label)
        <a href="?filter={{ $key }}" style="padding:7px 16px;border-radius:20px;font-size:12px;font-weight:600;
              text-decoration:none;transition:all .15s;
              background:{{ $filter === $key ? '#4361EE' : '#fff' }};
              color:{{ $filter === $key ? '#fff' : '#64748b' }};
              border:1.5px solid {{ $filter === $key ? '#4361EE' : '#e2e8f0' }};">
            {{ $label }}
            <span style="margin-left:4px;padding:1px 7px;border-radius:10px;font-size:11px;
                         background:{{ $filter === $key ? 'rgba(255,255,255,0.25)' : '#f1f5f9' }};
                         color:{{ $filter === $key ? '#fff' : '#94a3b8' }};">
                {{ $counts[$key] }}
            </span>
        </a>
    @endforeach
</div>

{{-- Applications list --}}
@if($filtered->isEmpty())
    <div style="background:#fff;border-radius:14px;padding:60px 20px;text-align:center;
                box-shadow:0 1px 4px rgba(0,0,0,.06);">
        <div style="width:56px;height:56px;border-radius:50%;background:#f1f5f9;
                    display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="#cbd5e1"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
        </div>
        <div style="font-size:14px;font-weight:600;color:#94a3b8;">No {{ $filter === 'all' ? '' : $filter }} applications yet.</div>
        @if(!$org->is_recruiting)
            <div style="font-size:12px;color:#cbd5e1;margin-top:6px;">Open recruitment so students can apply.</div>
        @endif
    </div>
@else
    <div style="display:flex;flex-direction:column;gap:10px;">
        @foreach($filtered as $req)
        @php
            $initials = strtoupper(substr($req->user->first_name, 0, 1) . substr($req->user->last_name, 0, 1));
            $statusStyles = [
                'pending'  => ['bg' => '#fef9c3', 'color' => '#854d0e', 'dot' => '#eab308'],
                'accepted' => ['bg' => '#dcfce7', 'color' => '#166534', 'dot' => '#16a34a'],
                'declined' => ['bg' => '#fee2e2', 'color' => '#991b1b', 'dot' => '#dc2626'],
            ];
            $s = $statusStyles[$req->status];
        @endphp
        <div style="background:#fff;border-radius:14px;padding:18px 20px;
                    box-shadow:0 1px 4px rgba(0,0,0,.06);
                    display:flex;align-items:center;gap:16px;flex-wrap:wrap;">

            {{-- Avatar --}}
            <div style="width:44px;height:44px;border-radius:50%;background:linear-gradient(135deg,#7b6de8,#4361EE);
                        display:flex;align-items:center;justify-content:center;flex-shrink:0;
                        font-size:15px;font-weight:700;color:#fff;">
                {{ $initials }}
            </div>

            {{-- Student info --}}
            <div style="flex:1;min-width:160px;">
                <div style="font-size:14px;font-weight:700;color:#1e2f6e;">
                    {{ $req->user->first_name }} {{ $req->user->last_name }}
                </div>
                <div style="font-size:11px;color:#94a3b8;margin-top:2px;">{{ $req->user->email }}</div>
            </div>

            {{-- Program / Year --}}
            <div style="min-width:140px;">
                <div style="font-size:11px;color:#94a3b8;margin-bottom:2px;">Program</div>
                <div style="font-size:12px;font-weight:600;color:#475569;">
                    {{ $req->user->profile?->program ?? '—' }}
                    @if($req->user->profile?->year_level)
                        · {{ $req->user->profile->year_level }}{{ ['st','nd','rd'][$req->user->profile->year_level - 1] ?? 'th' }} Year
                    @endif
                </div>
            </div>

            {{-- Student No --}}
            <div style="min-width:110px;">
                <div style="font-size:11px;color:#94a3b8;margin-bottom:2px;">Student No.</div>
                <div style="font-size:12px;font-weight:600;color:#475569;">{{ $req->user->student_number ?? '—' }}</div>
            </div>

            {{-- Social Media --}}
            <div style="min-width:130px;">
                <div style="font-size:11px;color:#94a3b8;margin-bottom:2px;">Social Media</div>
                <a href="{{ $req->social_media_link }}" target="_blank"
                   style="font-size:12px;font-weight:600;color:#4361EE;text-decoration:none;
                          display:flex;align-items:center;gap:4px;">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                    View Profile
                </a>
            </div>

            {{-- Applied date --}}
            <div style="min-width:80px;">
                <div style="font-size:11px;color:#94a3b8;margin-bottom:2px;">Applied</div>
                <div style="font-size:12px;color:#475569;">{{ $req->created_at->format('M j, Y') }}</div>
            </div>

            {{-- Status badge --}}
            <div>
                <span style="display:inline-flex;align-items:center;gap:5px;
                             background:{{ $s['bg'] }};color:{{ $s['color'] }};
                             padding:5px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:capitalize;">
                    <span style="width:6px;height:6px;border-radius:50%;background:{{ $s['dot'] }};"></span>
                    {{ $req->status }}
                </span>
            </div>

            {{-- Actions --}}
            @if($req->status === 'pending')
            <div style="display:flex;gap:8px;flex-shrink:0;">
                <form method="POST" action="{{ route('admin-officer.recruitment.status', $req) }}">
                    @csrf
                    <input type="hidden" name="status" value="accepted">
                    <button type="submit" style="display:inline-flex;align-items:center;gap:5px;
                           background:#4361EE;color:#fff;border:none;padding:8px 16px;
                           border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        Accept
                    </button>
                </form>
                <form method="POST" action="{{ route('admin-officer.recruitment.status', $req) }}">
                    @csrf
                    <input type="hidden" name="status" value="declined">
                    <button type="submit" style="display:inline-flex;align-items:center;gap:5px;
                           background:#f1f5f9;color:#64748b;border:none;padding:8px 16px;
                           border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
                        Decline
                    </button>
                </form>
            </div>
            @endif
        </div>
        @endforeach
    </div>
@endif

@endsection
