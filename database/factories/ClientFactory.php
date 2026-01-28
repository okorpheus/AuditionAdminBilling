<?php

namespace Database\Factories;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'abbreviation' => $this->faker->word(),
            'audition_date' => $this->faker->dateTimeBetween('+5 days', '+1 year'),
            'status' => ClientStatus::ACTIVE,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function withContact(?Contact $contact = null): static
    {
        return $this->afterCreating(function (Client $client) use ($contact) {
            $client->contacts()->attach(
                $contact ?? Contact::factory()->create(),
                ['is_primary' => true]
            );
        });
    }
}
