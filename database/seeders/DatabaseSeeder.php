<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            // ClienteSeeder::class,
            // ContatoSeeder::class,
            FornecedorSeeder::class,
            // -------------------
            // CompraSeeder::class,
            // VendaSeeder::class,
        ]);
    }
}
