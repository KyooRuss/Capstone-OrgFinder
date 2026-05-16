@php
$categoryList = [
    // Arts & Culture
    'Arts & Design','Creative','Creative Services','Multimedia','Performing Arts',
    'Photography','Photo & Video Editing','Media Production','Entertainment',
    'Singing','Music Publishing','Music Collaboration',
    'Recording & Production','Audio & Audiovisual Media','Dancing','Choreography',
    // Technology
    'Technology','Programming','Software Development','Information Technology',
    'Systems & Networking','Information Systems','Business & Technology Integration',
    'Research','Innovation','Academic Organization',
    // Gaming & Competition
    'Gaming','E-Sports','Competition','Team Strategy',
    // Leadership & Service
    'Leadership','Communication','Service','Community','Discipline','Educational',
    'Guidance & Counseling','Mental Health','Mental First Aid','Sign Language',
    // Other
    'Non-Academic',
];
$selectedCategories = $selectedCategories ?? [];
@endphp

<style>
.cat-tags-box{border:1px solid #e2e8f0;border-radius:8px;padding:6px 8px;min-height:38px;display:flex;flex-wrap:wrap;gap:5px;align-items:center;background:#fff;cursor:text;}
.cat-tags-box:focus-within{border-color:#93c5fd;box-shadow:0 0 0 3px rgba(59,130,246,.1);}
.cat-tag{display:inline-flex;align-items:center;gap:4px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;border-radius:20px;padding:2px 8px 2px 10px;font-size:12px;font-weight:600;}
.cat-tag button{background:none;border:none;cursor:pointer;color:#2563eb;font-size:14px;line-height:1;padding:0 1px;}
.cat-tag button:hover{color:#dc2626;}
.cat-type-input{border:none;outline:none;font-size:13px;min-width:140px;flex:1;padding:2px 0;}
.cat-dropdown{position:absolute;top:calc(100% + 4px);left:0;right:0;background:#fff;border:1px solid #e2e8f0;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.08);max-height:190px;overflow-y:auto;z-index:300;display:none;}
.cat-dd-item{padding:8px 12px;font-size:13px;color:#374151;cursor:pointer;}
.cat-dd-item:hover,.cat-dd-item.active{background:#eff6ff;color:#2563eb;}
.cat-dd-item.dimmed{opacity:.4;pointer-events:none;}
.cat-dd-empty{padding:10px 12px;font-size:12px;color:#94a3b8;font-style:italic;}
</style>

<div style="position:relative;" id="catWrap">
    <div class="cat-tags-box" id="catTagsBox" onclick="document.getElementById('catTypeInput').focus()">
        <div id="catTagsContainer">
            @foreach($selectedCategories as $cat)
            <span class="cat-tag">
                {{ $cat }}
                <button type="button" tabindex="-1" onclick="catRemoveTag(this, '{{ addslashes($cat) }}')">×</button>
                <input type="hidden" name="categories[]" value="{{ $cat }}">
            </span>
            @endforeach
        </div>
        <input type="text" id="catTypeInput" class="cat-type-input"
               placeholder="{{ count($selectedCategories) ? '' : 'Search or type a category...' }}"
               autocomplete="off">
    </div>
    <div class="cat-dropdown" id="catDropdown">
        @foreach($categoryList as $cat)
        <div class="cat-dd-item" data-value="{{ $cat }}">{{ $cat }}</div>
        @endforeach
        <div class="cat-dd-empty" id="catDdEmpty" style="display:none;">Press Enter to add custom category</div>
    </div>
</div>
<div style="font-size:11px;color:#94a3b8;margin-top:4px;">Pick 2–3 categories. Type a custom one + press Enter if not listed.</div>

<script>
(function () {
    const input    = document.getElementById('catTypeInput');
    const dropdown = document.getElementById('catDropdown');
    const tagsBox  = document.getElementById('catTagsContainer');
    const items    = dropdown.querySelectorAll('.cat-dd-item');
    const ddEmpty  = document.getElementById('catDdEmpty');

    function getSelected() {
        return [...tagsBox.querySelectorAll('input[type=hidden]')].map(i => i.value);
    }

    function renderDropdown(q) {
        q = q.trim().toLowerCase();
        const selected = getSelected().map(s => s.toLowerCase());
        let anyVisible = false;
        items.forEach(item => {
            const val = item.dataset.value.toLowerCase();
            const matchesSearch = !q || val.includes(q);
            const alreadyPicked = selected.includes(val);
            item.style.display = matchesSearch ? '' : 'none';
            item.classList.toggle('dimmed', alreadyPicked);
            if (matchesSearch) anyVisible = true;
        });
        ddEmpty.style.display = (!anyVisible && q) ? '' : 'none';
        dropdown.style.display = (anyVisible || (q && !anyVisible)) ? 'block' : 'none';
    }

    function addTag(value) {
        value = value.trim();
        if (!value) return;
        const selected = getSelected().map(s => s.toLowerCase());
        if (selected.includes(value.toLowerCase())) return;
        const span = document.createElement('span');
        span.className = 'cat-tag';
        span.innerHTML = `${value}<button type="button" tabindex="-1" onclick="catRemoveTag(this,'${value.replace(/'/g,"\\'")}')">×</button><input type="hidden" name="categories[]" value="${value}">`;
        tagsBox.appendChild(span);
        input.value = '';
        input.placeholder = '';
        renderDropdown('');
    }

    input.addEventListener('input',  () => renderDropdown(input.value));
    input.addEventListener('focus',  () => renderDropdown(input.value));
    input.addEventListener('blur',   () => setTimeout(() => { dropdown.style.display = 'none'; }, 180));
    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); addTag(input.value); }
    });

    items.forEach(item => {
        item.addEventListener('mousedown', () => addTag(item.dataset.value));
    });
})();

function catRemoveTag(btn, value) {
    btn.closest('.cat-tag').remove();
    const input = document.getElementById('catTypeInput');
    if (!document.querySelectorAll('#catTagsContainer .cat-tag').length) {
        input.placeholder = 'Search or type a category...';
    }
}
</script>
