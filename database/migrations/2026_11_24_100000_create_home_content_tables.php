<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        // One row per editable home block (hero, story_1..3, presentation): media + non-translated values
        Schema::create('home_blocks', function (Blueprint $table) {
            $table->string('code', 30)->primary();
            $table->string('video_path')->nullable();
            $table->string('poster_path')->nullable();
            $table->jsonb('plain')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        // Translated text of a block: one row per language, the fields are a json map
        Schema::create('home_block_texts', function (Blueprint $table) {
            $table->id();
            $table->string('block_code', 30);
            $table->string('locale', 10);
            $table->jsonb('fields');
            $table->timestamps();

            $table->foreign('block_code')->references('code')->on('home_blocks')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('locale')->references('code')->on('languages')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['block_code', 'locale']);
        });

        Schema::create('home_agencies', function (Blueprint $table) {
            $table->id();
            $table->string('logo_path')->nullable();
            $table->string('url', 500);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('home_agency_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('home_agencies')->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('name');
            $table->timestamps();

            $table->foreign('locale')->references('code')->on('languages')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['agency_id', 'locale']);
        });

        $this->seedAgencies();
    }

    public function down(): void
    {
        Schema::dropIfExists('home_agency_translations');
        Schema::dropIfExists('home_agencies');
        Schema::dropIfExists('home_block_texts');
        Schema::dropIfExists('home_blocks');
    }

    /**
     * The six agencies that were hard coded in the home view, in their displayed order.
     */
    private function seedAgencies(): void
    {
        $agencies = [
            [6, 'https://www.egat.co.th/home/', 'electricity_generating_authority_of_thailand'],
            [5, 'https://www.energy.go.th/th', 'ministry_of_energy'],
            [4, 'https://www.erc.or.th/', 'office_of_the_energy_regulatory'],
            [3, 'http://www.sepo.go.th/', 'state_enterprise_policy_office'],
            [2, 'https://namngiep1.com/', 'nam_ngiep_1_power_company'],
            [1, 'https://www.adaroindonesia.com/', 'pt_adaro_indonesia'],
        ];
        $locales = DB::table('languages')->pluck('code')->all();
        $strings = [];
        foreach ($locales as $locale) {
            $path = rtrim(config('web_locale.storage_path'), '/')."/{$locale}.json";
            $strings[$locale] = Storage::disk('local')->exists($path)
                ? (json_decode(Storage::disk('local')->get($path), true)['strings'] ?? [])
                : [];
        }

        foreach ($agencies as $order => [$logo, $url, $nameKey]) {
            $id = DB::table('home_agencies')->insertGetId([
                'logo_path' => "web-assets/stakeholders/{$logo}.png",
                'url' => $url,
                'sort_order' => $order + 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($locales as $locale) {
                $name = $strings[$locale]["stakeholders.{$nameKey}"] ?? null;
                if ($name) {
                    DB::table('home_agency_translations')->insert(['agency_id' => $id, 'locale' => $locale, 'name' => $name, 'created_at' => now(), 'updated_at' => now()]);
                }
            }
        }
    }
};
