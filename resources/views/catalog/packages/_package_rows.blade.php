@forelse ($rows as $i => $pkg)
    @include('catalog.packages._package_row', ['i' => $i, 'pkg' => $pkg])
@empty
    {{-- пусто не бывает: контроллер всегда отдаёт хотя бы одну строку --}}
@endforelse
