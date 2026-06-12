<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('mail_templates', 'is_html')) {
            Schema::table('mail_templates', function (Blueprint $table) {
                $table->boolean('is_html')->default(false)->after('recipient_type');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mail_templates', 'is_html')) {
            Schema::table('mail_templates', function (Blueprint $table) {
                $table->dropColumn('is_html');
            });
        }
    }
};
