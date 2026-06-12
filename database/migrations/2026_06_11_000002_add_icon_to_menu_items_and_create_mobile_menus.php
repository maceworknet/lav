<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Menü elemanlarına ikon desteği (mobil alt menü ve sidebar için)
        if (!Schema::hasColumn('menu_items', 'icon')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->string('icon')->nullable()->after('target');
            });
        }

        // Mobil alt menü: yoksa varsayılan öğelerle oluştur (mevcut veri ezilmez)
        $now = now();

        $bottomMenuId = DB::table('menus')->where('slug', 'mobile-bottom-menu')->value('id');
        if (!$bottomMenuId) {
            $bottomMenuId = DB::table('menus')->insertGetId([
                'name' => 'Mobil Alt Menü',
                'slug' => 'mobile-bottom-menu',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $items = [
                ['title' => 'Anasayfa', 'url' => '/', 'icon' => 'home', 'order' => 1],
                ['title' => 'Instagram', 'url' => 'https://instagram.com/', 'icon' => 'instagram', 'order' => 2],
                ['title' => 'Whatsapp', 'url' => '#', 'icon' => 'whatsapp', 'order' => 3],
                ['title' => 'Telefon', 'url' => '#', 'icon' => 'phone', 'order' => 4],
                ['title' => 'Favoriler', 'url' => '/favoriler', 'icon' => 'heart', 'order' => 5],
                ['title' => 'Hesabım', 'url' => '/hesabim', 'icon' => 'user', 'order' => 6],
            ];

            foreach ($items as $item) {
                DB::table('menu_items')->insert(array_merge($item, [
                    'menu_id' => $bottomMenuId,
                    'target' => '_self',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]));
            }
        }

        // Mobil sidebar menüsü: yoksa header menüsünün elemanlarıyla oluştur
        $sidebarMenuId = DB::table('menus')->where('slug', 'mobile-sidebar-menu')->value('id');
        if (!$sidebarMenuId) {
            $sidebarMenuId = DB::table('menus')->insertGetId([
                'name' => 'Mobil Sidebar Menü',
                'slug' => 'mobile-sidebar-menu',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $headerMenuId = DB::table('menus')->where('slug', 'header-menu')->value('id');
            if ($headerMenuId) {
                $headerItems = DB::table('menu_items')
                    ->where('menu_id', $headerMenuId)
                    ->where('is_active', true)
                    ->orderBy('order')
                    ->get();

                foreach ($headerItems as $item) {
                    DB::table('menu_items')->insert([
                        'menu_id' => $sidebarMenuId,
                        'title' => $item->title,
                        'url' => $item->url,
                        'target' => $item->target ?? '_self',
                        'icon' => 'flower',
                        'order' => $item->order,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('menu_items', 'icon')) {
            Schema::table('menu_items', function (Blueprint $table) {
                $table->dropColumn('icon');
            });
        }
    }
};
