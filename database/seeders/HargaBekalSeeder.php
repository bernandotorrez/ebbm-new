<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\HargaBekal;
use App\Models\Kota;
use App\Models\Bekal;

class HargaBekalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing kota and bekal records to use in the seeder
        $kotas = Kota::all();
        $bekals = Bekal::all();

        if($kotas->isNotEmpty() && $bekals->isNotEmpty()) {
            HargaBekal::create([
                'kota_id' => $kotas->first()->kota_id,
                'bekal_id' => $bekals->first()->bekal_id,
                'harga' => 150000.00
            ]);
            
            HargaBekal::create([
                'kota_id' => $kotas->last()->kota_id,
                'bekal_id' => $bekals->last()->bekal_id,
                'harga' => 200000.00
            ]);
        }
    }
}
