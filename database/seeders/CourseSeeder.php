<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET session_replication_role = replica;');
        DB::table('content_access_rules')->truncate();
        DB::table('lessons')->truncate();
        DB::table('courses')->truncate();
        DB::statement('SET session_replication_role = DEFAULT;');

        $n5Id = DB::table('jlpt_levels')->where('code', 'N5')->value('id');
        $n4Id = DB::table('jlpt_levels')->where('code', 'N4')->value('id');

        $courses = [

            // ── N5 COURSES — Free plan can access ─────────
            [
                'id'                => Str::uuid(),
                'jlpt_level_id'     => $n5Id,
                'title'             => 'N5 Essential Vocabulary',
                'slug'              => 'n5-essential-vocabulary',
                'description'       => 'Master the 800 most important N5 vocabulary words.',
                'category'          => 'vocabulary',
                'estimated_minutes' => 180,
                'sort_order'        => 1,
                'is_published'      => true,
                'thumbnail_url'     => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'id'                => Str::uuid(),
                'jlpt_level_id'     => $n5Id,
                'title'             => 'N5 Basic Grammar',
                'slug'              => 'n5-basic-grammar',
                'description'       => 'Learn fundamental Japanese grammar patterns.',
                'category'          => 'grammar',
                'estimated_minutes' => 240,
                'sort_order'        => 2,
                'is_published'      => true,
                'thumbnail_url'     => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'id'                => Str::uuid(),
                'jlpt_level_id'     => $n5Id,
                'title'             => 'N5 Kanji — First 100',
                'slug'              => 'n5-kanji-first-100',
                'description'       => 'Learn the 100 essential kanji required for JLPT N5.',
                'category'          => 'kanji',
                'estimated_minutes' => 200,
                'sort_order'        => 3,
                'is_published'      => true,
                'thumbnail_url'     => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],

            // ── N4 COURSES — Individual plan required ──────
            [
                'id'                => Str::uuid(),
                'jlpt_level_id'     => $n4Id,
                'title'             => 'N4 Vocabulary',
                'slug'              => 'n4-vocabulary',
                'description'       => 'Expand your vocabulary to the N4 level with 1500 essential words.',
                'category'          => 'vocabulary',
                'estimated_minutes' => 240,
                'sort_order'        => 4,
                'is_published'      => true,
                'thumbnail_url'     => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
            [
                'id'                => Str::uuid(),
                'jlpt_level_id'     => $n4Id,
                'title'             => 'N4 Grammar Patterns',
                'slug'              => 'n4-grammar-patterns',
                'description'       => 'Master intermediate grammar patterns needed to pass N4.',
                'category'          => 'grammar',
                'estimated_minutes' => 300,
                'sort_order'        => 5,
                'is_published'      => true,
                'thumbnail_url'     => null,
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ];

        DB::table('courses')->insert($courses);
        $this->command->info('✅ Courses seeded — 3 N5 (free) + 2 N4 (individual) courses created.');
    }
}