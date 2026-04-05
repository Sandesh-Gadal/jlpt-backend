<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class VocabularyLessonSeeder extends Seeder
{
    private array $translationCache = [];
    private string $cacheFile;

    public function run(): void
    {
        $this->cacheFile = storage_path('app/nepali_translation_cache.json');

        // Load existing cache so interrupted runs can resume
        if (file_exists($this->cacheFile)) {
            $this->translationCache = json_decode(file_get_contents($this->cacheFile), true) ?? [];
            $this->command->info('Loaded ' . count($this->translationCache) . ' cached translations.');
        }

        // Test Google Translate connection
        if (!$this->testConnection()) {
            $this->command->error('Cannot connect to Google Translate. Check your internet connection.');
            return;
        }

        $this->command->info('Google Translate connected ✅');

        DB::table('lessons')->whereIn('lesson_type', ['flashcard'])
            ->whereIn('course_id', function ($q) {
                $q->select('id')->from('courses')->where('category', 'vocabulary');
            })->delete();

        $courseIds = DB::table('courses')
            ->where('category', 'vocabulary')
            ->pluck('id', 'title');

        $csvPath = database_path('data/jlpt_vocab.csv');

        if (!file_exists($csvPath)) {
            $this->command->error("CSV not found at: {$csvPath}");
            $this->command->warn('Copy your jlpt_vocab.csv into database/data/');
            return;
        }

        // ── Parse CSV ──────────────────────────────────────────
        $wordsByLevel = ['N5' => [], 'N4' => [], 'N3' => [], 'N2' => [], 'N1' => []];

        if (($handle = fopen($csvPath, 'r')) !== false) {
            fgetcsv($handle); // skip header
            while (($row = fgetcsv($handle)) !== false) {
                if (count($row) < 4) continue;
                $level = trim($row[3]);
                if (!isset($wordsByLevel[$level])) continue;
                $wordsByLevel[$level][] = [
                    'original' => trim($row[0]),
                    'furigana' => trim($row[1]),
                    'english'  => trim($row[2]),
                    'level'    => $level,
                ];
            }
            fclose($handle);
        }

        $totalWords = array_sum(array_map('count', $wordsByLevel));
        $this->command->info("Loaded {$totalWords} words from CSV.");

        // ── Process each level ──────────────────────────────────
        foreach (['N5', 'N4', 'N3', 'N2', 'N1'] as $level) {
            $words = $wordsByLevel[$level];
            $count = count($words);

            $courseKey = $courseIds->first(
                fn ($id, $title) => str_contains($title, $level)
                                 && str_contains(strtolower($title), 'vocab')
            );

            if (!$courseKey) {
                $this->command->warn("No vocabulary course found for {$level} — skipping.");
                continue;
            }

            $this->command->info("\n── {$level}: {$count} words ──");

            $words = $this->translateWords($words, $level);

            // Build lessons (20 cards each)
            $chunks    = array_chunk($words, 20);
            $lessons   = [];
            $sortOrder = 1;

            foreach ($chunks as $group) {
                $cards = array_map(fn ($w) => [
                    'front'  => "{$w['original']} ({$w['furigana']})",
                    'back'   => $w['english'],
                    'nepali' => $w['nepali'] ?? '',
                ], $group);

                $lessons[] = [
                    'id'                => (string) Str::uuid(),
                    'course_id'         => $courseKey,
                    'title'             => "{$level} Vocabulary — Part {$sortOrder}",
                    'lesson_type'       => 'flashcard',
                    'estimated_minutes' => max(5, count($group)),
                    'xp_reward'         => 10,
                    'sort_order'        => $sortOrder++,
                    'is_published'      => true,
                    'content_json'      => json_encode(['type' => 'flashcard', 'cards' => $cards]),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];
            }

            foreach (array_chunk($lessons, 50) as $batch) {
                DB::table('lessons')->insert($batch);
            }

            $this->command->info("✅ {$level}: {$count} words → " . count($chunks) . " lessons inserted.");
        }

        $this->command->info("\n✅ All vocabulary lessons seeded with Nepali translations.");
    }

    // ── Translate word list one word at a time ───────────────────
    private function translateWords(array $words, string $level): array
    {
        $toTranslate = array_values(array_filter(
            $words,
            function ($w) use ($level) {
                $cached = $this->translationCache[$w['original'] . '|' . $level] ?? '';
                return trim((string) $cached) === '';
            }
        ));

        if (count($toTranslate) === 0) {
            $this->command->line("  All {$level} translations already cached.");
        } else {
            $total = count($toTranslate);
            $this->command->getOutput()->write("  Translating {$total} words");

            foreach ($toTranslate as $i => $word) {
                $nepali = $this->googleTranslate($word['english']);
                $this->translationCache[$word['original'] . '|' . $level] = $nepali;

                // Save cache every 100 words so we can resume if interrupted
                if ($i > 0 && $i % 100 === 0) {
                    file_put_contents(
                        $this->cacheFile,
                        json_encode($this->translationCache, JSON_UNESCAPED_UNICODE)
                    );
                    $this->command->getOutput()->write(" [{$i}/{$total}]");
                }

                usleep(100000); // 100ms between requests to avoid rate limiting
            }

            // Final cache save
            file_put_contents(
                $this->cacheFile,
                json_encode($this->translationCache, JSON_UNESCAPED_UNICODE)
            );

            $this->command->getOutput()->writeln(" [{$total}/{$total}] done.");
        }

        foreach ($words as &$word) {
            $word['nepali'] = $this->translationCache[$word['original'] . '|' . $level] ?? '';
        }
        unset($word);

        return $words;
    }

    // ── Translate a single string via unofficial Google endpoint ─
    private function googleTranslate(string $text): string
    {
        $retries = 3;

        while ($retries-- > 0) {
            try {
                $url = 'https://translate.googleapis.com/translate_a/single'
                     . '?client=gtx&sl=en&tl=ne&dt=t&q=' . urlencode($text);

                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])->timeout(15)->get($url);

                if ($response->successful()) {
                    $data = $response->json();

                    // Response structure: [ [ ["translated", "original", ...], ... ], ... ]
                    $translated = '';
                    if (isset($data[0]) && is_array($data[0])) {
                        foreach ($data[0] as $part) {
                            if (isset($part[0])) {
                                $translated .= $part[0];
                            }
                        }
                    }

                    if (trim($translated) !== '') {
                        return trim($translated);
                    }
                }

                sleep(1);
            } catch (\Exception $e) {
                sleep(2);
            }
        }

        return '';
    }

    // ── Quick connection test ────────────────────────────────────
    private function testConnection(): bool
    {
        try {
            $url = 'https://translate.googleapis.com/translate_a/single'
                 . '?client=gtx&sl=en&tl=ne&dt=t&q=' . urlencode('hello');

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])->timeout(10)->get($url);

            if (!$response->successful()) return false;

            $data = $response->json();
            return isset($data[0][0][0]) && trim($data[0][0][0]) !== '';
        } catch (\Exception $e) {
            return false;
        }
    }
}
