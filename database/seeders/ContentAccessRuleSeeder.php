<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContentAccessRuleSeeder extends Seeder
{
    public function run(): void
    {
    DB::table('content_access_rules')->truncate();
        $courses = DB::table('courses')->get();
        $rules   = [];

        foreach ($courses as $course) {
            // N5 courses — free users get first 5 lessons only
            $rules[] = [
                'id'                  => Str::uuid(),
                'content_type'        => 'course',
                'content_id'          => $course->id,
                'min_plan_type'       => 'free',
                'preview_allowed'     => true,
                'preview_lesson_count'=> 5,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        // Individual lessons — first 3 free, rest need pro
        $lessons = DB::table('lessons')->get();
        foreach ($lessons as $index => $lesson) {
            $rules[] = [
                'id'                  => Str::uuid(),
                'content_type'        => 'lesson',
                'content_id'          => $lesson->id,
                'min_plan_type'       => $index < 3 ? 'free' : 'individual',
                'preview_allowed'     => $index < 3,
                'preview_lesson_count'=> null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        // Question banks — free gets vocabulary only
        $banks = DB::table('question_banks')->get();
        foreach ($banks as $bank) {
            $rules[] = [
                'id'                  => Str::uuid(),
                'content_type'        => 'question_bank',
                'content_id'          => $bank->id,
                'min_plan_type'       => $bank->section_type === 'vocabulary' ? 'free' : 'individual',
                'preview_allowed'     => $bank->section_type === 'vocabulary',
                'preview_lesson_count'=> null,
                'created_at'          => now(),
                'updated_at'          => now(),
            ];
        }

        DB::table('content_access_rules')->insert($rules);
        $this->command->info('✅ Content access rules seeded — ' . count($rules) . ' rules created.');
    }
}