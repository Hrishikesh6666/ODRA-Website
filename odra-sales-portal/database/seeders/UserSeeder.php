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
            ['name' => 'Carlos Silva', 'rep_code' => 'CS', 'state' => 'Texas', 'city' => 'San Antonio TX 78233'],
            ['name' => 'Chris Norlin', 'rep_code' => 'CN', 'state' => 'Florida', 'city' => 'Orlando FL 32807'],
            ['name' => 'Fernando Oliveira', 'rep_code' => 'FO', 'state' => 'California', 'city' => 'Sacramento CA 91750'],
            ['name' => 'JJ Rickis', 'rep_code' => 'JR', 'state' => 'Alabama', 'city' => 'Birmingham AL 35234'],
            ['name' => 'John Wells', 'rep_code' => 'JW', 'state' => 'Georgia', 'city' => 'Cartersville GA 30121'],
            ['name' => 'Juan Oliver', 'rep_code' => 'JO', 'state' => 'Florida', 'city' => 'Miami FL 33172'],
            ['name' => 'Kerry Briggs', 'rep_code' => 'KB', 'state' => 'Utah', 'city' => 'Valley City UT 84119'],
            ['name' => 'Kra Whitmore', 'rep_code' => 'KW', 'state' => 'Florida', 'city' => 'Tampa FL 33609'],
            ['name' => 'Paul Piotrowski', 'rep_code' => 'PP', 'state' => 'Illinois', 'city' => 'Chicago IL 60612'],
            ['name' => 'Robert Yuska', 'rep_code' => 'RY', 'state' => 'Pennsylvania', 'city' => 'Harrisburg PA 17110'],
            ['name' => 'Ryan Khosrofian', 'rep_code' => 'RK', 'state' => 'Indiana', 'city' => 'Indianapolis IN 46214'],
            ['name' => 'Sean Chenoweth', 'rep_code' => 'SC', 'state' => 'Colorado', 'city' => 'Colorado Springs CO 80906'],
            ['name' => 'Shawn Herringer', 'rep_code' => 'SH', 'state' => 'Texas', 'city' => 'Dallas TX 75247'],
            ['name' => 'Troy Fisher', 'rep_code' => 'TF', 'state' => 'Arizona', 'city' => 'Phoenix AZ 85009'],
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
                    'state' => $rep['state'],
                    'city' => $rep['city'],
                    'territory' => $rep['city'] . ', ' . $rep['state'],
                    'is_active' => true,
                ]
            );
        }
    }
}
