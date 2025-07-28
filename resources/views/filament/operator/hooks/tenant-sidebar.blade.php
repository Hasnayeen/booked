@auth()
    <aside class="fi-tenant-sidebar fixed inset-y-0 start-0 flex flex-col h-screen content-start bg-white transition-all dark:bg-gray-900 lg:bg-transparent lg:shadow-none lg:ring-0 lg:transition-none dark:lg:bg-transparent fi-main-sidebar fi-sidebar-open w-16 translate-x-0 shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10 rtl:-translate-x-0">
        @if(filament()->hasTenancy())
            @php
                $currentTenant = filament()->getTenant();
                $currentTenantName = filament()->getTenantName($currentTenant);
                $items = filament()->getTenantMenuItems();

                $billingItem = $items['billing'] ?? null;
                $billingItemUrl = $billingItem?->getUrl();
                $isBillingItemVisible = $billingItem?->isVisible() ?? true;
                $hasBillingItem = (filament()->hasTenantBilling() || filled($billingItemUrl)) && $isBillingItemVisible;

                $registrationItem = $items['register'] ?? null;
                $registrationItemUrl = $registrationItem?->getUrl();
                $isRegistrationItemVisible = $registrationItem?->isVisible() ?? true;
                $hasRegistrationItem = ((filament()->hasTenantRegistration() && filament()->getTenantRegistrationPage()::canView()) || filled($registrationItemUrl)) && $isRegistrationItemVisible;

                $profileItem = $items['profile'] ?? null;
                $profileItemUrl = $profileItem?->getUrl();
                $isProfileItemVisible = $profileItem?->isVisible() ?? true;
                $hasProfileItem = ((filament()->hasTenantProfile() && filament()->getTenantProfilePage()::canView($currentTenant)) || filled($profileItemUrl)) && $isProfileItemVisible;

                $tenants = array_filter(
                    filament()->getUserTenants(filament()->auth()->user()),
                    fn (\Illuminate\Database\Eloquent\Model $tenant): bool => ! $tenant->is($currentTenant),
                );

                $items = \Illuminate\Support\Arr::except($items, ['billing', 'profile', 'register']);
            @endphp
        @endif

        <div class="mt-7">
            <div class="w-16 h-16 flex justify-center items-center">
                <div class="bg-[#F5DCDC] p-2 rounded-lg">
                    @if ($homeUrl = filament()->getHomeUrl())
                        <a {{ \Filament\Support\generate_href_html($homeUrl) }}>
                            <x-filament-panels::logo />
                        </a>
                    @else
                        <x-filament-panels::logo />
                    @endif
                </div>
            </div>
        </div>

        @if(filament()->hasTenancy())
            <div class="flex flex-col grow justify-start space-y-6">
                <div class="border-b border-t border-gray-200 py-3 flex flex-col space-y-3">
                    <div class="flex items-center justify-center">
                        <x-filament::link class="fi-avatar fi-size-lg bg-gray-200 dark:bg-slate-800 hover:bg-gray-300 dark:hover:bg-slate-950" icon="lucide-contact" :href="route('filament.operator.tenant.profile', ['tenant' => $currentTenant])" />
                    </div>
                    <div class="flex items-center justify-center">
                        <x-filament::link class="fi-avatar fi-size-lg ring-2 ring-gray-200 dark:ring-slate-800 hover:ring-primary-200 dark:hover:ring-slate-950" icon="lucide-plus" :href="route('filament.operator.tenant.registration')" />
                    </div>
                </div>

                <div class="flex items-center justify-center">
                    <x-filament-panels::avatar.tenant
                        :tenant="$currentTenant"
                        class="border-2 ring-2 border-primary-500/50 ring-primary-500/50"
                        size="lg"
                        x-tooltip="{content: '{{ filament()->getTenantName($currentTenant) }}', theme: $store.theme}"
                    />
                </div>

                @foreach ($tenants as $tenant)
                    <x-filament::link :href="filament()->getUrl($tenant)" tooltip="{{ filament()->getTenantName($tenant) }}">
                        <x-filament::avatar
                            :src="filament()->getTenantAvatarUrl($tenant)"
                            alt="Dan Harrin"
                            :circular="false"
                            size="lg"
                        />
                    </x-filament::link>
                @endforeach
            </div>
        @endif

    </aside>
@endauth
