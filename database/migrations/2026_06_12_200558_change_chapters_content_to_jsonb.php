<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE chapters
            ALTER COLUMN content
            TYPE jsonb
            USING '{}'::jsonb
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE chapters
            ALTER COLUMN content
            TYPE text
            USING content::text
        ");
    }
};