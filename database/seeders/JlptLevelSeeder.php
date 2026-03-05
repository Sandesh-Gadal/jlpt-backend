<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class JlptLevelSeeder extends Seeder
{
    public function run(): void
    {
        $levels = [
            [
                'id'         => Str::uuid(),
                'code'       => 'N5',
                'name_en'    => 'JLPT N5 — Beginner',
                'name_ja'    => '日本語能力試験 N5',
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => Str::uuid(),
                'code'       => 'N4',
                'name_en'    => 'JLPT N4 — Elementary',
                'name_ja'    => '日本語能力試験 N4',
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => Str::uuid(),
                'code'       => 'N3',
                'name_en'    => 'JLPT N3 — Intermediate',
                'name_ja'    => '日本語能力試験 N3',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => Str::uuid(),
                'code'       => 'N2',
                'name_en'    => 'JLPT N2 — Upper Intermediate',
                'name_ja'    => '日本語能力試験 N2',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'         => Str::uuid(),
                'code'       => 'N1',
                'name_en'    => 'JLPT N1 — Advanced',
                'name_ja'    => '日本語能力試験 N1',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('jlpt_levels')->insert($levels);

        $this->command->info('✅ JLPT Levels seeded — N1 through N5 created.');
    }
}