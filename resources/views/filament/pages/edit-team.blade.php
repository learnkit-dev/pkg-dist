<x-filament::page>
    <x-filament::form wire:submit.prevent="submit">
        {{ $this->form }}

        <x-filament::form.actions
            :actions="[$this->submitAction()]"
        />
    </x-filament::form>
</x-filament::page>
