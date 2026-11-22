<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Photos now live inside the rich text, so the separate gallery column goes away.
     * Existing gallery photos (except the cover) are appended to each translation's content first.
     */
    public function up(): void
    {
        DB::table('posts')->whereNotNull('gallery')->orderBy('id')->each(function (object $post): void {
            $images = collect(json_decode($post->gallery, true) ?: [])
                ->reject(fn (string $path): bool => $path === $post->cover_image)
                ->map(fn (string $path): string => '<p><img src="'.e(str_starts_with($path, 'web-assets/') ? '/'.$path : '/storage/'.$path).'" alt=""></p>')
                ->implode("\n");
            if ($images === '') {
                return;
            }
            DB::table('post_translations')->where('post_id', $post->id)->orderBy('id')->each(function (object $translation) use ($images): void {
                DB::table('post_translations')->where('id', $translation->id)->update([
                    'content' => rtrim((string) $translation->content)."\n".$images,
                ]);
            });
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('gallery');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->jsonb('gallery')->nullable();
        });
    }
};
