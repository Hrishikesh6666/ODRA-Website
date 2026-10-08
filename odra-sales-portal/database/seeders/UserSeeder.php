<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Technical Administrators (role_id = 1) - No financial visibility
        $admins = [
            [
                'name' => 'Dev Raj',
                'email' => 'cc@odrasweeper.com',
                'company_id' => 'ADMIN-DEV',
            ],
            [
                'name' => 'Hrishikesh S',
                'email' => 'hrishikesh.s@odrasweeper.com',
                'company_id' => 'ADMIN-HRISHI',
            ],
            [
                'name' => 'HQ Administrator',
                'email' => 'it.support@challengermfg.net',
                'company_id' => 'ADMIN001',
            ],
        ];

        foreach ($admins as $admin) {
            User::updateOrCreate(
                ['email' => strtolower($admin['email'])],
                [
                    'name' => $admin['name'],
                    'role_id' => 1,
                    'company_id' => $admin['company_id'],
                    'password' => Hash::make('OdraAdmin2026!'),
                    'territory' => 'Technical Operations',
                    'is_active' => true,
                ]
            );
        }

        // 2. Managers (role_id = 3) - Full financial & commercial visibility
        $managers = [
            ['name' => 'Alex Byczynski', 'email' => 'alex.byczynski@odrasweeper.com', 'company_id' => 'MGR-ALEX', 'territory' => 'Executive Management'],
            ['name' => 'Mirek Byczynski', 'email' => 'mirek.byczynski@odrasweeper.com', 'company_id' => 'MGR-MIREK', 'territory' => 'Executive Management'],
            ['name' => 'Doug Seto', 'email' => 'doug.seto@odrasweeper.com', 'company_id' => 'MGR-DOUG', 'territory' => 'Sales Director'],
            ['name' => 'Agnieszka Krochmal-Wegrzyn', 'email' => 'agnieszka.krochmal@odrasweeper.com', 'company_id' => 'MGR-AGNI', 'territory' => 'Finance & Operations'],
            ['name' => 'Jason Coover', 'email' => 'jason.coover@odrasweeper.com', 'company_id' => 'MGR-JASON', 'territory' => 'Regional Management'],
            ['name' => 'Aaron Harr', 'email' => 'aaron.harr@odrasweeper.com', 'company_id' => 'MGR-AARON', 'territory' => 'Sales Manager'],
        ];

        foreach ($managers as $mgr) {
            User::updateOrCreate(
                ['email' => strtolower($mgr['email'])],
                [
                    'name' => $mgr['name'],
                    'role_id' => 3,
                    'company_id' => $mgr['company_id'],
                    'password' => Hash::make('OdraManager2026!'),
                    'territory' => $mgr['territory'],
                    'is_active' => true,
                ]
            );
        }

        // 3. Sales Representatives (role_id = 2) - Field quotations with isolated access
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
        ];

        foreach ($salesReps as $rep) {
            $email = strtolower(str_replace(' ', '.', $rep['name'])) . '@odrasweeper.com';
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $rep['name'],
                    'rep_code' => $rep['rep_code'],
                    'company_id' => 'REP-' . $rep['rep_code'],
                    'role_id' => 2,
                    'password' => Hash::make('OdraSales2026!'),
                    'is_active' => true,
                ]
            );
        }
    }
}
