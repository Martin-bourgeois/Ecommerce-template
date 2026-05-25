<div>
    <ul class="space-y-1">
        @forelse ($categories as $category)
            <li>
                <a href="{{ route('catalog.index', ['category' => $category['slug']]) }}" 
                   class="block px-4 py-2 text-gray-800 hover:bg-blue-50 rounded">
                    {{ $category['name'] }}
                </a>
            </li>
        @empty
            <li class="text-gray-500 px-4 py-2">Aucune catégorie</li>
        @endforelse
    </ul>
</div>
