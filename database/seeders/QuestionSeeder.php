<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
            DB::table('questions')->truncate();

        $vocabBankId   = DB::table('question_banks')->where('section_type', 'vocabulary')->value('id');
        $grammarBankId = DB::table('question_banks')->where('section_type', 'grammar')->value('id');
        $readingBankId = DB::table('question_banks')->where('section_type', 'reading')->value('id');

        $questions = [

            // ── Vocabulary questions ───────────────────────
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $vocabBankId,
                'question_type'    => 'multiple_choice',
                'prompt'           => 'What is the meaning of 水 (みず)?',
                'options'          => json_encode(['A' => 'Fire', 'B' => 'Water', 'C' => 'Mountain', 'D' => 'Tree']),
                'correct_answer'   => 'B',
                'explanation'      => '水 (みず) means water. It is also read as スイ in compound words like 水曜日 (Wednesday).',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $vocabBankId,
                'question_type'    => 'multiple_choice',
                'prompt'           => 'What does 食べます (たべます) mean?',
                'options'          => json_encode(['A' => 'To drink', 'B' => 'To sleep', 'C' => 'To eat', 'D' => 'To read']),
                'correct_answer'   => 'C',
                'explanation'      => '食べます is the polite form of 食べる (taberu), meaning "to eat".',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $vocabBankId,
                'question_type'    => 'multiple_choice',
                'prompt'           => 'Which word means "school"?',
                'options'          => json_encode(['A' => '病院', 'B' => '学校', 'C' => '図書館', 'D' => '駅']),
                'correct_answer'   => 'B',
                'explanation'      => '学校 (がっこう) means school. 病院 = hospital, 図書館 = library, 駅 = station.',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $vocabBankId,
                'question_type'    => 'multiple_choice',
                'prompt'           => 'What is the reading of 月曜日?',
                'options'          => json_encode(['A' => 'かようび', 'B' => 'もくようび', 'C' => 'げつようび', 'D' => 'きんようび']),
                'correct_answer'   => 'C',
                'explanation'      => '月曜日 (げつようび) means Monday. 月 = moon/month, read as げつ in this context.',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $vocabBankId,
                'question_type'    => 'multiple_choice',
                'prompt'           => 'What does 大きい (おおきい) mean?',
                'options'          => json_encode(['A' => 'Small', 'B' => 'Fast', 'C' => 'Old', 'D' => 'Big']),
                'correct_answer'   => 'D',
                'explanation'      => '大きい (おおきい) means big or large. Its opposite is 小さい (ちいさい) meaning small.',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],

            // ── Grammar questions ──────────────────────────
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $grammarBankId,
                'question_type'    => 'multiple_choice',
                'prompt'           => 'Choose the correct particle: わたし___ 学生です。',
                'options'          => json_encode(['A' => 'を', 'B' => 'が', 'C' => 'は', 'D' => 'に']),
                'correct_answer'   => 'C',
                'explanation'      => 'は (wa) is the topic marker. わたしは学生です = I am a student. は marks わたし as the topic.',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $grammarBankId,
                'question_type'    => 'multiple_choice',
                'prompt'           => 'Which is correct? "I drink water."',
                'options'          => json_encode(['A' => '水は飲みます。', 'B' => '水を飲みます。', 'C' => '水が飲みます。', 'D' => '水に飲みます。']),
                'correct_answer'   => 'B',
                'explanation'      => 'を marks the direct object. 水を飲みます = drink water. を is used with action verbs.',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $grammarBankId,
                'question_type'    => 'multiple_choice',
                'prompt'           => 'What does ～ません mean?',
                'options'          => json_encode(['A' => 'Polite positive', 'B' => 'Polite negative', 'C' => 'Past tense', 'D' => 'Question form']),
                'correct_answer'   => 'B',
                'explanation'      => '～ません is the polite negative verb ending. 食べません = do not eat. ～ます is positive, ～ません is negative.',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],

            // ── Reading questions ──────────────────────────
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $readingBankId,
                'question_type'    => 'reading_comp',
                'prompt'           => "Read the passage and answer:\n\nたなかさんは まいにち がっこうへ いきます。がっこうは うちから ちかいです。たなかさんは にほんごと すうがくが すきです。\n\nWhere does Tanaka-san go every day?",
                'options'          => json_encode(['A' => 'To the hospital', 'B' => 'To the library', 'C' => 'To school', 'D' => 'To the station']),
                'correct_answer'   => 'C',
                'explanation'      => 'がっこうへ いきます means "goes to school". がっこう = school, いきます = go.',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'id'               => Str::uuid(),
                'question_bank_id' => $readingBankId,
                'question_type'    => 'reading_comp',
                'prompt'           => "Read and answer:\n\nきょうは どようびです。わたしは うちに います。ともだちと えいがを みます。えいがは さんじに はじまります。\n\nWhat time does the movie start?",
                'options'          => json_encode(['A' => '1 o\'clock', 'B' => '2 o\'clock', 'C' => '3 o\'clock', 'D' => '4 o\'clock']),
                'correct_answer'   => 'C',
                'explanation'      => 'さんじ means 3 o\'clock. さん = three, じ = o\'clock. はじまります = starts.',
                'difficulty'       => 'easy',
                'flag_count'       => 0,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ];

        DB::table('questions')->insert($questions);

        // Update question counts in banks
        DB::table('question_banks')->where('id', $vocabBankId)
            ->update(['question_count' => DB::table('questions')->where('question_bank_id', $vocabBankId)->count()]);
        DB::table('question_banks')->where('id', $grammarBankId)
            ->update(['question_count' => DB::table('questions')->where('question_bank_id', $grammarBankId)->count()]);
        DB::table('question_banks')->where('id', $readingBankId)
            ->update(['question_count' => DB::table('questions')->where('question_bank_id', $readingBankId)->count()]);

        $this->command->info('✅ Questions seeded — ' . count($questions) . ' N5 questions created.');
    }
}