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
            ['code' => "AL", 'label' => "Alabama", 'rep' => "Bud Earp", 'states' => ["Alabama"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "AK", 'label' => "Alaska", 'rep' => "Sales Management", 'states' => ["Alaska"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "AB", 'label' => "Alberta", 'rep' => "Sales Management", 'states' => ["Alberta"], 'channel' => "dealer", 'dealer_name' => "Superior North", 'region' => "North", 'country' => "Canada"],
            ['code' => "AZ", 'label' => "Arizona", 'rep' => "Troy Fisher", 'states' => ["Arizona"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "AR", 'label' => "Arkansas", 'rep' => "Bud Earp", 'states' => ["Arkansas"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "BC", 'label' => "British Columbia", 'rep' => "Sales Management", 'states' => ["British Columbia"], 'channel' => "dealer", 'dealer_name' => "Superior North", 'region' => "North", 'country' => "Canada"],
            ['code' => "CA", 'label' => "California", 'rep' => "Fernando Oliveira", 'states' => ["California"], 'channel' => "direct & dealer", 'dealer_name' => "Plumbers Depot", 'region' => "North", 'country' => "USA"],
            ['code' => "CO", 'label' => "Colorado", 'rep' => "Sean Chenoweth", 'states' => ["Colorado"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "CT", 'label' => "Connecticut", 'rep' => "Sales Management", 'states' => ["Connecticut"], 'channel' => "dealer", 'dealer_name' => "Chadwick", 'region' => "North", 'country' => "USA"],
            ['code' => "DE", 'label' => "Delaware", 'rep' => "Sales Management", 'states' => ["Delaware"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "DC", 'label' => "District of Columbia", 'rep' => "Sales Management", 'states' => ["District of Columbia"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "GA CN", 'label' => "Georgia-Contractor", 'rep' => "John Wells", 'states' => ["Georgia"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "GA ML", 'label' => "Georgia-Municipal", 'rep' => "Ted Bartolo", 'states' => ["Georgia"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "HI", 'label' => "Hawaii", 'rep' => "Jason Coover", 'states' => ["Hawaii"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "ID", 'label' => "Idaho", 'rep' => "Sales Management", 'states' => ["Idaho"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "IL", 'label' => "Illinois", 'rep' => "Paul Piotrowski", 'states' => ["Illinois"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "IN", 'label' => "Indiana", 'rep' => "Ryan Khosrofian", 'states' => ["Indiana"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "INTL", 'label' => "International", 'rep' => "Marcin Strojny", 'states' => ["International"], 'channel' => "direct", 'dealer_name' => "", 'region' => "International", 'country' => "International"],
            ['code' => "IA", 'label' => "Iowa", 'rep' => "Sales Management", 'states' => ["Iowa"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "KS", 'label' => "Kansas", 'rep' => "Jason Coover", 'states' => ["Kansas"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "KY", 'label' => "Kentucky", 'rep' => "Ryan Khosrofian", 'states' => ["Kentucky"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "LA", 'label' => "Louisiana", 'rep' => "Bud Earp", 'states' => ["Louisiana"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "ME", 'label' => "Maine", 'rep' => "Sales Management", 'states' => ["Maine"], 'channel' => "dealer", 'dealer_name' => "Chadwick", 'region' => "North", 'country' => "USA"],
            ['code' => "MB", 'label' => "Manitoba", 'rep' => "Sales Management", 'states' => ["Manitoba"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "Canada"],
            ['code' => "MD", 'label' => "Maryland", 'rep' => "Sales Management", 'states' => ["Maryland"], 'channel' => "dealer", 'dealer_name' => "ISG", 'region' => "North", 'country' => "USA"],
            ['code' => "MA", 'label' => "Massachusetts", 'rep' => "Sales Management", 'states' => ["Massachusetts"], 'channel' => "dealer", 'dealer_name' => "Chadwick", 'region' => "North", 'country' => "USA"],
            ['code' => "MI", 'label' => "Michigan", 'rep' => "Sales Management", 'states' => ["Michigan"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "MN", 'label' => "Minnesota", 'rep' => "Sales Management", 'states' => ["Minnesota"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "MS", 'label' => "Mississippi", 'rep' => "Bud Earp", 'states' => ["Mississippi"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "MO", 'label' => "Missouri", 'rep' => "Sales Management", 'states' => ["Missouri"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "MT", 'label' => "Montana", 'rep' => "Sales Management", 'states' => ["Montana"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "NE", 'label' => "Nebraska", 'rep' => "Sales Management", 'states' => ["Nebraska"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "NV", 'label' => "Nevada", 'rep' => "Sales Management", 'states' => ["Nevada"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "NH", 'label' => "New Hampshire", 'rep' => "Sales Management", 'states' => ["New Hampshire"], 'channel' => "dealer", 'dealer_name' => "Chadwick", 'region' => "North", 'country' => "USA"],
            ['code' => "NJ", 'label' => "New Jersey", 'rep' => "Sales Management", 'states' => ["New Jersey"], 'channel' => "direct & dealer", 'dealer_name' => "Sanitation Equipment", 'region' => "North", 'country' => "USA"],
            ['code' => "NM", 'label' => "New Mexico", 'rep' => "Jason Coover", 'states' => ["New Mexico"], 'channel' => "dealer", 'dealer_name' => "411 Equipment", 'region' => "South", 'country' => "USA"],
            ['code' => "NY", 'label' => "New York", 'rep' => "Sales Management", 'states' => ["New York"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "NL", 'label' => "Newfoundland and Labrador", 'rep' => "Sales Management", 'states' => ["Newfoundland and Labrador"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "Canada"],
            ['code' => "NC CN", 'label' => "North Carolina-Contractor", 'rep' => "John Wells", 'states' => ["North Carolina"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "NC ML", 'label' => "North Carolina-Municipal", 'rep' => "Ted Bartolo", 'states' => ["North Carolina"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "ND", 'label' => "North Dakota", 'rep' => "Sales Management", 'states' => ["North Dakota"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "N FL", 'label' => "North Florida", 'rep' => "Chris Norlin", 'states' => ["Florida"], 'cities' => ["Alachua, FL, 32616", "Cantonment, FL", "Daytona Beach, FL 32124", "Orlando, FL, 32807", "Pomona Park, FL 32181", "Saint Cloud, FL 34771", "Winter Garden, FL 34787", "Sanford, FL, 321771"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "OH", 'label' => "Ohio", 'rep' => "Sales Management", 'states' => ["Ohio"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "OK", 'label' => "Oklahoma", 'rep' => "Sales Management", 'states' => ["Oklahoma"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "ON", 'label' => "Ontario", 'rep' => "Sales Management", 'states' => ["Ontario"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "Canada"],
            ['code' => "OR", 'label' => "Oregon", 'rep' => "Sales Management", 'states' => ["Oregon"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "PA", 'label' => "Pennsylvania", 'rep' => "Robert Yuska", 'states' => ["Pennsylvania"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "QC", 'label' => "Quebec", 'rep' => "Sales Management", 'states' => ["Quebec"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "Canada"],
            ['code' => "RI", 'label' => "Rhode Island", 'rep' => "Sales Management", 'states' => ["Rhode Island"], 'channel' => "dealer", 'dealer_name' => "Chadwick", 'region' => "North", 'country' => "USA"],
            ['code' => "SK", 'label' => "Saskatchewan", 'rep' => "Sales Management", 'states' => ["Saskatchewan"], 'channel' => "dealer", 'dealer_name' => "Superior North", 'region' => "North", 'country' => "Canada"],
            ['code' => "SC CN", 'label' => "South Carolina-Contractor", 'rep' => "John Wells", 'states' => ["South Carolina"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "SC ML", 'label' => "South Carolina-Municipal", 'rep' => "Ted Bartolo", 'states' => ["South Carolina"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "SD", 'label' => "South Dakota", 'rep' => "Sales Management", 'states' => ["South Dakota"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "SE FL", 'label' => "South East Florida", 'rep' => "Juan Oliver", 'states' => ["Florida"], 'cities' => ["Dade City, FL 33525", "Deerfiled Beach, FL 33441", "Fort Lauderdale, FL 33311", "Hialeah Gardens, FL 33018", "Medley, FL 33178", "Miami, FL 33172", "West Palm Beach, FL 33411"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "SW FL", 'label' => "South West Florida", 'rep' => "Kra Whitmore", 'states' => ["Florida"], 'cities' => ["Brandenton, FL 34203", "Brooksville, FL 32604", "Largo, FL 33771", "Lehigh Acres, FL 34135", "Naples, FL 34109", "Ocala, FL, 34471", "Palmetto, FL 34221", "Sefner, FL 33584", "Tampa, FL 33609", "Tarpon Springs, FL 34689", "Temple Terrace, FL 33617", "Thonotosassa, FL 33592"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "TN", 'label' => "Tennessee", 'rep' => "John Wells", 'states' => ["Tennessee"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "N TX", 'label' => "North Texas", 'rep' => "Shawn Herringer", 'states' => ["Texas"], 'cities' => ["Fort Worth, TX 76111", "Mansfield, TX 76063", "Dallas, TX 75247"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "S TX", 'label' => "South Texas", 'rep' => "Carlos Silva", 'states' => ["Texas"], 'cities' => ["Converse, TX, 78109", "Brookshire, TX 77423", "San Antonio, TX 78233", "La Marque, TX 77568", "Lake Jackson, TX 77566"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "UT", 'label' => "Utah", 'rep' => "Sales Management", 'states' => ["Utah"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "VT", 'label' => "Vermont", 'rep' => "Sales Management", 'states' => ["Vermont"], 'channel' => "dealer", 'dealer_name' => "Chadwick", 'region' => "North", 'country' => "USA"],
            ['code' => "VA", 'label' => "Virginia", 'rep' => "Sales Management", 'states' => ["Virginia"], 'channel' => "dealer", 'dealer_name' => "ISG", 'region' => "North", 'country' => "USA"],
            ['code' => "WA", 'label' => "Washington", 'rep' => "Sales Management", 'states' => ["Washington"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "WV", 'label' => "West Virginia", 'rep' => "Sales Management", 'states' => ["West Virginia"], 'channel' => "dealer", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "WI", 'label' => "Wisconsin", 'rep' => "Paul Piotrowski", 'states' => ["Wisconsin"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "USA"],
            ['code' => "WY", 'label' => "Wyoming", 'rep' => "Jason Coover", 'states' => ["Wyoming"], 'channel' => "direct", 'dealer_name' => "", 'region' => "South", 'country' => "USA"],
            ['code' => "PE", 'label' => "Prince Edward", 'rep' => "Sales Management", 'states' => ["Prince Edward"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "Canada"],
            ['code' => "NB", 'label' => "New Brunswick", 'rep' => "Sales Management", 'states' => ["New Brunswick"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "Canada"],
            ['code' => "NS", 'label' => "Nova Scotia", 'rep' => "Sales Management", 'states' => ["Nova Scotia"], 'channel' => "direct", 'dealer_name' => "", 'region' => "North", 'country' => "Canada"],
        ];

        foreach ($territories as $t) {
            $repId = null;
            if ($t['rep']) {
                $user = User::where('name', $t['rep'])->first();
                if ($user) {
                    $repId = $user->id;
                }
            }

            Territory::updateOrCreate(
                ['code' => $t['code']],
                [
                    'label' => $t['label'],
                    'assigned_rep_id' => $repId,
                    'states' => isset($t['states']) ? $t['states'] : null,
                    'cities' => isset($t['cities']) ? $t['cities'] : null,
                    'channel' => $t['channel'] ?? null,
                    'dealer_name' => $t['dealer_name'] ?? null,
                    'region' => $t['region'] ?? null,
                    'country' => $t['country'] ?? null,
                ]
            );
        }

        // Purge old territory codes no longer in the master file
        $validCodes = array_column($territories, 'code');
        Territory::whereNotIn('code', $validCodes)->delete();
    }
}
