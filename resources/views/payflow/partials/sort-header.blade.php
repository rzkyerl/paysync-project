{{--
    sort-header.blade.php
    Renders a sortable column header link inside a <th>.

    Variables:
      $column      — the column key to sort by (e.g. 'name')
      $label       — display text (e.g. 'Nama')
      $currentSort — currently active sort column (e.g. $sortBy from controller)
      $currentDir  — current sort direction: 'asc' or 'desc' (e.g. $sortDir from controller)

    Click behaviour (3-state cycle):
      1st click (column inactive)      → sort ASC
      2nd click (column active, ASC)   → sort DESC
      3rd click (column active, DESC)  → clear sort (back to default)
--}}
@php
    $isActive = $currentSort === $column;

    if ($isActive && $currentDir === 'asc') {
        // 2nd click: go desc
        $sortUrl = request()->fullUrlWithQuery(['sort' => $column, 'dir' => 'desc', 'page' => 1]);
    } elseif ($isActive && $currentDir === 'desc') {
        // 3rd click: clear sort, go back to default
        $currentParams = request()->except(['sort', 'dir', 'page']);
        $currentParams['page'] = 1;
        $sortUrl = request()->url() . (count($currentParams) ? '?' . http_build_query($currentParams) : '');
    } else {
        // 1st click: go asc
        $sortUrl = request()->fullUrlWithQuery(['sort' => $column, 'dir' => 'asc', 'page' => 1]);
    }

    $icon = $isActive ? ($currentDir === 'asc' ? '↑' : '↓') : '↕';
@endphp
<th>
    <a href="{{ $sortUrl }}" class="sort-link {{ $isActive ? 'active' : '' }}">
        {{ $label }}<span class="sort-icon">{{ $icon }}</span>
    </a>
</th>
