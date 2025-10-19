<x-filament::page>
    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ __('notifications.title') }}
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mt-1">
                    {{ __('notifications.subtitle') }}
                </p>
            </div>

            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                @if($this->getNotificationsCount() > 0)
                <span class="px-3 py-1 bg-orange-100 text-orange-800 rounded-full text-sm font-medium">
                    {{ $this->getNotificationsCount() }} {{ __('notifications.unread_count') }}
                </span>
                @endif
            </div>
        </div>

        <x-filament::section>
            {{ $this->table }}
        </x-filament::section>
    </div>
</x-filament::page>