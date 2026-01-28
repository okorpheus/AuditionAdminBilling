<?php

use App\Models\Contact;
use Livewire\Component;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $sortBy = 'last_name';
    public string $sortDirection = 'asc';

    public function sort($column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy        = $column;
            $this->sortDirection = 'asc';
        }
    }

    #[On('contact-created')]
    #[On('contact-updated')]
    public function refresh(): void {}

    #[Computed]
    public function contacts()
    {
        return Contact::orderBy($this->sortBy, $this->sortDirection)->paginate(10);
    }
};
?>

<!--suppress RequiredAttributes -->
<div>
    <flux:table :paginate="$this->contacts">
        <flux:table.columns>
            <flux:table.column sortable :sorted="$sortBy === 'first_name'" :direction="$sortDirection"
                               wire:click="sort('first_name')">
                First Name
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'last_name'" :direction="$sortDirection"
                               wire:click="sort('last_name')">
                Last Name
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'email'" :direction="$sortDirection"
                               wire:click="sort('email')">
                Email
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'phone'" :direction="$sortDirection"
                               wire:click="sort('phone')">
                Phone
            </flux:table.column>
            <flux:table.column sortable :sorted="$sortBy === 'created_at'" :direction="$sortDirection"
                               wire:click="sort('created_at')">
                Created
            </flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach($this->contacts as $contact)
                <flux:table.row :key="$contact->id">
                    <flux:table.cell>{{ $contact->first_name }}</flux:table.cell>
                    <flux:table.cell>{{ $contact->last_name }}</flux:table.cell>
                    <flux:table.cell>{{ $contact->email }}</flux:table.cell>
                    <flux:table.cell>{{ $contact->phone }}</flux:table.cell>
                    <flux:table.cell>{{ $contact->created_at->local()->format('m/d/Y | g:i A') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:dropdown position="bottom" align="start">
                            <flux:button variant="ghost" size="sm" icon="ellipsis-horizontal"
                                         inset="top bottom"></flux:button>

                            <flux:navmenu>
                                <flux:menu.group heading="{{ $contact->first_name }} {{ $contact->last_name }}">
                                    <flux:menu.separator></flux:menu.separator>
                                    <flux:menu.item wire:click="$dispatch('edit-contact', { contactId: {{ $contact->id }} })" icon="pencil">Edit</flux:menu.item>
                                </flux:menu.group>
                            </flux:navmenu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>
</div>