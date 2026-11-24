<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Event photos of a news post (shown as a gallery under the article). Shared by every language.
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->string('path');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $this->moveBodyPhotosToGallery();
    }

    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }

    /**
     * A photo that sits alone in its own paragraph of the article text is an event photo: it moves to the gallery
     * (once per post, even when Thai and English both carried it) and leaves the text.
     */
    private function moveBodyPhotosToGallery(): void
    {
        $pattern = '#<p>\s*<img\b[^>]*\bsrc="(/[^"]+)"[^>]*>\s*</p>\s*#i';

        DB::table('posts')->orderBy('id')->each(function (object $post) use ($pattern): void {
            $paths = [];
            $translations = DB::table('post_translations')->where('post_id', $post->id)->orderBy('id')->get();

            foreach ($translations as $translation) {
                preg_match_all($pattern, (string) $translation->content, $matches);
                foreach ($matches[1] as $src) {
                    $path = ltrim($src, '/');
                    $path = str_starts_with($path, 'storage/') ? substr($path, strlen('storage/')) : $path;
                    $paths[$path] = true;
                }
                $clean = rtrim((string) preg_replace($pattern, '', (string) $translation->content));
                if ($clean !== (string) $translation->content) {
                    DB::table('post_translations')->where('id', $translation->id)->update(['content' => $clean]);
                }
            }

            $order = 0;
            foreach (array_keys($paths) as $path) {
                if ($path === $post->cover_image) {
                    continue;
                }
                DB::table('post_images')->insert(['post_id' => $post->id, 'path' => $path, 'sort_order' => ++$order, 'created_at' => now(), 'updated_at' => now()]);
            }
        });
    }
};
