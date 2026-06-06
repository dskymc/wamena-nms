@php
    $color = $status->color();
    $classes = match ($color) {
        'green' => 'bg-green-100 text-green-800',
        'red' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-700',
    };
    $extraClass = $extraClass ?? '';
@endphp

<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $classes }} {{ $extraClass }}">
    {{ $status->label() }}
</span>
