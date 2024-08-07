<?php

namespace Database\Seeders;

use App\Enums\User\UserRoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Random\RandomException;

class UserSeeder extends Seeder
{
    const USER_FAKER_COUNT = 100;
    /**
     * Run the database seeds.
     * @throws RandomException
     */
    public function run(): void
    {
        User::query()->truncate();
        $dataUserSeeder = [];
        $passwordDefault = Hash::make('password');
        $now = now();
        for ($i = 0; $i < self::USER_FAKER_COUNT; $i++) {
            $dataUserSeeder[] = [
                'id' => Str::uuid(),
                'name' => fake()->name,
                'email' => now()->timestamp . '_' . fake()->email,
                'password' => $passwordDefault,
                'role' => random_int(min: UserRoleEnum::ADMIN->value, max: UserRoleEnum::USER->value),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        User::query()->insert(values:  $dataUserSeeder);
    }
}
