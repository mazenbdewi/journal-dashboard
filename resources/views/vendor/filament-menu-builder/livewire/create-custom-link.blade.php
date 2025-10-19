<form wire:submit="save">
    <x-filament::section :heading="__('filament-menu-builder::menu-builder.custom_link')" :collapsible="true"
        :persist-collapsed="true" id="create-custom-link">

        <div class="space-y-4">
            {{ $this->form->getComponent('title') }}

            {{ $this->form->getComponent('url')
                ->helperText(__('filament-menu-builder::menu-builder.form.url_helper'))

             }}

            {{ $this->form->getComponent('target') }}
        </div>

        <x-slot:footerActions>
            <x-filament::button type="submit">
                {{ __('filament-menu-builder::menu-builder.actions.add.label') }}
            </x-filament::button>
        </x-slot:footerActions>
    </x-filament::section>
</form>