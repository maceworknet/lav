<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$page = App\Models\Page::where('slug', 'ana-sayfa')->with('pageBlocks')->first();
if ($page) {
    foreach ($page->pageBlocks as $block) {
        if ($block->type === 'hero_slider') {
            echo "Block Content JSON:\n";
            print_r($block->content);
        }
    }
} else {
    echo "No page found with slug 'ana-sayfa'.\n";
}
