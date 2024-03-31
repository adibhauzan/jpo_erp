<?php

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // $storeId = Store::pluck('id');
        // $convectionId = Store::pluck('id');

        // Str::

        // for ($i = 1; $i <= 5; $i++) {
        //     User::create([

        //         'name' => 'name ' . $i,
        //         'roles' => 'store',
        //         'phone_number' =>  . $i,
        //         'username',
        //         'password',
        //         'status',
        //         'store_id' => $storeId->random(),
        //         'category_type_id' => $categoryTypeIds->random(),
        //         'title' => 'Judul Artikel ' . $i,
        //         'image' => 'nama_file_gambar_' . $i . '.jpg',
        //         'description' => 'Deskripsi Artikel ' . $i,
        //         'master_id' => $masterIds->random(),
        //     ]);
        // }
    }
}
