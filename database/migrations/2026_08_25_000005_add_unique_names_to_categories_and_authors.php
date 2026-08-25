<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $this->mergeDuplicates('categories', 'category_id');
        $this->mergeDuplicates('authors', 'author_id');

        Schema::table('categories', fn (Blueprint $table) => $table->unique('name'));
        Schema::table('authors', fn (Blueprint $table) => $table->unique('name'));
    }

    public function down(): void
    {
        Schema::table('categories', fn (Blueprint $table) => $table->dropUnique(['name']));
        Schema::table('authors', fn (Blueprint $table) => $table->dropUnique(['name']));
    }

    protected function mergeDuplicates(string $table, string $bookForeignKey): void
    {
        DB::table($table)->orderBy('id')->get()->groupBy(
            fn ($row) => mb_strtolower(trim($row->name))
        )->each(function ($rows) use ($table, $bookForeignKey) {
            $keep = $rows->first();
            $duplicateIds = $rows->skip(1)->pluck('id');
            if ($duplicateIds->isEmpty()) return;

            DB::table('books')->whereIn($bookForeignKey, $duplicateIds)->update([$bookForeignKey => $keep->id]);
            DB::table($table)->whereIn('id', $duplicateIds)->delete();
        });
    }
};
