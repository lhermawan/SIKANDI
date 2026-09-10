<?php

namespace Database\Factories;

use App\Models\Agent;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AgentFactory extends Factory
{
    protected $model = Agent::class;

    public function definition(): array
    {
        return [
            'agent_id' => 'AGT-' . strtoupper(Str::random(8)),
            'hostname' => $this->faker->domainWord() . '-server',
            'ip_address' => $this->faker->ipv4(),
            'os' => 'Linux',
            'os_version' => 'Ubuntu 22.04',
            'agent_version' => '1.0.0',
            'status' => 'pending',
            'last_seen_at' => now(),
            'registered_at' => now(),
        ];
    }
}
