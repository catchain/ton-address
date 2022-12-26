<?php

namespace Catchain\Ton\Address;

use function base64_encode, base64_decode, strtr, array_values, pack, unpack, array_merge, floor;

class Utils
{
    /**
     * Base64 encoding with support of web-safe characters.
     */
    public static function base64encode(string $data, bool $urlSafe = true): string
    {
        $encoded = base64_encode($data);

        if ($urlSafe) {
            return strtr($encoded, '/+', '_-');
        }

        return $encoded;
    }

    /**
     * Base64 decoding with support of web-safe characters.
     */
    public static function base64decode(string $input): string
    {
        return base64_decode(strtr($input, '_-', '/+'));
    }

    /**
     * Converts signed hex string to unsigned hex string.
     */
    public static function signedHexAbs(int $input): int
    {
        return (int) unpack('c*', pack('C*', $input))[1];
    }

    /**
     * Telegram-flavored crc16 hashing.
     * @see https://github.com/toncenter/tonweb/blob/master/src/utils/Utils.js#L165
     */
    public static function crc16(string $data): string
    {
        $poly = 0x1021;

        $head = array_values(unpack('C*', $data)); // uint8array
        $tail = array(0, 0); // two-byte prefix

        $bytes = array_merge($head, $tail);
        $reg = 0;

        foreach ($bytes as $byte) {
            $mask = 0x80;
            while ($mask > 0) {
                $reg = $reg << 1;
                if ($byte & $mask) {
                    $reg += 1;
                }
                $mask = $mask >> 1;
                if ($reg > 0xffff) {
                    $reg = $reg & 0xffff;
                    $reg = $reg ^ $poly;
                }
            }
        }

        return pack('CC', (int) floor($reg / 256), $reg % 256);
    }   
}
