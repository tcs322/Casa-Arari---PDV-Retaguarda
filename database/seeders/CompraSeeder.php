<?php

namespace Database\Seeders;

use App\Models\Compra;
use Database\Factories\CompraFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Compra::factory(100)->create();
    }
}
