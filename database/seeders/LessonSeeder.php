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

        $vocabCourseId   = DB::table('courses')->where('category', 'vocabulary')->value('id');
        $grammarCourseId = DB::table('courses')->where('category', 'grammar')->value('id');
        $kanjiCourseId   = DB::table('courses')->where('category', 'kanji')->value('id');

        $lessons = [

            // ── Vocabulary lessons ─────────────────────────
            [
                'id'                => Str::uuid(),
                'course_id'         => $vocabCourseId,
                'title'             => 'Lesson 1 — Numbers & Counting',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 20,
                'xp_reward'         => 10,
                'sort_order'        => 1,
                'is_published'      => true,
                'content_json'      => json_encode([
                    'type'  => 'flashcard',
                    'cards' => [
                        ['front' => '一 (いち)', 'back' => 'One', 'example' => '一つのリンゴ — One apple'],
                        ['front' => '二 (に)',   'back' => 'Two', 'example' => '二人の友達 — Two friends'],
                        ['front' => '三 (さん)', 'back' => 'Three', 'example' => '三時 — 3 o\'clock'],
                        ['front' => '四 (し/よん)', 'back' => 'Four', 'example' => '四月 — April'],
                        ['front' => '五 (ご)',   'back' => 'Five', 'example' => '五円 — 5 yen'],
                        ['front' => '六 (ろく)', 'back' => 'Six', 'example' => '六時間 — Six hours'],
                        ['front' => '七 (しち/なな)', 'back' => 'Seven', 'example' => '七日 — Seventh day'],
                        ['front' => '八 (はち)', 'back' => 'Eight', 'example' => '八月 — August'],
                        ['front' => '九 (く/きゅう)', 'back' => 'Nine', 'example' => '九時 — 9 o\'clock'],
                        ['front' => '十 (じゅう)', 'back' => 'Ten', 'example' => '十円 — 10 yen'],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'                => Str::uuid(),
                'course_id'         => $vocabCourseId,
                'title'             => 'Lesson 2 — Days of the Week',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 15,
                'xp_reward'         => 10,
                'sort_order'        => 2,
                'is_published'      => true,
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
                'course_id'         => $vocabCourseId,
                'title'             => 'Lesson 3 — Family Members',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 20,
                'xp_reward'         => 10,
                'sort_order'        => 3,
                'is_published'      => true,
                'content_json'      => json_encode([
                    'type'  => 'flashcard',
                    'cards' => [
                        ['front' => '父 (ちち)',     'back' => 'Father (my own)'],
                        ['front' => 'お父さん (おとうさん)', 'back' => 'Father (someone else\'s)'],
                        ['front' => '母 (はは)',     'back' => 'Mother (my own)'],
                        ['front' => 'お母さん (おかあさん)', 'back' => 'Mother (someone else\'s)'],
                        ['front' => '兄 (あに)',     'back' => 'Older brother (my own)'],
                        ['front' => '姉 (あね)',     'back' => 'Older sister (my own)'],
                        ['front' => '弟 (おとうと)', 'back' => 'Younger brother'],
                        ['front' => '妹 (いもうと)', 'back' => 'Younger sister'],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ── Grammar lessons ────────────────────────────
            [
                'id'                => Str::uuid(),
                'course_id'         => $grammarCourseId,
                'title'             => 'Lesson 1 — は (wa) Particle',
                'lesson_type'       => 'text',
                'estimated_minutes' => 25,
                'xp_reward'         => 15,
                'sort_order'        => 1,
                'is_published'      => true,
                'content_json'      => json_encode([
                    'type'     => 'text',
                    'sections' => [
                        [
                            'heading' => 'What is は?',
                            'body'    => 'は (pronounced "wa") is the topic marker particle. It marks what the sentence is about.',
                        ],
                        [
                            'heading' => 'Basic Pattern',
                            'body'    => '[Topic] は [Comment] です',
                        ],
                        [
                            'heading' => 'Examples',
                            'examples' => [
                                ['jp' => 'わたしは学生です。', 'reading' => 'Watashi wa gakusei desu.', 'en' => 'I am a student.'],
                                ['jp' => 'これはペンです。',   'reading' => 'Kore wa pen desu.',       'en' => 'This is a pen.'],
                                ['jp' => 'あれは山です。',     'reading' => 'Are wa yama desu.',       'en' => 'That is a mountain.'],
                            ],
                        ],
                        [
                            'heading' => 'Practice',
                            'body'    => 'Try making your own sentences using は. Start with わたしは...',
                        ],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'                => Str::uuid(),
                'course_id'         => $grammarCourseId,
                'title'             => 'Lesson 2 — を (wo) Particle',
                'lesson_type'       => 'text',
                'estimated_minutes' => 25,
                'xp_reward'         => 15,
                'sort_order'        => 2,
                'is_published'      => true,
                'content_json'      => json_encode([
                    'type'     => 'text',
                    'sections' => [
                        [
                            'heading' => 'What is を?',
                            'body'    => 'を (pronounced "wo" or "o") marks the direct object of a verb — the thing that receives the action.',
                        ],
                        [
                            'heading' => 'Basic Pattern',
                            'body'    => '[Subject] は [Object] を [Verb]',
                        ],
                        [
                            'heading' => 'Examples',
                            'examples' => [
                                ['jp' => 'りんごを食べます。',   'reading' => 'Ringo wo tabemasu.',     'en' => 'I eat an apple.'],
                                ['jp' => '本を読みます。',       'reading' => 'Hon wo yomimasu.',       'en' => 'I read a book.'],
                                ['jp' => '水を飲みます。',       'reading' => 'Mizu wo nomimasu.',      'en' => 'I drink water.'],
                            ],
                        ],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ── Kanji lessons ──────────────────────────────
            [
                'id'                => Str::uuid(),
                'course_id'         => $kanjiCourseId,
                'title'             => 'Lesson 1 — Basic Kanji: Nature',
                'lesson_type'       => 'flashcard',
                'estimated_minutes' => 20,
                'xp_reward'         => 10,
                'sort_order'        => 1,
                'is_published'      => true,
                'content_json'      => json_encode([
                    'type'  => 'flashcard',
                    'cards' => [
                        ['front' => '山', 'back' => 'Mountain (やま / サン)', 'example' => '富士山 — Mt. Fuji'],
                        ['front' => '川', 'back' => 'River (かわ / セン)',    'example' => '川の水 — River water'],
                        ['front' => '木', 'back' => 'Tree (き / モク)',       'example' => '木の葉 — Leaf'],
                        ['front' => '火', 'back' => 'Fire (ひ / カ)',         'example' => '火曜日 — Tuesday'],
                        ['front' => '水', 'back' => 'Water (みず / スイ)',    'example' => '水曜日 — Wednesday'],
                        ['front' => '月', 'back' => 'Moon/Month (つき / ゲツ)', 'example' => '月曜日 — Monday'],
                        ['front' => '日', 'back' => 'Sun/Day (ひ / ニチ)',    'example' => '日曜日 — Sunday'],
                        ['front' => '土', 'back' => 'Earth/Soil (つち / ド)', 'example' => '土曜日 — Saturday'],
                    ],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('lessons')->insert($lessons);
        $this->command->info('✅ Lessons seeded — ' . count($lessons) . ' N5 lessons created.');
    }
}