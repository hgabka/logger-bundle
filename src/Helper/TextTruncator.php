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
}
