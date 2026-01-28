<?php

use App\Models\Client;
use Livewire\Component;

new class extends Component {
    #[Computed]
    public function clients()
    {
        return Client::all();
    }
};
?>

<div>
    <flux:table>
        <flux:table.columns>
            <flux:table.column>Name</flux:table.column>
            <flux:table.column>
                Abbreviation
            </flux:table.column>
            <flux:table.column>
                Audition Date
            </flux:table.column>
            <flux:table.column>
                Status
            </flux:table.column>
            <flux:table.column>
                Created
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach($this->clients() as $client)
                <flux:table.row :key="$client->id">
                    <flux:table.cell>{{ $client->name }}</flux:table.cell>
                    <flux:table.cell>{{ $client->abbreviation }}</flux:table.cell>
                    <flux:table.cell>{{ $client->audition_date->local()->format('m/d/Y') }}</flux:table.cell>
                    <flux:table.cell>
                        {{ $client->status }}
                    </flux:table.cell>
                    <flux:table.cell>{{ $client->created_at->local()->format('m/d/Y | g:i A') }}</flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>
