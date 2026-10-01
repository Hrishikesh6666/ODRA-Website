<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Territory;
use App\Models\User;

class TerritorySeeder extends Seeder
{
    public function run(): void
    {
        $territories = [
            ['code' => 'AL', 'label' => 'Alabama', 'rep' => 'JJ Rickis', 'states' => ['Alabama']],
            ['code' => 'AR', 'label' => 'Arkansas', 'rep' => 'JJ Rickis', 'states' => ['Arkansas']],
            ['code' => 'LA', 'label' => 'Louisiana', 'rep' => 'JJ Rickis', 'states' => ['Louisiana']],
            ['code' => 'MS', 'label' => 'Mississippi', 'rep' => 'JJ Rickis', 'states' => ['Mississippi']],
            ['code' => 'AZ', 'label' => 'Arizona', 'rep' => 'Troy Fisher', 'states' => ['Arizona']],
            ['code' => 'CA', 'label' => 'California', 'rep' => 'Fernando Oliveira', 'states' => ['California']],
            ['code' => 'CO', 'label' => 'Colorado', 'rep' => 'Sean Chenoweth', 'states' => ['Colorado']],
            ['code' => 'WY', 'label' => 'Wyoming', 'rep' => 'Sean Chenoweth', 'states' => ['Wyoming']],
            ['code' => 'GA', 'label' => 'Georgia', 'rep' => 'John Wells', 'states' => ['Georgia']],
            ['code' => 'IL', 'label' => 'Illinois', 'rep' => 'Paul Piotrowski', 'states' => ['Illinois']],
            ['code' => 'IN', 'label' => 'Indiana', 'rep' => 'Ryan Khosrofian', 'states' => ['Indiana']],
            ['code' => 'KY', 'label' => 'Kentucky', 'rep' => 'Ryan Khosrofian', 'states' => ['Kentucky']],
            ['code' => 'MO', 'label' => 'Missouri', 'rep' => null, 'states' => ['Missouri']],
            ['code' => 'PA', 'label' => 'Pennsylvania', 'rep' => 'Robert Yuska', 'states' => ['Pennsylvania']],
            ['code' => 'TN', 'label' => 'Tennessee', 'rep' => 'John Wells', 'states' => ['Tennessee']],
            ['code' => 'UT', 'label' => 'Utah', 'rep' => 'Kerry Briggs', 'states' => ['Utah']],
            ['code' => 'WI', 'label' => 'Wisconsin', 'rep' => 'Paul Piotrowski', 'states' => ['Wisconsin']],
            ['code' => 'NFL', 'label' => 'North Florida', 'rep' => 'Chris Norlin', 'cities' => ['Alachua, FL, 32616', 'Cantonment, FL', 'Daytona Beach, FL 32124', 'Orlando, FL, 32807', 'Pomona Park, FL 32181', 'Saint Cloud, FL 34771', 'Winter Garden, FL 34787', 'Sanford, FL, 321771']],
            ['code' => 'SWFL', 'label' => 'Southwest Florida', 'rep' => 'Kra Whitmore', 'cities' => ['Brandenton, FL 34203', 'Brooksville, FL 32604', 'Largo, FL 33771', 'Lehigh Acres, FL 34135', 'Naples, FL 34109', 'Ocala, FL, 34471', 'Palmetto, FL 34221', 'Sefner, FL 33584', 'Tampa, FL 33609', 'Tarpon Springs, FL 34689', 'Temple Terrace, FL 33617', 'Thonotosassa, FL 33592']],
            ['code' => 'SEFL', 'label' => 'Southeast Florida', 'rep' => 'Juan Oliver', 'cities' => ['Dade City, FL 33525', 'Deerfiled Beach, FL 33441', 'Fort Lauderdale, FL 33311', 'Hialeah Gardens, FL 33018', 'Medley, FL 33178', 'Miami, FL 33172', 'West Palm Beach, FL 33411']],
            ['code' => 'NTX', 'label' => 'North Texas', 'rep' => 'Shawn Herringer', 'cities' => ['Fort Worth, TX 76111', 'Mansfield, TX 76063', 'Dallas, TX 75247']],
            ['code' => 'STX', 'label' => 'South Texas', 'rep' => 'Carlos Silva', 'cities' => ['Converse, TX, 78109', 'Brookshire, TX 77423', 'San Antonio, TX 78233', 'La Marque, TX 77568', 'Lake Jackson, TX 77566']],
        ];

        foreach ($territories as $t) {
            $repId = null;
            if ($t['rep']) {
                $user = User::where('name', $t['rep'])->first();
                if ($user) {
                    $repId = $user->id;
                }
            }

            $territory = Territory::updateOrCreate(
                ['code' => $t['code']],
                [
                    'label' => $t['label'],
                    'assigned_rep_id' => $repId,
                    'states' => isset($t['states']) ? json_encode($t['states']) : null,
                    'cities' => isset($t['cities']) ? json_encode($t['cities']) : null,
                ]
            );
        }
    }
}
