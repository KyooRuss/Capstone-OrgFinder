@php $uid = $inputId ?? 'programSelect'; @endphp

<div class="prog-select-wrap" id="{{ $uid }}Wrap">
    {{-- Trigger button --}}
    <div class="prog-select-btn" id="{{ $uid }}Btn" onclick="toggleProgDrop('{{ $uid }}')">
        <div class="prog-tags" id="{{ $uid }}Tags">
            <span class="prog-placeholder" id="{{ $uid }}Placeholder">All Programs</span>
        </div>
        <svg width="14" height="14" viewBox="0 0 24 24" fill="#64748b"><path d="M7 10l5 5 5-5z"/></svg>
    </div>

    {{-- Dropdown --}}
    <div class="prog-drop" id="{{ $uid }}Drop" style="display:none;">
        <div class="prog-search-wrap">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" placeholder="Search program..." oninput="filterProgs('{{ $uid }}', this.value)"
                   style="border:none;outline:none;font-size:13px;width:100%;background:transparent;">
        </div>
        <div class="prog-options" id="{{ $uid }}Options">
            @foreach($programs as $prog)
            <div class="prog-option {{ in_array($prog, $selectedPrograms) ? 'selected' : '' }}"
                 id="{{ $uid }}_opt_{{ $prog }}"
                 onclick="toggleProg('{{ $uid }}', '{{ $prog }}')">
                <span>{{ $prog }}</span>
                <svg class="prog-check" width="15" height="15" viewBox="0 0 24 24" fill="#1a2e78"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Hidden inputs for form submission --}}
    <div id="{{ $uid }}Inputs">
        @foreach($selectedPrograms as $prog)
        <input type="hidden" name="eligible_programs[]" value="{{ $prog }}">
        @endforeach
    </div>
</div>

<style>
.prog-select-wrap { position:relative; }
.prog-select-btn {
    display:flex; align-items:center; justify-content:space-between;
    border:1.5px solid #e2e8f0; border-radius:8px; padding:8px 12px;
    background:#f8fafc; cursor:pointer; min-height:42px; gap:8px;
    transition:border-color .15s;
}
.prog-select-btn:hover { border-color:#1a2e78; }
.prog-tags { display:flex; flex-wrap:wrap; gap:5px; flex:1; }
.prog-placeholder { font-size:13px; color:#94a3b8; }
.prog-tag {
    display:inline-flex; align-items:center; gap:4px;
    background:#e0e7ff; color:#1a2e78; font-size:12px; font-weight:600;
    border-radius:5px; padding:2px 8px;
}
.prog-tag-remove { cursor:pointer; font-size:14px; line-height:1; opacity:.7; }
.prog-tag-remove:hover { opacity:1; }
.prog-drop {
    position:absolute; top:calc(100% + 4px); left:0; right:0; z-index:100;
    background:#fff; border:1.5px solid #e2e8f0; border-radius:10px;
    box-shadow:0 8px 24px rgba(0,0,0,0.1); overflow:hidden;
}
.prog-search-wrap {
    display:flex; align-items:center; gap:8px;
    padding:10px 12px; border-bottom:1px solid #f1f5f9;
}
.prog-options { max-height:200px; overflow-y:auto; }
.prog-option {
    display:flex; align-items:center; justify-content:space-between;
    padding:9px 14px; font-size:13px; color:#374151; cursor:pointer;
    transition:background .1s;
}
.prog-option:hover { background:#f0f4ff; }
.prog-option.selected { background:#eef2ff; color:#1a2e78; font-weight:600; }
.prog-option .prog-check { display:none; }
.prog-option.selected .prog-check { display:block; }
.prog-option-hidden { display:none; }
</style>

<script>
(function() {
    const uid   = '{{ $uid }}';
    const init  = @json($selectedPrograms);

    function render() {
        const tags    = document.getElementById(uid + 'Tags');
        const ph      = document.getElementById(uid + 'Placeholder');
        const inputs  = document.getElementById(uid + 'Inputs');
        const selected = [...document.querySelectorAll('#' + uid + 'Options .prog-option.selected')]
                            .map(el => el.dataset.val);

        // Tags
        tags.innerHTML = '';
        if (!selected.length) {
            tags.appendChild(ph || Object.assign(document.createElement('span'), {
                className:'prog-placeholder', textContent:'All Programs'
            }));
        } else {
            selected.forEach(v => {
                const tag = document.createElement('span');
                tag.className = 'prog-tag';
                tag.innerHTML = `${v}<span class="prog-tag-remove" onclick="removeProg(event,'${uid}','${v}')">×</span>`;
                tags.appendChild(tag);
            });
        }

        // Hidden inputs
        inputs.innerHTML = selected.map(v =>
            `<input type="hidden" name="eligible_programs[]" value="${v}">`
        ).join('');
    }

    // Init selected state from blade
    init.forEach(v => {
        const opt = document.getElementById(uid + '_opt_' + v);
        if (opt) { opt.classList.add('selected'); opt.dataset.val = v; }
    });
    // Set data-val on all options
    document.querySelectorAll('#' + uid + 'Options .prog-option').forEach(el => {
        if (!el.dataset.val) el.dataset.val = el.querySelector('span').textContent.trim();
    });

    render();

    window['toggleProgDrop'] = window['toggleProgDrop'] || function(id) {
        const drop = document.getElementById(id + 'Drop');
        const isOpen = drop.style.display !== 'none';
        // Close all other dropdowns
        document.querySelectorAll('.prog-drop').forEach(d => d.style.display = 'none');
        drop.style.display = isOpen ? 'none' : 'block';
        if (!isOpen) document.getElementById(id + 'Drop').querySelector('input').focus();
    };

    window['toggleProg'] = function(id, val) {
        const opt = document.getElementById(id + '_opt_' + val);
        opt.classList.toggle('selected');
        render(id); // re-render won't work — scoped, call local render
        // Trigger render for the correct instance
        document.getElementById(id + 'Wrap').dispatchEvent(new Event('prog-change'));
    };

    window['removeProg'] = function(e, id, val) {
        e.stopPropagation();
        const opt = document.getElementById(id + '_opt_' + val);
        if (opt) opt.classList.remove('selected');
        document.getElementById(id + 'Wrap').dispatchEvent(new Event('prog-change'));
    };

    window['filterProgs'] = function(id, q) {
        document.querySelectorAll('#' + id + 'Options .prog-option').forEach(el => {
            const matches = el.dataset.val.toLowerCase().includes(q.toLowerCase());
            el.classList.toggle('prog-option-hidden', !matches);
            el.style.display = matches ? '' : 'none';
        });
    };

    document.getElementById(uid + 'Wrap').addEventListener('prog-change', render);

    // Close on outside click
    document.addEventListener('click', function(e) {
        if (!document.getElementById(uid + 'Wrap').contains(e.target)) {
            document.getElementById(uid + 'Drop').style.display = 'none';
        }
    });
})();
</script>
