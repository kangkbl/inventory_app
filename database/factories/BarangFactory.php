<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Barang;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Barang>
 */
class BarangFactory extends Factory
{
    protected $model = Barang::class;

    public function definition(): array
    {
        $faker = fake('id_ID'); // opsional: rasa Indonesia

        $kategori = [
            'Encoder', 'Multiplexer', 'Transmitter', 'Router', 'Switch',
            'Kabel', 'Konektor', 'UPS', 'Rack', 'RTU', 'Kamera', 'Audio'
        ];

        $lokasi = [
            'Ruang Transmisi Joglo',
            'Ruang Transmisi Serang',
            'Ruang Transmisi Cilegon',
            'Master Control',
            'Studio 1',
            'Studio 2',
            'Gudang Peralatan',
        ];

        return [
            'nama_barang'       => $faker->words(2, true), // contoh: "Router Gigabit"
            'merk'              => $faker->randomElement([
                'MikroTik','Ubiquiti','Cisco','TP-Link','Panasonic',
                'Sony','Sharp','Dahua','Hikvision','Rohde & Schwarz'
            ]),
            // Banyak kombinasi -> aman untuk UNIQUE
            'kode_barang_bmn'   => strtoupper($faker->unique()->bothify('BMN-####/##/??')),
            'kategori'          => $faker->randomElement($kategori),
            'lokasi'            => $faker->randomElement($lokasi),
            'kondisi'           => $faker->randomElement(['baik','rusak','perbaikan']),
            'jumlah'            => $faker->numberBetween(1, 20),
            // Kolom YEAR -> isi angka 4 digit
            'tahun_pengadaan'   => $faker->numberBetween(2010, (int) date('Y')),
            'keterangan'        => $faker->optional(0.5)->sentence(),

            'created_at'        => now(),
            'updated_at'        => now(),
        ];
    }
}
