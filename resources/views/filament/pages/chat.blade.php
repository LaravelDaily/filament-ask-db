<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        @if(!$waitingForResponse)
            <x-filament::button
                    class="mt-6"
                    type="submit"
                    wire:target="submit">
                Send Message
            </x-filament::button>
        @else
            <div class="mt-6">
                <p>Waiting for AI response...</p>
            </div>
        @endif

        @if($reply)
            <div class="mt-6">
                <p class="font-bold">Your question:</p>
                <p>{{ $lastQuestion }}</p>
                <p class="mt-6 font-bold">AI Response:</p>
                <p>{{ $reply }}</p>
            </div>
        @endif
    </form>
</x-filament-panels::page>
