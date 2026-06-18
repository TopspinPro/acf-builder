<?php

namespace Tsp\AcfBuilder\Runtime\Support;

use Tsp\AcfBuilder\AdminVisibility;

class RuntimeUtils
{
    public static function sanitizeKey(mixed $value): string
    {
        return AdminVisibility::sanitizeKey($value);
    }

    public static function sanitizeText(mixed $value): string
    {
        return AdminVisibility::sanitizeText($value);
    }

    public static function currentPostId(): int
    {
        $postId = self::absint($_POST['post_ID'] ?? 0);

        if ($postId > 0) {
            return $postId;
        }

        return self::absint($_GET['post'] ?? 0);
    }

    public static function currentOptionsPageSlug(): ?string
    {
        $page = $_GET['page'] ?? null;

        if (!is_string($page)) {
            return null;
        }

        $page = self::sanitizeKey(self::wpUnslash($page));

        return $page !== '' ? $page : null;
    }

    public static function wpUnslash(mixed $value): mixed
    {
        return function_exists('wp_unslash') ? wp_unslash($value) : self::stripslashesDeep($value);
    }

    public static function absint(mixed $value): int
    {
        return function_exists('absint') ? absint($value) : abs((int) $value);
    }

    private static function stripslashesDeep(mixed $value): mixed
    {
        if (is_array($value)) {
            return array_map([self::class, 'stripslashesDeep'], $value);
        }

        return is_string($value) ? stripslashes($value) : $value;
    }
}
