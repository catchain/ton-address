<?php

use Catchain\Ton\Address\Address;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->addresses = [
            'unfriendly' => '-1:811ced271f8f449cb51eb5920090b92cb200b20f07170676e9db6fbe9da516cf',
            'userFriendlyWebSafeBouncable' => 'Ef-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz8VY',
            'userFriendlyWebUnsafeNonBouncable' => 'Uf+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWz5id',
            'userFriendlyWebSafeTestOnlyBouncable' => 'kf-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz37S',
            'userFriendlyWebUnsafeTestOnlyNonBouncable' => '0f+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWzyMX',
        ];
    }

    public function testCanBeCreatedFromStringInDifferentFormats(): void
    {
        foreach ($this->addresses as $name => $address) {
            $this->assertInstanceOf(Address::class, Address::parse($address));
        }
    }

    public function testAddressesInDifferentFormatsAllHaveTheSameHashPart(): void
    {
        $hashParts = array_map(fn ($address) => Address::parse($address)->hashPart, array_values($this->addresses));

        $this->assertEquals(...$hashParts);
    }

    public function testAllAddressesAreSerializedUsingTheSameParamsAsInitialAddress(): void
    {
        foreach ($this->addresses as $name => $address) {
            $this->assertEquals($address, Address::parse($address)->toString());
        }
    }

    public function testCanBeCreatedFromOtherAddressObject(): void
    {
        $addr = Address::parse('-1:811ced271f8f449cb51eb5920090b92cb200b20f07170676e9db6fbe9da516cf');
        $cloned = Address::parse($addr);

        $this->assertInstanceOf(Address::class, $cloned);
        $this->assertEquals($addr->toString(), $cloned->toString());
    }

    public function testCannotBeCreatedWithInvalidHash(): void
    {
        $this->expectException(\Exception::class);

        Address::parse('Ef-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz000');
    }

    public function testCannotBeCreatedWithInvalidFriendlyLength(): void
    {
        $this->expectException(\Exception::class);

        Address::parse('EEf-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz000');
    }

    public function testCannotBeCreatedWithInvalidUnfriendlyLength(): void
    {
        $this->expectException(\Exception::class);

        Address::parse('-1:811ced271f8f449cb51eb5920090b92cb200b20f07170676e9db6fbe9da51');
    }

    public function testAddressValidator(): void
    {
        $this->assertTrue(Address::isValid('Uf+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWz5id'));
        $this->assertFalse(Address::isValid('12345'));
    }

    public function testFriendlyAddressUrlSafeParamIsParsedCorrectly(): void
    {
        $this->assertFalse(Address::parse('Uf+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWz5id')->isUrlSafe);
        $this->assertTrue(Address::parse('Ef-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz8VY')->isUrlSafe);
    }

    public function testDefaultUrlSafeParamIsTrue(): void
    {
        $this->assertTrue(Address::parse('EQDCH6vT0MvVp0bBYNjoONpkgb51NMPNOJXFQWG54XoIAs5Y')->isUrlSafe);
    }

    public function testBouncableFlagParsedCorrectly(): void
    {
        $this->assertFalse(Address::parse('Uf+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWz5id')->isBounceable);
        $this->assertTrue(Address::parse('Ef-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz8VY')->isBounceable);
    }

    public function testUserFriendlyFlagParsedCorrectly(): void
    {
        $this->assertTrue(Address::parse('Ef-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz8VY')->isUserFriendly);
        $this->assertFalse(Address::parse('-1:811ced271f8f449cb51eb5920090b92cb200b20f07170676e9db6fbe9da516cf')->isUserFriendly);
    }

    public function testSerializationParams(): void
    {
        $address = Address::parse('-1:811ced271f8f449cb51eb5920090b92cb200b20f07170676e9db6fbe9da516cf');

        $this->assertEquals('-1:811ced271f8f449cb51eb5920090b92cb200b20f07170676e9db6fbe9da516cf', $address->toString(
            userFriendly: false,
        ));

        $this->assertEquals('Ef-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz8VY', $address->toString(
            userFriendly: true,
            testOnly: false,
            urlSafe: true,
            bounceable: true,
        ));

        $this->assertEquals('Uf+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWz5id', $address->toString(
            userFriendly: true,
            testOnly: false,
            urlSafe: false,
            bounceable: false,
        ));

        $this->assertEquals('kf-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz37S', $address->toString(
            userFriendly: true,
            testOnly: true,
            urlSafe: true,
            bounceable: true,
        ));

        $this->assertEquals('0f+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWzyMX', $address->toString(
            userFriendly: true,
            testOnly: true,
            urlSafe: false,
            bounceable: false,
        ));
    }

    public function testTonscanSerializationFormat()
    {
        $address = 'Uf+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWz5id';

        $this->assertEquals('Ef-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz8VY', Address::parse($address)->toTonscanFormat());
    }
}
