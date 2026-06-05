@props(['text'])

<p {{ $attributes->merge(['class' => 'mt-1 text-xs text-gray-500']) }}>
    {{ $text }}
</p>
