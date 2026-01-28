<?php

use App\Models\Client;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;


new class extends Component {
    use WithPagination;

    public string $sortBy = 'abbreviation';
    public string $sortDirection = 'desc';

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[Computed]
    public function clients()
    {
        return Client::orderBy($this->sortBy, $this->sortDirection)->paginate(10);
    }
};
?>

<!--suppress RequiredAttributes -->
<div>
    <flux:table :paginate="$this->clients">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection"
                               wire:click="sort('name')">
                Name
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'abbreviation'" :direction="$sortDirection"
                               wire:click="sort('abbreviation')">
                Abbreviation
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'audition_date'" :direction="$sortDirection"
                               wire:click="sort('audition_date')">
                Audition Date
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'status'" :direction="$sortDirection"
                               wire:click="sort('status')">
                Status
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                               wire:click="sort('created_at')">
                Created
            </flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach($this->clients as $client)
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
