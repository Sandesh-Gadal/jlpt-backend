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

        $rules = [];

        // ── COURSE RULES ───────────────────────────────────
        $n5Courses = DB::table('courses')
            ->whereIn('jlpt_level_id', [
                DB::table('jlpt_levels')->where('code', 'N5')->value('id')
            ])->get();

        $n4Courses = DB::table('courses')
            ->whereIn('jlpt_level_id', [
                DB::table('jlpt_levels')->where('code', 'N4')->value('id')
            ])->get();

        // N5 courses — free plan, preview 2 lessons
        foreach ($n5Courses as $course) {
            $rules[] = [
                'id'                   => Str::uuid(),
                'content_type'         => 'course',
                'content_id'           => $course->id,
                'min_plan_type'        => 'free',
                'preview_allowed'      => true,
                'preview_lesson_count' => 2,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];
        }

        // N4 courses — individual plan required, no preview
        foreach ($n4Courses as $course) {
            $rules[] = [
                'id'                   => Str::uuid(),
                'content_type'         => 'course',
                'content_id'           => $course->id,
                'min_plan_type'        => 'individual',
                'preview_allowed'      => false,
                'preview_lesson_count' => 0,
                'created_at'           => now(),
                'updated_at'           => now(),
            ];
        }

        // ── LESSON RULES ───────────────────────────────────
        $allLessons = DB::table('lessons')
            ->join('courses', 'lessons.course_id', '=', 'courses.id')
            ->join('jlpt_levels', 'courses.jlpt_level_id', '=', 'jlpt_levels.id')
            ->orderBy('lessons.sort_order')
            ->select('lessons.id', 'lessons.sort_order', 'jlpt_levels.code as level_code')
            ->get();

        foreach ($allLessons as $lesson) {
            if ($lesson->level_code === 'N5') {
                // N5 — first 2 lessons free, rest need individual
                $isFree = $lesson->sort_order <= 2;
                $rules[] = [
                    'id'                   => Str::uuid(),
                    'content_type'         => 'lesson',
                    'content_id'           => $lesson->id,
                    'min_plan_type'        => $isFree ? 'free' : 'individual',
                    'preview_allowed'      => $isFree,
                    'preview_lesson_count' => null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];
            } else {
                // N4+ — all lessons need individual plan
                $rules[] = [
                    'id'                   => Str::uuid(),
                    'content_type'         => 'lesson',
                    'content_id'           => $lesson->id,
                    'min_plan_type'        => 'individual',
                    'preview_allowed'      => false,
                    'preview_lesson_count' => null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];
            }
        }

        DB::table('content_access_rules')->insert($rules);
        $this->command->info('✅ Access rules seeded — ' . count($rules) . ' rules created.');
    }
}