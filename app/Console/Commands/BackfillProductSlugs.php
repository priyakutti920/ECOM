<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillProductSlugs extends Command
{
 protected $signature = 'products:backfill-slugs';
 protected $description = 'Generate slugs for existing products that have no slug';

 public function handle()
 {
 $count = 0;
 $products = Product::whereNull('slug')->orWhere('slug', '')->get();
 foreach ($products as $p) {
 $base = Str::slug($p->name);
 $slug = $base;
 $i = 1;
 while (Product::withTrashed()->where('slug', $slug)->where('id', '!=', $p->id)->exists()) {
 $slug = $base . '-' . $i++;
 }
 $p->slug = $slug;
 $p->save();
 $count++;
 }
 $this->info("Backfilled {$count} products with slugs.");
 }
}
