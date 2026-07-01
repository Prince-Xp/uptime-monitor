<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClientSeeder extends Seeder
{
    
    public function run(): void
    {
         Client::factory(8)
        ->has(Website::factory()->count(3))
        ->create();
    }
}
