<?php

namespace Tests\Unit;

use App\Helpers\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_scripts_and_event_handlers_are_removed(): void
    {
        $clean = HtmlSanitizer::clean('<p onclick="x()">Hi<script>alert(1)</script><img src="https://a.test/i.jpg" onerror="y()"></p>');

        $this->assertStringNotContainsString('script', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringContainsString('<p>Hi', $clean);
        $this->assertStringContainsString('<img src="https://a.test/i.jpg"', $clean);
    }

    public function test_javascript_links_are_dropped_and_safe_links_get_rel(): void
    {
        $clean = HtmlSanitizer::clean('<a href="javascript:alert(1)">bad</a> <a href="https://example.com">ok</a>');

        $this->assertStringNotContainsString('javascript:', $clean);
        $this->assertStringContainsString('href="https://example.com"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);
    }

    public function test_iframes_and_styles_are_removed_but_formatting_stays(): void
    {
        $clean = HtmlSanitizer::clean('<h2>Title</h2><iframe src="https://evil.test"></iframe><p style="x:y"><strong>bold</strong></p>');

        $this->assertStringNotContainsString('iframe', $clean);
        $this->assertStringNotContainsString('style=', $clean);
        $this->assertStringContainsString('<h2>Title</h2>', $clean);
        $this->assertStringContainsString('<strong>bold</strong>', $clean);
    }

    public function test_empty_input_gives_empty_string(): void
    {
        $this->assertSame('', HtmlSanitizer::clean(null));
        $this->assertSame('', HtmlSanitizer::clean('  '));
    }
}
