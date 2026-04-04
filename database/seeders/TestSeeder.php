<?php

namespace Database\Seeders;

use App\Models\JlptLevel;
use App\Models\TestSet;
use App\Models\QuestionBank;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('questions')->truncate();
        DB::table('question_banks')->truncate();
        DB::table('test_sets')->truncate();

        $n5 = JlptLevel::where('code', 'N5')->first();
        $n4 = JlptLevel::where('code', 'N4')->first();
        $n3 = JlptLevel::where('code', 'N3')->first();

        if (!$n5 || !$n4 || !$n3) {
            $this->command->error('JLPT levels missing. Seed jlpt_levels first.');
            return;
        }

        // ---------------- N5 TEST ----------------
        $n5Test = TestSet::create([
            'jlpt_level_id' => $n5->id,
            'title' => 'N5 Vocabulary & Grammar Practice',
            'description' => 'Basic Japanese vocabulary and grammar practice for JLPT N5.',
            'test_type' => 'practice',
            'time_limit_seconds' => 1800,
            'passing_score_percent' => 60,
            'xp_reward_pass' => 50,
            'xp_reward_fail' => 20,
            'is_published' => true,
        ]);

        $n5VocabBank = QuestionBank::create([
            'jlpt_level_id' => $n5->id,
            'test_set_id' => $n5Test->id,
            'name' => 'Vocabulary',
            'section_type' => 'vocabulary',
            'question_count' => 5,
        ]);

        $n5GrammarBank = QuestionBank::create([
            'jlpt_level_id' => $n5->id,
            'test_set_id' => $n5Test->id,
            'name' => 'Grammar',
            'section_type' => 'grammar',
            'question_count' => 5,
        ]);

        $this->insertQuestions($n5VocabBank, [
            [
                'prompt' => '「たべる」の meaning is?',
                'options' => [
                    ['id' => 'A', 'text' => 'to eat'],
                    ['id' => 'B', 'text' => 'to drink'],
                    ['id' => 'C', 'text' => 'to sleep'],
                    ['id' => 'D', 'text' => 'to walk'],
                ],
                'correct_answer' => 'A',
                'explanation' => 'たべる means to eat.',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '「みず」は?',
                'options' => [
                    ['id' => 'A', 'text' => 'water'],
                    ['id' => 'B', 'text' => 'fire'],
                    ['id' => 'C', 'text' => 'earth'],
                    ['id' => 'D', 'text' => 'wind'],
                ],
                'correct_answer' => 'A',
                'explanation' => 'みず means water.',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '「がっこう」は?',
                'options' => [
                    ['id' => 'A', 'text' => 'hospital'],
                    ['id' => 'B', 'text' => 'school'],
                    ['id' => 'C', 'text' => 'station'],
                    ['id' => 'D', 'text' => 'market'],
                ],
                'correct_answer' => 'B',
                'explanation' => 'がっこう means school.',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '「でんしゃ」は?',
                'options' => [
                    ['id' => 'A', 'text' => 'bus'],
                    ['id' => 'B', 'text' => 'train'],
                    ['id' => 'C', 'text' => 'car'],
                    ['id' => 'D', 'text' => 'ship'],
                ],
                'correct_answer' => 'B',
                'explanation' => 'でんしゃ means train.',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '「ともだち」は?',
                'options' => [
                    ['id' => 'A', 'text' => 'friend'],
                    ['id' => 'B', 'text' => 'teacher'],
                    ['id' => 'C', 'text' => 'book'],
                    ['id' => 'D', 'text' => 'room'],
                ],
                'correct_answer' => 'A',
                'explanation' => 'ともだち means friend.',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
            ],
        ]);

        $this->insertQuestions($n5GrammarBank, [
            [
                'prompt' => 'わたし___がくせいです。',
                'options' => [
                    ['id' => 'A', 'text' => 'は'],
                    ['id' => 'B', 'text' => 'を'],
                    ['id' => 'C', 'text' => 'に'],
                    ['id' => 'D', 'text' => 'で'],
                ],
                'correct_answer' => 'A',
                'explanation' => 'は is the topic marker.',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => 'パン___たべます。',
                'options' => [
                    ['id' => 'A', 'text' => 'が'],
                    ['id' => 'B', 'text' => 'を'],
                    ['id' => 'C', 'text' => 'へ'],
                    ['id' => 'D', 'text' => 'と'],
                ],
                'correct_answer' => 'B',
                'explanation' => 'を marks the object.',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '7じ___おきます。',
                'options' => [
                    ['id' => 'A', 'text' => 'に'],
                    ['id' => 'B', 'text' => 'を'],
                    ['id' => 'C', 'text' => 'で'],
                    ['id' => 'D', 'text' => 'が'],
                ],
                'correct_answer' => 'A',
                'explanation' => 'に is used with time.',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => 'にほん___いきます。',
                'options' => [
                    ['id' => 'A', 'text' => 'へ'],
                    ['id' => 'B', 'text' => 'を'],
                    ['id' => 'C', 'text' => 'が'],
                    ['id' => 'D', 'text' => 'は'],
                ],
                'correct_answer' => 'A',
                'explanation' => 'へ indicates direction.',
                'difficulty' => 'easy',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => 'きのう、べんきょう___しませんでした。',
                'options' => [
                    ['id' => 'A', 'text' => 'を'],
                    ['id' => 'B', 'text' => 'が'],
                    ['id' => 'C', 'text' => 'に'],
                    ['id' => 'D', 'text' => 'で'],
                ],
                'correct_answer' => 'A',
                'explanation' => 'べんきょうをします is the correct pattern.',
                'difficulty' => 'medium',
                'question_type' => 'multiple_choice',
            ],
        ]);

        // ---------------- N4 TEST ----------------
        $n4Test = TestSet::create([
            'jlpt_level_id' => $n4->id,
            'title' => 'N4 Reading Practice',
            'description' => 'Short reading comprehension practice for JLPT N4.',
            'test_type' => 'practice',
            'time_limit_seconds' => 2400,
            'passing_score_percent' => 60,
            'xp_reward_pass' => 70,
            'xp_reward_fail' => 30,
            'is_published' => true,
        ]);

        $n4ReadingBank = QuestionBank::create([
            'jlpt_level_id' => $n4->id,
            'test_set_id' => $n4Test->id,
            'name' => 'Reading',
            'section_type' => 'reading',
            'question_count' => 5,
        ]);

        $this->insertQuestions($n4ReadingBank, [
            [
                'prompt' => 'ジョンさんはあした図書館へ行きます。Where will John go tomorrow?',
                'options' => [
                    ['id' => 'A', 'text' => 'School'],
                    ['id' => 'B', 'text' => 'Library'],
                    ['id' => 'C', 'text' => 'Park'],
                    ['id' => 'D', 'text' => 'Bank'],
                ],
                'correct_answer' => 'B',
                'explanation' => '図書館 means library.',
                'difficulty' => 'medium',
                'question_type' => 'reading_comp',
            ],
            [
                'prompt' => 'きょうは雨です。What is the weather today?',
                'options' => [
                    ['id' => 'A', 'text' => 'Sunny'],
                    ['id' => 'B', 'text' => 'Rainy'],
                    ['id' => 'C', 'text' => 'Snowy'],
                    ['id' => 'D', 'text' => 'Windy'],
                ],
                'correct_answer' => 'B',
                'explanation' => '雨 means rain.',
                'difficulty' => 'easy',
                'question_type' => 'reading_comp',
            ],
            [
                'prompt' => '母はスーパーで野菜を買いました。What did mother buy?',
                'options' => [
                    ['id' => 'A', 'text' => 'Vegetables'],
                    ['id' => 'B', 'text' => 'Books'],
                    ['id' => 'C', 'text' => 'Medicine'],
                    ['id' => 'D', 'text' => 'Shoes'],
                ],
                'correct_answer' => 'A',
                'explanation' => '野菜 means vegetables.',
                'difficulty' => 'medium',
                'question_type' => 'reading_comp',
            ],
            [
                'prompt' => 'わたしのへやは二階にあります。Where is my room?',
                'options' => [
                    ['id' => 'A', 'text' => 'First floor'],
                    ['id' => 'B', 'text' => 'Second floor'],
                    ['id' => 'C', 'text' => 'Third floor'],
                    ['id' => 'D', 'text' => 'Outside'],
                ],
                'correct_answer' => 'B',
                'explanation' => '二階 means second floor.',
                'difficulty' => 'medium',
                'question_type' => 'reading_comp',
            ],
            [
                'prompt' => 'この店は九時から六時までです。When is the shop open?',
                'options' => [
                    ['id' => 'A', 'text' => '9 to 6'],
                    ['id' => 'B', 'text' => '8 to 5'],
                    ['id' => 'C', 'text' => '10 to 7'],
                    ['id' => 'D', 'text' => 'Always open'],
                ],
                'correct_answer' => 'A',
                'explanation' => '九時から六時まで means from 9 to 6.',
                'difficulty' => 'medium',
                'question_type' => 'reading_comp',
            ],
        ]);

        // ---------------- N3 TEST ----------------
        $n3Test = TestSet::create([
            'jlpt_level_id' => $n3->id,
            'title' => 'N3 Mini Mock Exam',
            'description' => 'Mini mixed mock exam for JLPT N3.',
            'test_type' => 'mock_exam',
            'time_limit_seconds' => 3600,
            'passing_score_percent' => 60,
            'xp_reward_pass' => 120,
            'xp_reward_fail' => 50,
            'is_published' => true,
        ]);

        $n3VocabBank = QuestionBank::create([
            'jlpt_level_id' => $n3->id,
            'test_set_id' => $n3Test->id,
            'name' => 'Vocabulary',
            'section_type' => 'vocabulary',
            'question_count' => 4,
        ]);

        $n3GrammarBank = QuestionBank::create([
            'jlpt_level_id' => $n3->id,
            'test_set_id' => $n3Test->id,
            'name' => 'Grammar',
            'section_type' => 'grammar',
            'question_count' => 4,
        ]);

        $n3ReadingBank = QuestionBank::create([
            'jlpt_level_id' => $n3->id,
            'test_set_id' => $n3Test->id,
            'name' => 'Reading',
            'section_type' => 'reading',
            'question_count' => 4,
        ]);

        $this->insertQuestions($n3VocabBank, [
            [
                'prompt' => '「改善」に最も近い意味は？',
                'options' => [
                    ['id' => 'A', 'text' => 'よくすること'],
                    ['id' => 'B', 'text' => 'わすれること'],
                    ['id' => 'C', 'text' => 'たずねること'],
                    ['id' => 'D', 'text' => 'かくすこと'],
                ],
                'correct_answer' => 'A',
                'explanation' => '改善 means improvement.',
                'difficulty' => 'medium',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '「努力」の意味は？',
                'options' => [
                    ['id' => 'A', 'text' => 'to rest'],
                    ['id' => 'B', 'text' => 'to try hard'],
                    ['id' => 'C', 'text' => 'to forget'],
                    ['id' => 'D', 'text' => 'to lose'],
                ],
                'correct_answer' => 'B',
                'explanation' => '努力 means effort.',
                'difficulty' => 'medium',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '「状況」に近い言葉は？',
                'options' => [
                    ['id' => 'A', 'text' => 'state/situation'],
                    ['id' => 'B', 'text' => 'price'],
                    ['id' => 'C', 'text' => 'rule'],
                    ['id' => 'D', 'text' => 'chance'],
                ],
                'correct_answer' => 'A',
                'explanation' => '状況 means situation.',
                'difficulty' => 'medium',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '「増加」の反対に近いものは？',
                'options' => [
                    ['id' => 'A', 'text' => 'ふえる'],
                    ['id' => 'B', 'text' => 'へる'],
                    ['id' => 'C', 'text' => 'すすむ'],
                    ['id' => 'D', 'text' => 'ひろがる'],
                ],
                'correct_answer' => 'B',
                'explanation' => '増加 is increase, opposite is decrease.',
                'difficulty' => 'hard',
                'question_type' => 'multiple_choice',
            ],
        ]);

        $this->insertQuestions($n3GrammarBank, [
            [
                'prompt' => '時間がないので、朝ごはんを食べる___出かけた。',
                'options' => [
                    ['id' => 'A', 'text' => 'ことなく'],
                    ['id' => 'B', 'text' => 'ところ'],
                    ['id' => 'C', 'text' => 'ように'],
                    ['id' => 'D', 'text' => 'ほど'],
                ],
                'correct_answer' => 'A',
                'explanation' => 'ことなく means without doing.',
                'difficulty' => 'hard',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => 'この本は子ども___大人まで人気がある。',
                'options' => [
                    ['id' => 'A', 'text' => 'しか'],
                    ['id' => 'B', 'text' => 'まで'],
                    ['id' => 'C', 'text' => 'から'],
                    ['id' => 'D', 'text' => 'だけ'],
                ],
                'correct_answer' => 'C',
                'explanation' => 'から〜まで means from children to adults.',
                'difficulty' => 'medium',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '雨が降る___、試合は中止です。',
                'options' => [
                    ['id' => 'A', 'text' => '場合は'],
                    ['id' => 'B', 'text' => 'ところ'],
                    ['id' => 'C', 'text' => 'ほど'],
                    ['id' => 'D', 'text' => 'ように'],
                ],
                'correct_answer' => 'A',
                'explanation' => '場合は means in case.',
                'difficulty' => 'medium',
                'question_type' => 'multiple_choice',
            ],
            [
                'prompt' => '日本へ行ったら、富士山を見に行く___です。',
                'options' => [
                    ['id' => 'A', 'text' => 'まま'],
                    ['id' => 'B', 'text' => '予定'],
                    ['id' => 'C', 'text' => 'だけ'],
                    ['id' => 'D', 'text' => 'あいだ'],
                ],
                'correct_answer' => 'B',
                'explanation' => '予定です means plan.',
                'difficulty' => 'medium',
                'question_type' => 'multiple_choice',
            ],
        ]);

        $this->insertQuestions($n3ReadingBank, [
            [
                'prompt' => '田中さんは毎日電車で会社へ通っています。How does Tanaka go to work?',
                'options' => [
                    ['id' => 'A', 'text' => 'By bus'],
                    ['id' => 'B', 'text' => 'By train'],
                    ['id' => 'C', 'text' => 'By car'],
                    ['id' => 'D', 'text' => 'On foot'],
                ],
                'correct_answer' => 'B',
                'explanation' => '電車で means by train.',
                'difficulty' => 'medium',
                'question_type' => 'reading_comp',
            ],
            [
                'prompt' => 'この店では現金しか使えません。What is accepted in this shop?',
                'options' => [
                    ['id' => 'A', 'text' => 'Cash only'],
                    ['id' => 'B', 'text' => 'Credit card'],
                    ['id' => 'C', 'text' => 'Bank transfer'],
                    ['id' => 'D', 'text' => 'QR only'],
                ],
                'correct_answer' => 'A',
                'explanation' => '現金しか使えません means only cash is accepted.',
                'difficulty' => 'medium',
                'question_type' => 'reading_comp',
            ],
            [
                'prompt' => '会議は三時に始まる予定でしたが、四時に変更されました。When will the meeting start?',
                'options' => [
                    ['id' => 'A', 'text' => '3 PM'],
                    ['id' => 'B', 'text' => '4 PM'],
                    ['id' => 'C', 'text' => '5 PM'],
                    ['id' => 'D', 'text' => 'Cancelled'],
                ],
                'correct_answer' => 'B',
                'explanation' => 'It was changed from 3 PM to 4 PM.',
                'difficulty' => 'hard',
                'question_type' => 'reading_comp',
            ],
            [
                'prompt' => '図書館では飲み物を飲まないでください。What should you not do in the library?',
                'options' => [
                    ['id' => 'A', 'text' => 'Read books'],
                    ['id' => 'B', 'text' => 'Borrow books'],
                    ['id' => 'C', 'text' => 'Drink beverages'],
                    ['id' => 'D', 'text' => 'Sit quietly'],
                ],
                'correct_answer' => 'C',
                'explanation' => '飲まないでください means please do not drink.',
                'difficulty' => 'medium',
                'question_type' => 'reading_comp',
            ],
        ]);

        $this->command->info('✅ Test sets, question banks, and questions seeded.');
    }

    private function insertQuestions(QuestionBank $bank, array $questions): void
    {
        foreach ($questions as $q) {
            Question::create([
                'question_bank_id' => $bank->id,
                'question_type' => $q['question_type'],
                'prompt' => $q['prompt'],
                'options' => $q['options'],
                'correct_answer' => $q['correct_answer'],
                'explanation' => $q['explanation'],
                'difficulty' => $q['difficulty'],
                'audio_url' => null,
                'is_published' => true,
            ]);
        }
    }
}
