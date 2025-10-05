<x-filament::page>
	<x-filament-panels::form :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()" wire:submit="save">
		{{ $this->form }}
        @if (session()->has('success'))
            <p class="mt-4 text-green-500">{{ session('success') }}</p>
        @endif
        <x-filament::button size="sm" wire:click='save' class="">
            Save
        </x-filament::button>
	</x-filament-panels::form>    

</x-filament::page>