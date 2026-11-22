<?php

namespace Tests\Unit;

use App\Helpers\WebDate;
use App\Helpers\WebMediaResolver;
use Carbon\Carbon;
use Tests\TestCase;

class WebHelpersTest extends TestCase
{
    public function test_shipped_asset_path_resolves_to_public_url(): void
    {
        $this->assertSame(asset('web-assets/news/a.webp'), WebMediaResolver::fileUrl('web-assets/news/a.webp'));
    }

    public function test_empty_path_has_no_url(): void
    {
        $this->assertNull(WebMediaResolver::fileUrl(null));
        $this->assertNull(WebMediaResolver::fileUrl(''));
    }

    public function test_date_chip_uses_buddhist_year_for_thai_only(): void
    {
        $date = Carbon::parse('2026-10-09');

        app()->setLocale('th');
        $this->assertSame(2569, WebDate::chip($date)['year']);

        app()->setLocale('en');
        $this->assertSame(2026, WebDate::chip($date)['year']);
        $this->assertSame('09', WebDate::chip($date)['day']);
    }
}
