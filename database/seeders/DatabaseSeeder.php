<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        // Disable foreign key checks so truncate works cleanly
    DB::statement('SET session_replication_role = replica;');

        $this->call([
            PlanSeeder::class,
            JlptLevelSeeder::class,
            CourseSeeder::class,
            LessonSeeder::class,
            ContentAccessRuleSeeder::class,
             TestSeeder::class,
        ]);

          // Re-enable foreign key checks
    DB::statement('SET session_replication_role = DEFAULT;');
    }
}
