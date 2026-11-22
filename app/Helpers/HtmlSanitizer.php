<?php

namespace App\Helpers;

use Symfony\Component\HtmlSanitizer\HtmlSanitizer as SymfonySanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

/**
 * Cleans rich text (TinyMCE) on the server before it is saved. Only the tags
 * listed here survive, so the front office can print the saved HTML with {!! !!}.
 * Never trust the editor itself: a request can skip it and post anything.
 */
class HtmlSanitizer
{
    protected static ?SymfonySanitizer $sanitizer = null;

    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        return self::sanitizer()->sanitize($html);
    }

    protected static function sanitizer(): SymfonySanitizer
    {
        if (self::$sanitizer !== null) {
            return self::$sanitizer;
        }

        $config = new HtmlSanitizerConfig;
        foreach (['p', 'br', 'hr', 'strong', 'b', 'em', 'i', 'u', 's', 'sub', 'sup', 'h2', 'h3', 'h4', 'ul', 'ol', 'li', 'blockquote', 'pre', 'code', 'figure', 'figcaption', 'table', 'thead', 'tbody', 'tr'] as $tag) {
            $config = $config->allowElement($tag);
        }
        $config = $config
            ->allowElement('th', ['colspan', 'rowspan'])
            ->allowElement('td', ['colspan', 'rowspan'])
            ->allowElement('a', ['href', 'title', 'target'])
            ->allowElement('img', ['src', 'alt', 'title', 'width', 'height'])
            ->allowLinkSchemes(['http', 'https', 'mailto', 'tel'])
            ->allowMediaSchemes(['http', 'https'])
            ->allowRelativeLinks()
            ->allowRelativeMedias()
            ->forceAttribute('a', 'rel', 'noopener noreferrer');

        return self::$sanitizer = new SymfonySanitizer($config);
    }
}
