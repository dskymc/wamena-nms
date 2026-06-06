<aside class="nms-sidebar is-closed transition-transform duration-200 ease-in-out"
       :class="sidebarOpen ? 'is-open' : 'is-closed'">
    <div class="flex shrink-0 items-center gap-3 border-b border-gray-100 px-5 py-4">
        <img src="{{ asset(config('nms.branding.logo')) }}"
             alt="{{ config('nms.branding.organization') }}"
             class="nms-img-logo-sm">
        <div class="min-w-0 leading-tight">
            <p class="truncate text-sm font-bold text-indigo-700">{{ config('nms.branding.name') }}</p>
            <p class="mt-0.5 text-[10px] leading-snug text-gray-500">{{ config('nms.branding.organization') }}</p>
        </div>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-sidebar-link>
        @can('locations.view')
            <x-sidebar-link :href="route('admin.locations.index')" :active="request()->routeIs('admin.locations.*')">
                Lokasi
            </x-sidebar-link>
        @endcan
        @can('snmp_profiles.view')
            <x-sidebar-link :href="route('admin.snmp-profiles.index')" :active="request()->routeIs('admin.snmp-profiles.*')">
                Profil SNMP
            </x-sidebar-link>
        @endcan
        @can('devices.view')
            <x-sidebar-link :href="route('admin.devices.index')" :active="request()->routeIs('admin.devices.*')">
                Perangkat
            </x-sidebar-link>
        @endcan
        @can('alert_rules.view')
            <x-sidebar-link :href="route('admin.alert-rules.index')" :active="request()->routeIs('admin.alert-rules.*')">
                Alert Rules
            </x-sidebar-link>
        @endcan
        @can('alert_events.view')
            <x-sidebar-link :href="route('admin.alert-events.index')" :active="request()->routeIs('admin.alert-events.*')">
                Alert Log
            </x-sidebar-link>
        @endcan
        @can('notification_settings.update')
            <x-sidebar-link :href="route('admin.notification-settings.edit')" :active="request()->routeIs('admin.notification-settings.*')">
                Notifikasi
            </x-sidebar-link>
        @endcan
        @can('users.view')
            <x-sidebar-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                Pengguna
            </x-sidebar-link>
        @endcan
    </nav>

    <div class="shrink-0 border-t border-gray-100 px-4 py-4">
        <div class="mb-3 min-w-0">
            <p class="truncate text-sm font-medium text-gray-800">{{ Auth::user()->name }}</p>
            <p class="truncate text-xs text-gray-500">{{ Auth::user()->email }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('profile.edit') }}"
               class="flex-1 rounded-md bg-gray-100 px-3 py-2 text-center text-xs font-medium text-gray-700 transition hover:bg-gray-200">
                Profil
            </a>
            <form method="POST" action="{{ route('logout') }}" class="flex-1">
                @csrf
                <button type="submit"
                        class="w-full rounded-md bg-indigo-50 px-3 py-2 text-xs font-medium text-indigo-700 transition hover:bg-indigo-100">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</aside>

<div x-show="sidebarOpen"
     x-transition.opacity
     @click="sidebarOpen = false"
     class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
     x-cloak></div>
