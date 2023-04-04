<?php

namespace Database\Seeders;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userRequest = new UserRequest();
        $adminUser = [
            'first_name' => 'Simon',
            'last_name' => 'Siva',
            'is_admin' => 1,
            'address' => 'PO BOX 254-00100',
            'phone_number' => '0707898345',
            'is_marketing' => 0,
            'email' => 'admin@buckhill.co.uk',
            'password' => Hash::make('admin'),
        ];
        $validator = Validator::make($adminUser, $userRequest->rules());
        if ($validator->passes()) {
            User::create($adminUser);
        }

        $marketingUser = [
            'first_name' => 'Simon',
            'last_name' => 'Mulwa',
            'is_admin' => 0,
            'address' => 'PO BOX 254-00100',
            'phone_number' => '0707898345',
            'is_marketing' => 1,
            'email' => 'marketing@buckhill.co.uk',
            'password' => Hash::make('marketing'),
        ];
        $validator = Validator::make($marketingUser, $userRequest->rules());
        if ($validator->passes()) {
            User::create($marketingUser);
        }



    }
}
