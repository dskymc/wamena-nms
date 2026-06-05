<a href="{{ $href ?? route('dashboard') }}" {{ $attributes->merge(['class' => 'inline-flex flex-col items-center']) }}>
    <img src="{{ asset(config('nms.branding.logo')) }}"
         alt="{{ config('nms.branding.organization') }}"
         class="nms-img-logo-sm mb-3"
         style="height:5rem;width:5rem;max-height:5rem;max-width:5rem;">
    <span class="block text-xl font-bold text-indigo-700">{{ config('nms.branding.name') }}</span>
    <span class="mt-1 block max-w-xs text-center text-xs text-gray-600">{{ config('nms.branding.full_name') }}</span>
    <span class="mt-1 block text-center text-xs text-gray-400">{{ config('nms.branding.organization') }}</span>
</a>
