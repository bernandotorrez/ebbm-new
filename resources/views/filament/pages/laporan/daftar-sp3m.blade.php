<x-filament-panels::page>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            {{ $this->form }}
      
            <div class="mt-4 pt-4">
                <x-filament-panels::form.actions
                    :actions="$this->getFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </div>
         
        </div>
    </div>
</x-filament-panels::page>