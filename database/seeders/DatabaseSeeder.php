<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\QandA_Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([QandA_Seeder::class]);
    }
}
