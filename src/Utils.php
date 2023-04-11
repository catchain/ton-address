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
            return strtr($encoded, '=/+', '-_-');
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
    public static function crc16(string $data): string {
        $crc = 0x0000;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= ord($data[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                if ($crc & 0x8000) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc = $crc << 1;
                }
            }
        }
        return $crc & 0xFFFF;
    }
}
