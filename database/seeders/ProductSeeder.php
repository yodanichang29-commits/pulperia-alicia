<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name'=>'Coca-Cola 600ml','barcode'=>'750XYZ0001','category'=>'Bebidas','price'=>18,'stock'=>80],
            ['name'=>'Agua 600ml','barcode'=>'750XYZ0002','category'=>'Bebidas','price'=>12,'stock'=>100],
            ['name'=>'Galletas Oreo','barcode'=>'750XYZ0003','category'=>'Snacks','price'=>15,'stock'=>70],
            ['name'=>'Chicles','barcode'=>'750XYZ0004','category'=>'Snacks','price'=>5,'stock'=>200],
            ['name'=>'Pan dulce','barcode'=>'750XYZ0005','category'=>'Pan','price'=>10,'stock'=>60],
            ['name'=>'Leche 1L','barcode'=>'750XYZ0006','category'=>'Lácteos','price'=>25,'stock'=>50],
        ];

        foreach ($items as $it) {
            Product::updateOrCreate(['barcode' => $it['barcode']], $it);
        }
    }
}

