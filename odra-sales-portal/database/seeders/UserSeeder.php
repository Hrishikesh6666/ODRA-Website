<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $salesReps = [
            ['name' => 'Carlos Silva', 'rep_code' => 'CS'],
            ['name' => 'Chris Norlin', 'rep_code' => 'CN'],
            ['name' => 'Fernando Oliveira', 'rep_code' => 'FO'],
            ['name' => 'JJ Rickis', 'rep_code' => 'JR'],
            ['name' => 'John Wells', 'rep_code' => 'JW'],
            ['name' => 'Juan Oliver', 'rep_code' => 'JO'],
            ['name' => 'Kerry Briggs', 'rep_code' => 'KB'],
            ['name' => 'Kra Whitmore', 'rep_code' => 'KW'],
            ['name' => 'Paul Piotrowski', 'rep_code' => 'PP'],
            ['name' => 'Robert Yuska', 'rep_code' => 'RY'],
            ['name' => 'Ryan Khosrofian', 'rep_code' => 'RK'],
            ['name' => 'Sean Chenoweth', 'rep_code' => 'SC'],
            ['name' => 'Shawn Herringer', 'rep_code' => 'SH'],
            ['name' => 'Troy Fisher', 'rep_code' => 'TF'],
            ['name' => 'Jason Coover', 'rep_code' => 'JC'],
            ['name' => 'Alex Byczynski', 'rep_code' => 'AB'],
        ];

        User::updateOrCreate(
            ['email' => 'it.support@challengermfg.net'],
            [
                'name' => 'HQ Administrator',
                'role_id' => 1,
                'company_id' => 'ADMIN001',
                'password' => Hash::make('OdraAdmin2026!'),
                'territory' => 'Headquarters',
                'is_active' => true,
            ]
        );

        foreach ($salesReps as $rep) {
            $email = strtolower(str_replace(' ', '.', $rep['name'])) . '@odrasweeper.com';
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $rep['name'],
                    'rep_code' => $rep['rep_code'],
                    'role_id' => 2,
                    'password' => Hash::make('OdraSales2026!'),
                    'is_active' => true,
                ]
            );
        }
    }
}
