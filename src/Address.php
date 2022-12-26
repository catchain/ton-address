<?php

namespace Catchain\Ton\Address;

use function intval, trigger_error, strlen, is_string, preg_match, sprintf, bin2hex, strpos, pack, unpack;
use Exception;

const TAG_BOUNCABLE = 0x11;
const TAG_NON_BOUNCABLE = 0x51;
const FLAG_TESTNET = 0x80;

class Address
{
    public readonly int $wc;
    public readonly string $hashPart;
    public readonly bool $isTestOnly;
    public readonly bool $isBounceable;
    public readonly bool $isUserFriendly;
    public readonly bool $isUrlSafe;

    /**
     * @param int      $wc
     * @param string   $hashPart
     * @param bool     $isTestOnly
     * @param bool     $isBounceable
     * @param bool     $isUserFriendly
     * @param bool     $isUrlSafe
     */
    public function __construct(
        int $wc,
        string $hashPart,
        bool $isTestOnly = false,
        bool $isBounceable = true,
        bool $isUserFriendly = true,
        bool $isUrlSafe = true,
    ) {
        $this->wc = intval($wc);

        if ($this->wc !== 0 && $this->wc !== -1) {
            trigger_error(sprintf('Workchain should be 0 or -1, given %d', $this->wc), \E_USER_WARNING);
        }

        $this->hashPart = $hashPart;

        if (strlen($this->hashPart) !== 32) {
            throw new Exception('Address hash part must be a 32-byte binary string');
        }

        $this->isTestOnly = $isTestOnly;
        $this->isBounceable = $isBounceable;
        $this->isUserFriendly = $isUserFriendly;
        $this->isUrlSafe = $isUrlSafe;
    }

    /**
     * Parses the input value, returns filled Address object.
     */
    public static function parse(Address|string $input): Address
    {
        if ($input instanceof Address) {
            return clone $input;
        }

        if (!is_string($input)) {
            throw new Exception('Input must be a string');
        }

        // Native format (wc:hashPartInHex):
        if (preg_match("/^(?<wc>\-?\d)\:(?<hashpart>[a-f\d]{64})$/i", $input, $match)) {
            return new static(
                wc: intval($match['wc']),
                hashPart: hex2bin($match['hashpart']),
                isUserFriendly: false);

        // Short base64 format:
        } elseif (strlen($input) === 48) {
            return self::parseHumanReadable($input);
        }

        throw new Exception('Invalid address format');
    }

    /**
     * Parses the address in human-readable base64 format.
     */
    private static function parseHumanReadable(string $input): Address
    {
        $addressBytes = Utils::base64decode($input);

        if (strlen($addressBytes) !== 36) {
            throw new Exception('Invalid address format: length must be 36 bytes');
        }

        $address = unpack('ctag/cwc/a32hashpart/a2crc16', $addressBytes);

        $crc16hash = Utils::crc16(substr($addressBytes, 0, 34));

        if ($crc16hash !== $address['crc16']) {
            throw new Exception(sprintf('Wrong crc16 hashsum, expected %s, given %s',
                Utils::base64encode($crc16hash),
                Utils::base64encode($address['crc16'])
            ));
        }

        $tag = $address['tag'];
        $isUrlSafe = strpos($input, '+') === false && strpos($input, '/') === false;
        $isTestOnly = false;
        $isBounceable = false;

        if ($tag & FLAG_TESTNET) {
            $isTestOnly = true;
            $tag = Utils::signedHexAbs($tag ^ FLAG_TESTNET);
        }

        if ($tag !== TAG_BOUNCABLE && $tag !== TAG_NON_BOUNCABLE) {
            throw new Exception(sprintf('Invalid tag: expected %d or %d, given %d', TAG_BOUNCABLE, TAG_NON_BOUNCABLE, $tag));
        }

        return new static(isUserFriendly: true,
            wc: $address['wc'],
            hashPart: $address['hashpart'],
            isTestOnly: $isTestOnly,
            isBounceable: $tag === TAG_BOUNCABLE,
            isUrlSafe: $isUrlSafe);
    }

    /**
     * Checks whether the input contains valid address.
     */
    public static function isValid(mixed $input): bool
    {
        try {
            self::parse($input);
            return true;

        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Returns stringified address in different formats.
     */
    public function toString(
        ?bool $userFriendly = null,
        ?bool $urlSafe = null,
        ?bool $bounceable = null,
        ?bool $testOnly = null,
    ): string {
        if ($userFriendly === null) $userFriendly = $this->isUserFriendly;
        if ($urlSafe === null) $urlSafe = $this->isUrlSafe;
        if ($bounceable === null) $bounceable = $this->isBounceable;
        if ($testOnly === null) $testOnly = $this->isTestOnly;

        if (!$userFriendly) {
            return sprintf('%d:%s', $this->wc, bin2hex($this->hashPart));
        }

        $tag = $bounceable ? TAG_BOUNCABLE : TAG_NON_BOUNCABLE;

        if ($testOnly) {
            $tag = $tag | FLAG_TESTNET;
        }

        $address = pack('cca*', $tag, $this->wc, $this->hashPart);
        $addressWithHash = $address . Utils::crc16($address);

        return Utils::base64encode($addressWithHash, $urlSafe);
    }

    /**
     * Serializes address to the format used in tonscan.org (bouncable base64 web-safe):
     */
    public function toTonscanFormat(): string
    {
        return $this->toString(userFriendly: true, urlSafe: true, bounceable: true);
    }
}
