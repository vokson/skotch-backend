<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ApiUser;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $guest = new ApiUser();
        $guest->name ='guest';
        $guest->surname ='guest';
        $guest->email = 'guest@mail.com';
        $guest->password = hash('sha256', '1234');
        $guest->role = 'guest';
        $guest->active = 1;
        $guest->access_token = '';
        $guest->save();

        $admin = new ApiUser();
        $admin->name ='admin';
        $admin->surname ='admin';
        $admin->email = 'admin@mail.com';
        $admin->password = hash('sha256', '1234');
        $admin->role = 'admin';
        $admin->active = 1;
        $admin->access_token = '';
        $admin->save();
    }
}
