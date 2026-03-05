<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionBankSeeder extends Seeder
{
    public function run(): void
    {
        
    DB::table('question_banks')->truncate();
    
        $n5Id = DB::table('jlpt_levels')->where('code', 'N5')->value('id');

        $banks = [
            [
                'id'             => Str::uuid(),
                'jlpt_level_id'  => $n5Id,
                'name'           => 'N5 Vocabulary Questions',
                'section_type'   => 'vocabulary',
                'question_count' => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'id'             => Str::uuid(),
                'jlpt_level_id'  => $n5Id,
                'name'           => 'N5 Grammar Questions',
                'section_type'   => 'grammar',
                'question_count' => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'id'             => Str::uuid(),
                'jlpt_level_id'  => $n5Id,
                'name'           => 'N5 Reading Questions',
                'section_type'   => 'reading',
                'question_count' => 0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
        ];

        DB::table('question_banks')->insert($banks);
        $this->command->info('✅ Question banks seeded — 3 N5 banks created.');
    }
}