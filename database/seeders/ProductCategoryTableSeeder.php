<?php

namespace Database\Seeders;

use App\Enums\Status;
use Illuminate\Support\Str;
use App\Models\ProductCategory;
use Illuminate\Database\Seeder;
use Dipokhalder\EnvEditor\EnvEditor;

class ProductCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */


    public array $fashionCategories = [
        [
            'name'     => [
                'en' => 'Men',
                'ar' => 'رجال'
            ],
        ],
        [
            'name'     => [
                'en' => 'Women',
                'ar' => 'سيدات'
            ],
        ],
        [
            'name'     => [
                'en' => 'Juniors',
                'ar' => 'شباب'
            ],
        ]
    ];

    public function run(): void
    {
        $envService = new EnvEditor();
        if ($envService->getValue('DEMO')) {
            foreach ($this->fashionCategories as $fashionCategory) {
                $productCategory = ProductCategory::create([
                    'name'        => $fashionCategory['name'],
                    'slug'        => Str::slug($fashionCategory['name']['en'] . rand(1, 100)),
                    'description' => null,
                    'image'       => 'image',
                    'status'      => Status::ACTIVE,
                ]);
                if (file_exists(public_path('/images/seeder/product-category/' . env('DISPLAY') . '/' . strtolower(str_replace(' ', '_', $fashionCategory['name']['en'])) . '.png'))) {
                    $productCategory->addMedia(public_path('/images/seeder/product-category/' . env('DISPLAY') . '/' . strtolower(str_replace(' ', '_', $fashionCategory['name']['en'])) . '.png'))->preservingOriginal()->toMediaCollection('product-category');
                }

            }
        }
    }

    public function nested($arrays, $id = null): void
    {
        foreach ($arrays as $array) {
            $productCategory = ProductCategory::create([
                'parent_id'   => $id,
                'name'        => $array['name'],
                'slug'        => Str::slug($array['name'] . rand(101, 500)).rand(100, 200),
                'description' => null,
                'status'      => Status::ACTIVE,
            ]);
            if (file_exists(public_path('/images/seeder/product-category/' . env('DISPLAY') . '/' . strtolower(str_replace(' ', '_', $array['name'])) . '.png'))) {
                $productCategory->addMedia(public_path('/images/seeder/product-category/' . env('DISPLAY') . '/' . strtolower(str_replace(' ', '_', $array['name'])) . '.png'))->preservingOriginal()->toMediaCollection('product-category');
            }
            if (isset($array['children']) && count($array) > 0) {
                $this->nested($array['children'], $productCategory->id);
            }
        }
    }
}
