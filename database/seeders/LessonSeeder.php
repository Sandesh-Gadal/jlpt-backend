<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LessonSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('lessons')->truncate();

        $vocabN5Id   = DB::table('courses')->where('title', 'N5 Essential Vocabulary')->value('id');
        $grammarN5Id = DB::table('courses')->where('title', 'N5 Basic Grammar')->value('id');
        $vocabN4Id   = DB::table('courses')->where('title', 'N4 Vocabulary')->value('id');
        $grammarN4Id = DB::table('courses')->where('title', 'N4 Grammar Patterns')->value('id');

        $lessons = [

            // ────────────────────────────────────────────────
            // N5 VOCABULARY — first 2 FREE, lesson 3+ LOCKED
            // ────────────────────────────────────────────────

            [
                'id'                => Str::uuid(),
                'course_id'         => $vocabN5Id,
                'title'             => 'Lesson 1 — Numbers & Counting',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 20,
                'xp_reward'         => 10,
                'sort_order'        => 1,
                'is_published'      => true,
                // FREE ✅
                'content_json'      => json_encode([
                    'type'  => 'flashcard',
                    'cards' => [
                        ['front' => '一 (いち)',      'back' => 'One',   'example' => '一つのリンゴ — One apple'],
                        ['front' => '二 (に)',        'back' => 'Two',   'example' => '二人の友達 — Two friends'],
                        ['front' => '三 (さん)',      'back' => 'Three', 'example' => '三時 — 3 o\'clock'],
                        ['front' => '四 (し/よん)',   'back' => 'Four',  'example' => '四月 — April'],
                        ['front' => '五 (ご)',        'back' => 'Five',  'example' => '五円 — 5 yen'],
                        ['front' => '六 (ろく)',      'back' => 'Six',   'example' => '六時間 — Six hours'],
                        ['front' => '七 (しち/なな)', 'back' => 'Seven', 'example' => '七日 — Seventh day'],
                        ['front' => '八 (はち)',      'back' => 'Eight', 'example' => '八月 — August'],
                        ['front' => '九 (く/きゅう)', 'back' => 'Nine',  'example' => '九時 — 9 o\'clock'],
                        ['front' => '十 (じゅう)',    'back' => 'Ten',   'example' => '十円 — 10 yen'],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'                => Str::uuid(),
                'course_id'         => $vocabN5Id,
                'title'             => 'Lesson 2 — Days of the Week',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 15,
                'xp_reward'         => 10,
                'sort_order'        => 2,
                'is_published'      => true,
                // FREE ✅
                'content_json'      => json_encode([
                    'type'  => 'flashcard',
                    'cards' => [
                        ['front' => '月曜日 (げつようび)', 'back' => 'Monday'],
                        ['front' => '火曜日 (かようび)',   'back' => 'Tuesday'],
                        ['front' => '水曜日 (すいようび)', 'back' => 'Wednesday'],
                        ['front' => '木曜日 (もくようび)', 'back' => 'Thursday'],
                        ['front' => '金曜日 (きんようび)', 'back' => 'Friday'],
                        ['front' => '土曜日 (どようび)',   'back' => 'Saturday'],
                        ['front' => '日曜日 (にちようび)', 'back' => 'Sunday'],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'                => Str::uuid(),
                'course_id'         => $vocabN5Id,
                'title'             => 'Lesson 3 — Family Members',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 20,
                'xp_reward'         => 10,
                'sort_order'        => 3,
                'is_published'      => true,
                // LOCKED 🔒 — individual required
                'content_json'      => json_encode([
                    'type'  => 'flashcard',
                    'cards' => [
                        ['front' => '父 (ちち)',           'back' => 'Father (my own)'],
                        ['front' => 'お父さん (おとうさん)', 'back' => 'Father (someone else\'s)'],
                        ['front' => '母 (はは)',           'back' => 'Mother (my own)'],
                        ['front' => 'お母さん (おかあさん)', 'back' => 'Mother (someone else\'s)'],
                        ['front' => '兄 (あに)',           'back' => 'Older brother'],
                        ['front' => '姉 (あね)',           'back' => 'Older sister'],
                        ['front' => '弟 (おとうと)',       'back' => 'Younger brother'],
                        ['front' => '妹 (いもうと)',       'back' => 'Younger sister'],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'                => Str::uuid(),
                'course_id'         => $vocabN5Id,
                'title'             => 'Lesson 4 — Colors',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 15,
                'xp_reward'         => 10,
                'sort_order'        => 4,
                'is_published'      => true,
                // LOCKED 🔒 — individual required
                'content_json'      => json_encode([
                    'type'  => 'flashcard',
                    'cards' => [
                        ['front' => '赤 (あか)',   'back' => 'Red'],
                        ['front' => '青 (あお)',   'back' => 'Blue'],
                        ['front' => '白 (しろ)',   'back' => 'White'],
                        ['front' => '黒 (くろ)',   'back' => 'Black'],
                        ['front' => '黄色 (きいろ)', 'back' => 'Yellow'],
                        ['front' => '緑 (みどり)', 'back' => 'Green'],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ────────────────────────────────────────────────
            // N5 GRAMMAR — first 2 FREE, lesson 3+ LOCKED
            // ────────────────────────────────────────────────

            [
                'id'                => Str::uuid(),
                'course_id'         => $grammarN5Id,
                'title'             => 'Lesson 1 — は (wa) Particle',
                'lesson_type'       => 'text',
                'estimated_minutes' => 25,
                'xp_reward'         => 15,
                'sort_order'        => 1,
                'is_published'      => true,
                // FREE ✅
                'content_json'      => json_encode([
                    'type'     => 'text',
                    'sections' => [
                        [
                            'heading' => 'What is は?',
                            'body'    => 'は (pronounced "wa") is the topic marker particle. It marks what the sentence is about.',
                        ],
                        [
                            'heading'  => 'Examples',
                            'examples' => [
                                ['jp' => 'わたしは学生です。', 'reading' => 'Watashi wa gakusei desu.', 'en' => 'I am a student.'],
                                ['jp' => 'これはペンです。',   'reading' => 'Kore wa pen desu.',       'en' => 'This is a pen.'],
                            ],
                        ],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'                => Str::uuid(),
                'course_id'         => $grammarN5Id,
                'title'             => 'Lesson 2 — を (wo) Particle',
                'lesson_type'       => 'text',
                'estimated_minutes' => 25,
                'xp_reward'         => 15,
                'sort_order'        => 2,
                'is_published'      => true,
                // FREE ✅
                'content_json'      => json_encode([
                    'type'     => 'text',
                    'sections' => [
                        [
                            'heading' => 'What is を?',
                            'body'    => 'を marks the direct object of a verb — the thing that receives the action.',
                        ],
                        [
                            'heading'  => 'Examples',
                            'examples' => [
                                ['jp' => 'りんごを食べます。', 'reading' => 'Ringo wo tabemasu.', 'en' => 'I eat an apple.'],
                                ['jp' => '水を飲みます。',     'reading' => 'Mizu wo nomimasu.',  'en' => 'I drink water.'],
                            ],
                        ],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'                => Str::uuid(),
                'course_id'         => $grammarN5Id,
                'title'             => 'Lesson 3 — に (ni) Particle',
                'lesson_type'       => 'text',
                'estimated_minutes' => 25,
                'xp_reward'         => 15,
                'sort_order'        => 3,
                'is_published'      => true,
                // LOCKED 🔒 — individual required
                'content_json'      => json_encode([
                    'type'     => 'text',
                    'sections' => [
                        [
                            'heading' => 'What is に?',
                            'body'    => 'に marks direction, location of existence, and time.',
                        ],
                        [
                            'heading'  => 'Examples',
                            'examples' => [
                                ['jp' => '学校に行きます。', 'reading' => 'Gakkou ni ikimasu.', 'en' => 'I go to school.'],
                                ['jp' => '三時に起きます。', 'reading' => 'Sanji ni okimasu.',  'en' => 'I wake up at 3.'],
                            ],
                        ],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ────────────────────────────────────────────────
            // N4 VOCABULARY — ALL LOCKED 🔒 (individual plan)
            // ────────────────────────────────────────────────

            [
                'id'                => Str::uuid(),
                'course_id'         => $vocabN4Id,
                'title'             => 'Lesson 1 — N4 Verbs Group 1',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 25,
                'xp_reward'         => 15,
                'sort_order'        => 1,
                'is_published'      => true,
                // LOCKED 🔒 — individual required
                'content_json'      => json_encode([
                    'type'  => 'flashcard',
                    'cards' => [
                        ['front' => '受ける (うける)',   'back' => 'To receive / take (exam)'],
                        ['front' => '変える (かえる)',   'back' => 'To change something'],
                        ['front' => '続ける (つづける)', 'back' => 'To continue'],
                        ['front' => '集める (あつめる)', 'back' => 'To collect / gather'],
                        ['front' => '調べる (しらべる)', 'back' => 'To investigate / check'],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'                => Str::uuid(),
                'course_id'         => $vocabN4Id,
                'title'             => 'Lesson 2 — N4 Adjectives',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 20,
                'xp_reward'         => 15,
                'sort_order'        => 2,
                'is_published'      => true,
                // LOCKED 🔒 — individual required
                'content_json'      => json_encode([
                    'type'  => 'flashcard',
                    'cards' => [
                        ['front' => '複雑 (ふくざつ)', 'back' => 'Complicated'],
                        ['front' => '正直 (しょうじき)', 'back' => 'Honest'],
                        ['front' => '丁寧 (ていねい)', 'back' => 'Polite / careful'],
                        ['front' => '便利 (べんり)',   'back' => 'Convenient'],
                        ['front' => '不便 (ふべん)',   'back' => 'Inconvenient'],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ────────────────────────────────────────────────
            // N4 GRAMMAR — ALL LOCKED 🔒 (individual plan)
            // ────────────────────────────────────────────────

            [
                'id'                => Str::uuid(),
                'course_id'         => $grammarN4Id,
                'title'             => 'Lesson 1 — ～ている (te iru) Form',
                'lesson_type'       => 'text',
                'estimated_minutes' => 30,
                'xp_reward'         => 20,
                'sort_order'        => 1,
                'is_published'      => true,
                // LOCKED 🔒 — individual required
                'content_json'      => json_encode([
                    'type'     => 'text',
                    'sections' => [
                        [
                            'heading' => 'What is ～ている?',
                            'body'    => 'ている expresses an ongoing action or a resulting state.',
                        ],
                        [
                            'heading'  => 'Examples',
                            'examples' => [
                                ['jp' => '食べている。',   'reading' => 'Tabete iru.',   'en' => 'I am eating.'],
                                ['jp' => '結婚している。', 'reading' => 'Kekkon shite iru.', 'en' => 'I am married.'],
                            ],
                        ],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'                => Str::uuid(),
                'course_id'         => $grammarN4Id,
                'title'             => 'Lesson 2 — ～たことがある (Past Experience)',
                'lesson_type'       => 'text',
                'estimated_minutes' => 30,
                'xp_reward'         => 20,
                'sort_order'        => 2,
                'is_published'      => true,
                // LOCKED 🔒 — individual required
                'content_json'      => json_encode([
                    'type'     => 'text',
                    'sections' => [
                        [
                            'heading' => 'What is ～たことがある?',
                            'body'    => 'Use this to express that you have had an experience at some point in the past.',
                        ],
                        [
                            'heading'  => 'Examples',
                            'examples' => [
                                ['jp' => '日本に行ったことがある。', 'reading' => 'Nihon ni itta koto ga aru.', 'en' => 'I have been to Japan.'],
                                ['jp' => '寿司を食べたことがある。', 'reading' => 'Sushi wo tabeta koto ga aru.', 'en' => 'I have eaten sushi.'],
                            ],
                        ],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('lessons')->insert($lessons);
        $this->command->info('✅ Lessons seeded — ' . count($lessons) . ' lessons created (N5 free/locked + N4 all locked).');
    }
}