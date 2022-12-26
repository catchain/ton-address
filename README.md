# TON Addresses for PHP
This package contains some PHP utility functions for working with TON blockchain addresses.

### Installation
```
composer require catchain/ton-address
```

### Usage

Creating Address object:

```php
use Catchain\Ton\Address\Address;

// Supports all address formats:
$address = Address::parse('-1:811ced271f8f449cb51eb5920090b92cb200b20f07170676e9db6fbe9da516cf');
```

Object structure:

```
Catchain\Ton\Address\Address {
  +wc: -1,
  +hashPart: b"ü\x1CÝ'\x1FÅD£Á\x1EÁÆ\0É╣,▓\0▓\x0F\x07\x17\x06vÚ█o¥ØÑ\x16¤",
  +isTestOnly: true,
  +isBounceable: true,
  +isUserFriendly: true,
  +isUrlSafe: true,
}
```

Serializing to string (arguments are self-explanatory):

```php
$address->toString(
    userFriendly: true,
    urlSafe: true,
    bounceable: true,
    testOnly: false,
);

// Ef-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz8VY
```

Serializing to the long format:

```php
$address->toString(userFriendly: false);

// -1:811ced271f8f449cb51eb5920090b92cb200b20f07170676e9db6fbe9da516cf
```

By default the object is serialized to the same format that it was created from:

```php
Address::parse('Uf+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWz5id')->toString();
// returns Uf+BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22++naUWz5id

Address::parse('kf-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz37S')->toString();
// returns kf-BHO0nH49EnLUetZIAkLkssgCyDwcXBnbp22--naUWz37S
```
