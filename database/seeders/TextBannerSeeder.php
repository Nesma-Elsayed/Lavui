<?php

namespace Database\Seeders;

use App\Enums\Status;
use App\Models\TextBanner;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Database\Seeder;

class TextBannerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $envService = new EnvEditor();
        if ($envService->getValue('DEMO')) {
            $textBanners = [
                [
                    'name'       => [
                        'en' => 'TextBanner1',
                        'ar' => 'مجموعه'
                    ],
                    'status'      => Status::ACTIVE,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ],
                [
                    'name'       => [
                        'en' => 'TextBanner',
                        'ar' => 'مجموعه'
                    ],
                    'status'      => Status::ACTIVE,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ],
            ];

            foreach ($textBanners as $textBanner) {
                $groupObject = TextBanner::create($textBanner);
                $imagePath = public_path('/images/seeder/textBanner/' . strtolower(str_replace(' ', '_', $textBanner['name']['en'])) . '.png');
                if (file_exists($imagePath)) {
                    $groupObject->addMedia($imagePath)->preservingOriginal()->toMediaCollection('textBanner');
                }
            }
        }
    }
}
