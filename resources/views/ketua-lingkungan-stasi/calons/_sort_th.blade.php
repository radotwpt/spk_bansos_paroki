@php
    $isCurrent = $currentSort === $col;
    $newDir    = ($isCurrent && $currentDir === 'asc') ? 'desc' : 'asc';
    $sortUrl   = route('ketua-lingkungan-stasi.calons.index', array_merge($query, [
        'sort'      => $col,
        'direction' => $newDir,
    ]));
@endphp
<a href="{{ $sortUrl }}" class="sort-link" title="Urutkan berdasarkan {{ $label }}">
    {{ $label }}
    <span class="sort-icon {{ $isCurrent ? $currentDir : '' }}">
        <span class="arr-up"></span>
        <span class="arr-down"></span>
    </span>
</a>
