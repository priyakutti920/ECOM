<?php

namespace App\Console\Commands;

use App\Models\Category;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class BackfillCategorySlugs extends Command
{
 protected $signature = 'categories:backfill-slugs';
 protected $description = 'Generate slugs for existing categories that have no slug';

 public function handle()
 {
 $count = 0;
 $categories = Category::whereNull('slug')->orWhere('slug', '')->get();
 foreach ($categories as $cat) {
 $base = Str::slug($cat->name);
 $slug = $base;
 $i = 1;
 while (Category::where('slug', $slug)->where('id', '!=', $cat->id)->exists()) {
 $slug = $base . '-' . $i++;
 }
 $cat->slug = $slug;
 $cat->save();
 $count++;
 }
 $this->info("Backfilled {$count} categories with slugs.");
 }
}
