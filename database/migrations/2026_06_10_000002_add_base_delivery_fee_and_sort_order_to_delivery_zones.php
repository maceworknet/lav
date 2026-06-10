<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->decimal('base_delivery_fee', 10, 2)->default(0.00)->after('district');
            $table->integer('sort_order')->default(0)->after('is_active');
        });

        Schema::table('delivery_neighborhoods', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropColumn(['base_delivery_fee', 'sort_order']);
        });

        Schema::table('delivery_neighborhoods', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
