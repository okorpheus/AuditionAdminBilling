<?php

use App\Models\Contact;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Flux\Flux;

new class extends Component {
    #[Validate('required|string|max:255')]
    public string $first_name = '';

    #[Validate('required|string|max:255')]
    public string $last_name = '';

    #[Validate('required|email|max:255|unique:contacts,email')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $phone = '';

    public function save(): void
    {
        $this->validate();

        Contact::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone ?: null,
        ]);

        $this->reset();
        Flux::modal('create-contact')->close();
        $this->dispatch('contact-created');
    }
};
?>

<div>
    <flux:modal.trigger name="create-contact">
        <flux:button icon="plus" variant="primary">
            New Contact
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="create-contact" class="md:w-96">
        <form wire:submit="save" class="space-y-6">
            <flux:heading size="lg">Create Contact</flux:heading>

            <flux:input label="First Name" wire:model="first_name" />
            <flux:input label="Last Name" wire:model="last_name" />
            <flux:input label="Email" wire:model="email" type="email" />
            <flux:input label="Phone" wire:model="phone" type="tel" />

            <div class="flex gap-2">
                <flux:spacer />
                <flux:button type="submit" variant="primary">Create</flux:button>
            </div>
        </form>
    </flux:modal>
</div>