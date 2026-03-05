<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourseSeeder extends Seeder
{
    public function run(): void
    {

    DB::table('courses')->truncate();
        $n5Id = DB::table('jlpt_levels')->where('code', 'N5')->value('id');

        $courses = [
            [
                'id'                 => Str::uuid(),
                'jlpt_level_id'      => $n5Id,
                'title'              => 'N5 Essential Vocabulary',
                'description'        => 'Master the 800 most important N5 vocabulary words with mnemonics and example sentences.',
                'category'           => 'vocabulary',
                'estimated_minutes'  => 180,
                'sort_order'         => 1,
                'is_published'       => true,
                'thumbnail_url'      => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => Str::uuid(),
                'jlpt_level_id'      => $n5Id,
                'title'              => 'N5 Basic Grammar',
                'description'        => 'Learn fundamental Japanese grammar patterns including particles, verb conjugations, and sentence structure.',
                'category'           => 'grammar',
                'estimated_minutes'  => 240,
                'sort_order'         => 2,
                'is_published'       => true,
                'thumbnail_url'      => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => Str::uuid(),
                'jlpt_level_id'      => $n5Id,
                'title'              => 'N5 Kanji — First 100',
                'description'        => 'Learn the 100 essential kanji required for JLPT N5 with stroke order and readings.',
                'category'           => 'kanji',
                'estimated_minutes'  => 200,
                'sort_order'         => 3,
                'is_published'       => true,
                'thumbnail_url'      => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => Str::uuid(),
                'jlpt_level_id'      => $n5Id,
                'title'              => 'N5 Reading Practice',
                'description'        => 'Short reading passages with furigana to build your reading confidence at N5 level.',
                'category'           => 'reading',
                'estimated_minutes'  => 120,
                'sort_order'         => 4,
                'is_published'       => true,
                'thumbnail_url'      => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
            [
                'id'                 => Str::uuid(),
                'jlpt_level_id'      => $n5Id,
                'title'              => 'N5 Listening Basics',
                'description'        => 'Train your ear with simple Japanese conversations and listening exercises at N5 level.',
                'category'           => 'listening',
                'estimated_minutes'  => 150,
                'sort_order'         => 5,
                'is_published'       => true,
                'thumbnail_url'      => null,
                'created_at'         => now(),
                'updated_at'         => now(),
            ],
        ];

        DB::table('courses')->insert($courses);
        $this->command->info('✅ Courses seeded — 5 N5 courses created.');
    }
}