<?php
namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Benefit;
use Illuminate\Database\Seeder;
use Dipokhalder\EnvEditor\EnvEditor;

class BenefitTableSeeder extends Seeder
{
    public function run()
    {
        $envService = new EnvEditor();
        if ($envService->getValue('DEMO')) {
            $benefits = [
                [
                    'title'       => json_encode([
                        'en' => 'benefit',
                        'ar' => 'فايدة'
                    ]),
                    'description' => json_encode([
                        'en' => 'description 1',
                        'ar' => 'وصف1'
                    ]),
                    'icon'        => 'image',
                    'status'      => Status::ACTIVE,
                    'sort'        => 1,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ],
                [
                    'title'       => json_encode([
                        'en' => 'benefit',
                        'ar' => 'فايدة'
                    ]),
                    'description' => json_encode([
                        'en' => 'Different secure payment methods',
                        'ar' => 'وصف1'
                    ]),
                    'icon'        => 'image',
                    'status'      => Status::ACTIVE,
                    'sort'        => 1,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ],
                [
                    'title'       => json_encode([
                        'en' => 'benefit',
                        'ar' => 'فايدة'
                    ]),
                    'description' => json_encode([
                        'en' => 'Fast and convenient door to door delivery',
                        'ar' => 'وصف1'
                    ]),
                    'icon'        => 'image',
                    'status'      => Status::ACTIVE,
                    'sort'        => 1,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ],
                [
                    'title'       => json_encode([
                        'en' => 'benefit',
                        'ar' => 'فايدة'
                    ]),
                    'description' => json_encode([
                        'en' => 'Comprehensive quality control and affordable prices',
                        'ar' => 'وصف1'
                    ]),
                    'icon'        => 'image',
                    'status'      => Status::ACTIVE,
                    'sort'        => 1,
                    'created_at'  => now(),
                    'updated_at'  => now()
                ],
            ];

            foreach ($benefits as $benefit) {
                $benefitObject = Benefit::create($benefit);
                $imagePath = public_path('/images/seeder/benefit/' . strtolower(str_replace(' ', '_', $benefit['title'])) . '.png');
                if (file_exists($imagePath)) {
                    $benefitObject->addMedia($imagePath)->preservingOriginal()->toMediaCollection('benefit');
                }
            }
        }
    }
}
