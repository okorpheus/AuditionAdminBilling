<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        return [
            'status' => InvoiceStatus::DRAFT,
            'invoice_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays(30),
            'notes' => $this->faker->word(),
            'internal_notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'client_id' => Client::factory()->withContact(),
        ];
    }
}
