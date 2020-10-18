<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transmittal = Status::create(['name' => 'TRANSMITTAL']);
        $transmittal->save();
    }
}
