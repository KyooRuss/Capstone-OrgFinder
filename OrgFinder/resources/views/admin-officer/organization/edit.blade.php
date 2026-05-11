@extends('admin-officer.layouts.app')

@section('title', 'Edit Organization Profile')
@section('page-title', $organization->name)
@section('page-subtitle', 'Edit Organization Profile')

@push('styles')
<style>
    .edit-grid { display:grid; grid-template-columns:1fr 1fr; gap:24px; }
    @media(max-width:768px){ .edit-grid{grid-template-columns:1fr;} }
    .section-title { font-size:13px;font-weight:700;color:#1e3a5c;text-transform:uppercase;letter-spacing:.5px;margin-bottom:14px; }
    .removable-item { position:relative; margin-bottom:10px; }
    .remove-btn { position:absolute;top:10px;right:8px;background:none;border:none;cursor:pointer;color:#ef4444;font-size:16px;line-height:1;padding:2px 4px;border-radius:4px;display:none; }
    .removable-item:hover .remove-btn { display:block; }
    .removable-item textarea,.removable-item .form-control { padding-right:30px; }
    .btn-add { display:inline-flex;align-items:center;gap:5px;padding:6px 14px;background:#eff6ff;color:#2563eb;border:1px dashed #93c5fd;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer; }
    .btn-add:hover { background:#dbeafe; }
    .photo-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:10px; }
    .existing-photo { aspect-ratio:1;border-radius:10px;overflow:hidden;position:relative;background:#f1f5f9; }
    .existing-photo img { width:100%;height:100%;object-fit:cover; }
    .slot-remove { position:absolute;top:4px;right:4px;background:rgba(239,68,68,.85);color:#fff;border:none;border-radius:50%;width:22px;height:22px;font-size:14px;cursor:pointer;display:none;align-items:center;justify-content:center;z-index:10; }
    .existing-photo:hover .slot-remove,.photo-slot-wrap:hover .slot-remove { display:flex; }
    .photo-slot-wrap { position:relative; }
    .photo-slot { aspect-ratio:1;border:2px dashed #e2e8f0;border-radius:10px;display:flex;align-items:center;justify-content:center;cursor:pointer;background:#f8fafc;overflow:hidden;position:relative; }
</style>
@endpush

@section('content')
@if(session('success'))
<div style="background:#ecfdf5;border:1px solid #6ee7b7;color:#065f46;padding:10px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;">
    {{ session('success') }}
</div>
@endif

<form id="editForm" action="{{ route('admin-officer.organization.update') }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')

    <div class="edit-grid">

        {{-- Left column --}}
        <div>
            {{-- Logo --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="section-title">Logo</div>
                <div style="display:flex;align-items:center;gap:16px;">
                    <div id="logoPreviewWrap" style="width:80px;height:80px;border-radius:50%;overflow:hidden;background:#f1f5f9;flex-shrink:0;">
                        @if($organization->logo)
                        <img id="logoPreview" src="{{ asset('storage/'.$organization->logo) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                        <div id="logoPreview" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#7b6de8,#3b5fe2);font-size:26px;font-weight:700;color:#fff;">
                            {{ strtoupper(substr($organization->name,0,1)) }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <label style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;background:#eff6ff;color:#2563eb;border:1px dashed #93c5fd;border-radius:8px;font-size:12.5px;font-weight:600;cursor:pointer;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="#2563eb"><path d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5z"/></svg>
                            Change Logo
                            <input type="file" name="logo" accept="image/*" style="display:none;" onchange="previewLogo(this)">
                        </label>
                        <div style="font-size:11px;color:#94a3b8;margin-top:5px;">Max 2MB. JPG, PNG.</div>
                    </div>
                </div>
            </div>

            {{-- Category --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="section-title">Category</div>
                @include('super-admin.partials.category-select', ['selectedCategories' => old('categories', $organization->category ?? [])])
            </div>

            {{-- Basic info --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="section-title">Basic Information</div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">President / Contact Person</label>
                    <input type="text" name="president" class="form-control" value="{{ old('president', $organization->president) }}" placeholder="Name of president">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">Room Number</label>
                    <input type="text" name="room_number" class="form-control" value="{{ old('room_number', $organization->room_number) }}" placeholder="e.g. Room 204">
                </div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">Telegram</label>
                    <input type="text" name="contact_telegram" class="form-control" value="{{ old('contact_telegram', $organization->contact_telegram) }}" placeholder="@username">
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">Facebook</label>
                    <input type="text" name="contact_facebook" class="form-control" value="{{ old('contact_facebook', $organization->contact_facebook) }}" placeholder="Facebook page or number">
                </div>
            </div>

            {{-- Vision & Mission --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="section-title">Vision & Mission</div>
                <div style="margin-bottom:12px;">
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">Vision</label>
                    <textarea name="vision" class="form-control" rows="3" placeholder="Organization vision">{{ old('vision', $organization->vision) }}</textarea>
                </div>
                <div>
                    <label style="font-size:12px;font-weight:600;color:#475569;display:block;margin-bottom:5px;">Mission / Reasons to Join</label>
                    <textarea name="mission" class="form-control" rows="5" placeholder="Each line becomes a bullet point in Reasons to Join">{{ old('mission', $organization->mission) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div>
            {{-- Photos --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="section-title">Photos</div>
                <div class="photo-grid" id="photoGrid">
                    @foreach($organization->photos as $photo)
                    <div class="existing-photo">
                        <img src="{{ asset('storage/'.$photo->photo_path) }}" alt="">
                        <button type="button" class="slot-remove"
                                onclick="deletePhoto({{ $photo->id }}, this)">×</button>
                    </div>
                    @endforeach
                    <div class="photo-slot-wrap">
                        <label class="photo-slot">
                            <input type="file" name="photos[]" accept="image/*" onchange="previewNewPhoto(this)" style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="#cbd5e1"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                        </label>
                        <button type="button" class="slot-remove" onclick="this.closest('.photo-slot-wrap').remove()">×</button>
                    </div>
                </div>
                <div style="margin-top:10px;display:flex;justify-content:flex-end;">
                    <button type="button" class="btn-add" onclick="addPhotoSlot()">+ Add Photo</button>
                </div>
            </div>

            {{-- Reasons to Join --}}
            <div class="card" style="margin-bottom:20px;">
                <div class="section-title">Reasons to Join</div>
                <div id="reasonsList">
                    @forelse($organization->reasons as $reason)
                    <div class="removable-item">
                        <textarea name="reasons[]" class="form-control" rows="2" placeholder="Enter a reason to join">{{ old('reasons.'.$loop->index, $reason->reason) }}</textarea>
                        <button type="button" class="remove-btn" onclick="this.closest('.removable-item').remove()">×</button>
                    </div>
                    @empty
                    <div class="removable-item">
                        <textarea name="reasons[]" class="form-control" rows="2" placeholder="Enter a reason to join"></textarea>
                        <button type="button" class="remove-btn" onclick="this.closest('.removable-item').remove()">×</button>
                    </div>
                    @endforelse
                </div>
                <div style="margin-top:10px;display:flex;justify-content:flex-end;">
                    <button type="button" class="btn-add" onclick="addReason()">+ Add Reason</button>
                </div>
            </div>

            {{-- Testimonials --}}
            <div class="card">
                <div class="section-title">Member / Alumni Testimonials</div>
                <div id="testimonialsList">
                    @forelse($organization->testimonials as $testimonial)
                    <div class="removable-item">
                        <input type="text" name="testimonial_authors[]" class="form-control" placeholder="Author name (e.g. Juan dela Cruz, Alumni)" value="{{ old('testimonial_authors.'.$loop->index, $testimonial->author) }}" style="margin-bottom:6px;">
                        <textarea name="testimonials[]" class="form-control" rows="3" placeholder="Enter testimonial">{{ old('testimonials.'.$loop->index, $testimonial->testimonial) }}</textarea>
                        <button type="button" class="remove-btn" onclick="this.closest('.removable-item').remove()">×</button>
                    </div>
                    @empty
                    <div class="removable-item">
                        <input type="text" name="testimonial_authors[]" class="form-control" placeholder="Author name (e.g. Juan dela Cruz, Alumni)" style="margin-bottom:6px;">
                        <textarea name="testimonials[]" class="form-control" rows="3" placeholder="Enter testimonial 1"></textarea>
                        <button type="button" class="remove-btn" onclick="this.closest('.removable-item').remove()">×</button>
                    </div>
                    @endforelse
                </div>
                <div style="margin-top:10px;display:flex;justify-content:flex-end;">
                    <button type="button" class="btn-add" onclick="addTestimonial()">+ Add Testimonial</button>
                </div>
            </div>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:20px;">
        <a href="{{ route('admin-officer.organization.index') }}"
           style="padding:9px 20px;border-radius:8px;border:1px solid #e2e8f0;font-size:13px;font-weight:600;color:#64748b;text-decoration:none;">
            Cancel
        </a>
        <button type="submit" style="padding:9px 24px;background:#4361EE;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;">
            Save Changes
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
let photoCount = 0;

function previewLogo(input) {
    if (!input.files[0]) return;
    const wrap = document.getElementById('logoPreviewWrap');
    const reader = new FileReader();
    reader.onload = e => wrap.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
    reader.readAsDataURL(input.files[0]);
}

function previewNewPhoto(input) {
    if (!input.files[0]) return;
    const label = input.closest('label');
    const reader = new FileReader();
    reader.onload = e => {
        // Remove any existing preview, keep the original input intact
        const existing = label.querySelector('img.slot-preview');
        if (existing) existing.remove();
        const svg = label.querySelector('svg');
        if (svg) svg.style.display = 'none';
        const img = document.createElement('img');
        img.src = e.target.result;
        img.className = 'slot-preview';
        img.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;object-fit:cover;border-radius:8px;pointer-events:none;';
        label.appendChild(img);
    };
    reader.readAsDataURL(input.files[0]);
}

function addPhotoSlot() {
    const grid = document.getElementById('photoGrid');
    const wrap = document.createElement('div');
    wrap.className = 'photo-slot-wrap';
    wrap.innerHTML = `
        <label class="photo-slot">
            <input type="file" name="photos[]" accept="image/*" onchange="previewNewPhoto(this)" style="position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="#cbd5e1"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
        </label>
        <button type="button" class="slot-remove" onclick="this.closest('.photo-slot-wrap').remove()">×</button>`;
    grid.appendChild(wrap);
}

function addReason() {
    const list = document.getElementById('reasonsList');
    const div = document.createElement('div');
    div.className = 'removable-item';
    div.innerHTML = `
        <textarea name="reasons[]" class="form-control" rows="2" placeholder="Enter a reason to join"></textarea>
        <button type="button" class="remove-btn" onclick="this.closest('.removable-item').remove()">×</button>`;
    list.appendChild(div);
}

function addTestimonial() {
    const list = document.getElementById('testimonialsList');
    const div = document.createElement('div');
    div.className = 'removable-item';
    div.innerHTML = `
        <input type="text" name="testimonial_authors[]" class="form-control" placeholder="Author name (e.g. Juan dela Cruz, Alumni)" style="margin-bottom:6px;">
        <textarea name="testimonials[]" class="form-control" rows="3" placeholder="Enter testimonial"></textarea>
        <button type="button" class="remove-btn" onclick="this.closest('.removable-item').remove()">×</button>`;
    list.appendChild(div);
}

function deletePhoto(photoId, btn) {
    if (!confirm('Remove this photo?')) return;
    fetch(`{{ url('admin-officer/organization/photos') }}/${photoId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
    }).then(r => r.json()).then(() => btn.closest('.existing-photo').remove());
}
</script>
@endpush
