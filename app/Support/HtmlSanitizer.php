<?php

namespace App\Support;

class HtmlSanitizer
{
    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        $allowed = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><a><blockquote><span>';

        $clean = strip_tags($html, $allowed);
        $clean = preg_replace('/on\w+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/javascript\s*:/i', '', $clean) ?? $clean;

        return $clean;
    }
}
