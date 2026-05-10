@extends('super-admin.layouts.app')

@section('title', 'Event History')
@section('page-title', 'Event History')
@section('page-subtitle', 'All approved and rejected events')

@section('content')
<div class="card">
    <div class="toolbar">
        <div class="toolbar-left">Total Events: {{ $events->count() }}</div>
        <form method="GET" style="display:flex;gap:8px;align-items:center;">
            <div class="search-box">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="6"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" name="search" placeholder="Search events..." value="{{ request('search') }}">
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Organization</th>
                    <th>Event Title</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Venue</th>
                    <th>Status</th>
                    <th style="text-align:center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($events as $event)
                <tr>
                    <td>{{ $event->organization->org_name }}</td>
                    <td>{{ $event->title }}</td>
                    <td>{{ $event->date->format('m-d-Y') }}</td>
                    <td>{{ $event->time ? date('g:i A', strtotime($event->time)) : '—' }}</td>
                    <td>{{ $event->venue ?? '—' }}</td>
                    <td>
                        <span class="badge badge-{{ $event->status }}">{{ ucfirst($event->status) }}</span>
                    </td>
                    <td style="text-align:center">
                        <button class="btn btn-primary btn-sm" onclick="viewEvent({{ $event->id }})">View Details</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center;color:#94a3b8;padding:40px;">No event history found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Event Detail Modal --}}
<div class="modal-overlay" id="eventModal">
    <div class="modal" style="max-width:600px;">
        <button class="modal-close" onclick="closeModal('eventModal')">×</button>
        <div style="display:flex;gap:20px;">
            <div id="eventPosterWrap" style="width:160px;min-height:200px;background:#e2e8f0;border-radius:10px;flex-shrink:0;overflow:hidden;">
                <img id="eventPoster" src="" style="width:100%;height:100%;object-fit:cover;display:none;">
            </div>
            <div style="flex:1;">
                <div id="eventStatusBadge" style="margin-bottom:8px;"></div>
                <div id="eventTitle" style="font-size:17px;font-weight:700;color:#1e3a5c;margin-bottom:10px;"></div>
                <div style="display:flex;flex-direction:column;gap:5px;margin-bottom:14px;">
                    <span id="eventDate" style="font-size:13px;color:#64748b;"></span>
                    <span id="eventTime" style="font-size:13px;color:#64748b;"></span>
                    <span id="eventLocation" style="font-size:13px;color:#64748b;"></span>
                </div>
                <div style="margin-bottom:12px;">
                    <p style="font-size:12px;font-weight:700;color:#1e3a5c;margin-bottom:4px;">About This Event</p>
                    <p id="eventDesc" style="font-size:13px;color:#64748b;line-height:1.6;"></p>
                </div>
                <div>
                    <p style="font-size:12px;font-weight:700;color:#1e3a5c;margin-bottom:6px;">What you will gain</p>
                    <ul id="eventGains" style="list-style:disc;padding-left:18px;font-size:13px;color:#64748b;line-height:1.8;"></ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function viewEvent(id) {
    fetch(`/super-admin/events/${id}`, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(e => {
        document.getElementById('eventTitle').textContent = `${e.title} (${e.organization})`;
        document.getElementById('eventDate').textContent = '📅 ' + e.date;
        document.getElementById('eventTime').textContent = '🕐 ' + (e.time || 'TBA');
        document.getElementById('eventLocation').textContent = '📍 ' + (e.venue || 'TBA');
        document.getElementById('eventDesc').textContent = e.description || '';
        document.getElementById('eventGains').innerHTML = (e.benefits || []).map(g => `<li>${g}</li>`).join('');

        const poster = document.getElementById('eventPoster');
        if (e.poster) { poster.src = e.poster; poster.style.display = 'block'; }
        else { poster.style.display = 'none'; }

        const badge = document.getElementById('eventStatusBadge');
        const colors = { approved: 'badge-approved', rejected: 'badge-rejected', pending: 'badge-pending' };
        badge.innerHTML = `<span class="badge ${colors[e.status]}">${e.status.charAt(0).toUpperCase() + e.status.slice(1)}</span>`;

        openModal('eventModal');
    });
}
</script>
@endpush
