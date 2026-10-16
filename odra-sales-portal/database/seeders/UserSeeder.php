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

        // 3. Sales Representatives (role_id = 2) - Field quotations matching Master Sales Territory Schedule
        $salesReps = [
            [
                'name' => 'Bud Earp',
                'rep_code' => 'BE',
                'state' => 'Alabama',
                'city' => 'Birmingham AL 35234',
                'assigned_states' => ['Alabama', 'Arkansas', 'Louisiana', 'Mississippi']
            ],
            [
                'name' => 'Carlos Silva',
                'rep_code' => 'CS',
                'state' => 'Texas',
                'city' => 'San Antonio TX 78233',
                'assigned_states' => ['Texas']
            ],
            [
                'name' => 'Chris Norlin',
                'rep_code' => 'CN',
                'state' => 'Florida',
                'city' => 'Orlando FL 32807',
                'assigned_states' => ['Florida']
            ],
            [
                'name' => 'Fernando Oliveira',
                'rep_code' => 'FO',
                'state' => 'California',
                'city' => 'Sacramento CA 91750',
                'assigned_states' => ['California']
            ],
            [
                'name' => 'Jason Coover',
                'rep_code' => 'JC',
                'state' => 'Kansas',
                'city' => 'Shawnee KS 66217',
                'assigned_states' => ['Hawaii', 'Kansas', 'New Mexico', 'Wyoming']
            ],
            [
                'name' => 'John Wells',
                'rep_code' => 'JW',
                'state' => 'Georgia',
                'city' => 'Cartersville GA 30121',
                'assigned_states' => ['Georgia', 'North Carolina', 'South Carolina', 'Tennessee']
            ],
            [
                'name' => 'Juan Oliver',
                'rep_code' => 'JO',
                'state' => 'Florida',
                'city' => 'Miami FL 33172',
                'assigned_states' => ['Florida']
            ],
            [
                'name' => 'Kra Whitmore',
                'rep_code' => 'KW',
                'state' => 'Florida',
                'city' => 'Tampa FL 33609',
                'assigned_states' => ['Florida']
            ],
            [
                'name' => 'Marcin Strojny',
                'rep_code' => 'MS',
                'state' => 'International',
                'city' => 'International',
                'assigned_states' => ['International']
            ],
            [
                'name' => 'Paul Piotrowski',
                'rep_code' => 'PP',
                'state' => 'Illinois',
                'city' => 'Chicago IL 60612',
                'assigned_states' => ['Illinois', 'Wisconsin']
            ],
            [
                'name' => 'Robert Yuska',
                'rep_code' => 'RY',
                'state' => 'Pennsylvania',
                'city' => 'Harrisburg PA 17110',
                'assigned_states' => ['Pennsylvania']
            ],
            [
                'name' => 'Ryan Khosrofian',
                'rep_code' => 'RK',
                'state' => 'Indiana',
                'city' => 'Indianapolis IN 46214',
                'assigned_states' => ['Indiana', 'Kentucky']
            ],
            [
                'name' => 'Sales Management',
                'rep_code' => 'SM',
                'state' => 'Minnesota',
                'city' => 'North Saint Paul MN 55109',
                'assigned_states' => [
                    'Alaska', 'Alberta', 'British Columbia', 'Connecticut', 'Delaware',
                    'District of Columbia', 'Idaho', 'Iowa', 'Maine', 'Manitoba',
                    'Maryland', 'Massachusetts', 'Michigan', 'Minnesota', 'Missouri',
                    'Montana', 'Nebraska', 'Nevada', 'New Hampshire', 'New Jersey',
                    'New York', 'Newfoundland and Labrador', 'North Dakota', 'Ohio',
                    'Oklahoma', 'Ontario', 'Oregon', 'Quebec', 'Rhode Island',
                    'Saskatchewan', 'South Dakota', 'Utah', 'Vermont', 'Virginia',
                    'Washington', 'West Virginia', 'Prince Edward', 'New Brunswick', 'Nova Scotia'
                ]
            ],
            [
                'name' => 'Sean Chenoweth',
                'rep_code' => 'SC',
                'state' => 'Colorado',
                'city' => 'Colorado Springs CO 80906',
                'assigned_states' => ['Colorado']
            ],
            [
                'name' => 'Shawn Herringer',
                'rep_code' => 'SH',
                'state' => 'Texas',
                'city' => 'Dallas TX 75247',
                'assigned_states' => ['Texas']
            ],
            [
                'name' => 'Ted Bartolo',
                'rep_code' => 'TB',
                'state' => 'Georgia',
                'city' => 'Cartersville GA 30121',
                'assigned_states' => ['Georgia', 'North Carolina', 'South Carolina']
            ],
            [
                'name' => 'Troy Fisher',
                'rep_code' => 'TF',
                'state' => 'Arizona',
                'city' => 'Phoenix AZ 85009',
                'assigned_states' => ['Arizona']
            ],
        ];

        $activeSalesEmails = [];
        foreach ($salesReps as $rep) {
            $email = strtolower(str_replace(' ', '.', $rep['name'])) . '@odrasweeper.com';
            $activeSalesEmails[] = $email;

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
                    'assigned_states' => $rep['assigned_states'],
                    'territory' => implode(', ', array_slice($rep['assigned_states'], 0, 5)) . (count($rep['assigned_states']) > 5 ? '... (' . count($rep['assigned_states']) . ' states)' : ''),
                    'is_active' => true,
                ]
            );
        }

        // Purge former sales reps no longer in master sales list
        User::where('role_id', 2)
            ->whereNotIn('email', $activeSalesEmails)
            ->get()
            ->each(function ($oldRep) {
                \App\Models\Territory::where('assigned_rep_id', $oldRep->id)->update(['assigned_rep_id' => null]);
                \App\Models\AuditLog::where('user_id', $oldRep->id)->update(['user_id' => null]);
                $oldRep->delete();
            });
    }
}
