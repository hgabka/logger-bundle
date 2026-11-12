<?php

namespace Hgabka\LoggerBundle\Helper;

/**
 * A log mezok TEXT tipusuak (max 65535 bajt); STRICT sql_mode-ban a hosszabb ertek
 * "Data too long" hibat okozna (pl. base64 kepet tartalmazo POST), ezert csonkoljuk.
 */
final class TextTruncator
{
    public const MAX_LENGTH = 65000;

    public static function truncate(string|false|null $str, int $max = self::MAX_LENGTH): ?string
    {
        if (null === $str || false === $str) {
            return null;
        }

        return \strlen($str) > $max ? mb_strcut($str, 0, $max, 'UTF-8') : $str;
    }

    /**
     * Az ervenytelen UTF-8 bajtokat (pl. a szkennerek overlong "%c0%a7" payloadjai) '?'-re cseréli: az utf8/utf8mb4
     * oszlopok MySQL 1366 hibaval utasitjak vissza oket, ami bezarja az EntityManagert is.
     */
    public static function scrub(string|false|null $str): ?string
    {
        if (null === $str || false === $str) {
            return null;
        }

        return mb_scrub($str, 'UTF-8');
    }

    /**
     * json_encode, ami az ervenytelen UTF-8 bajtokat nem dobja el (false), hanem U+FFFD-re cseréli.
     */
    public static function json(mixed $value): string|false
    {
        return json_encode($value, \JSON_INVALID_UTF8_SUBSTITUTE | \JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
}
