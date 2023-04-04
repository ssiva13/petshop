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
            'phone_number' => '254717898345',
            'is_marketing' => 0,
            'email' => 'admin@buckhill.co.uk',
            'password' => 'adminadmin',
            'password_confirmation' => 'adminadmin',
        ];
        $validator = Validator::make($adminUser, $userRequest->rules());
        if ($validator->passes()) {
            unset($adminUser['password_confirmation']);
            $adminUser['password'] = Hash::make('admin');
            User::create($adminUser);
        }

        $marketingUser = [
            'first_name' => 'Simon',
            'last_name' => 'Mulwa',
            'is_admin' => 0,
            'address' => 'PO BOX 254-00100',
            'phone_number' => '254707898345',
            'is_marketing' => 1,
            'email' => 'marketing@buckhill.co.uk',
            'password' => 'marketingmarketing',
            'password_confirmation' => 'marketingmarketing',
        ];
        $validator = Validator::make($marketingUser, $userRequest->rules());
        if ($validator->passes()) {
            unset($marketingUser['password_confirmation']);
            $marketingUser['password'] = Hash::make('marketing');
            User::create($marketingUser);
        }

        User::factory(5)->create();

    }
}
