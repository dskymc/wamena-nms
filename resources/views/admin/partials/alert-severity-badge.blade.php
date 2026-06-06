@php
    $classes = match ($severity->color()) {
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        default => 'bg-blue-100 text-blue-800',
    };
@endphp
<span class="inline-flex px-2 py-0.5 rounded text-xs font-medium {{ $classes }}">{{ $severity->label() }}</span>
