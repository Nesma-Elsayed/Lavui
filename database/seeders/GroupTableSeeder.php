<?php
namespace Database\Seeders;

use App\Enums\Status;
use App\Models\Group;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Database\Seeder;

class GroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $envService = new EnvEditor();
        $envService->getValue('DEMO');
        $groups = [
            [
                'name'       => [
                    'en' => 'group',
                    'ar' => 'مجموعه'
                ],
                'status'      => Status::ACTIVE,
                'created_at'  => now(),
                'updated_at'  => now()
            ],
            [
                'name'       => [
                    'en' => 'group',
                    'ar' => 'مجموعه'
                ],
                'status'      => Status::ACTIVE,
                'created_at'  => now(),
                'updated_at'  => now()
            ],
        ];

        foreach ($groups as $group) {
            $groupObject = Group::create($group);
            $imagePath = public_path('/images/seeder/benefit/' . strtolower(str_replace(' ', '_', $group['name']['en'])) . '.png');
            if (file_exists($imagePath)) {
                $groupObject->addMedia($imagePath)->preservingOriginal()->toMediaCollection('group');
            }
        }

    }
}
