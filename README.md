# PhPsst

[![Latest Stable Version](https://poser.pugx.org/felixsand/phpsst/v/stable)](https://packagist.org/packages/felixsand/phpsst)
[![Build Status](https://travis-ci.org/felixsand/PhPsst.svg?branch=master)](https://travis-ci.org/felixsand/PhPsst)
[![License](https://poser.pugx.org/felixsand/phpsst/license)](https://packagist.org/packages/felixsand/phpsst)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/felixsand/PhPsst/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/felixsand/PhPsst/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/felixsand/PhPsst/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/felixsand/PhPsst/?branch=master)

A PHP library for distributing (one time) passwords/secrets in a more secure way

## Installation
Add the package as a requirement to your `composer.json`:
```bash
$ composer require felixsand/phpsst
```

## Usage
```php
<?php
use PhPsst\PhPsst;
use PhPsst\Storage\FileStorage;

$phPsst = new PhPsst(new FileStorage('data/passwords', 10));
$secret = $phPsst->store('my secret password');
echo "Retrieve the password from: https://example.net/get-password?secret={$secret}";
```
```php
<?php
use PhPsst\PhPsst;
use PhPsst\Storage\FileStorage;

$phPsst = new PhPsst(new FileStorage('data/passwords', 10));
$decryptedPassword = $phPsst->retrieve($_GET['secret']);
echo "The password stored: {$decryptedPassword}";
```

## Storage Classes
### FileStorage
The most basic of the storage classes is the FileStorage. It's also (generally) the most insecure and if you store a lot
of passwords there's a performance issue due to the garbage collector being very crude. It is however the easiest way
to try out the library and useful during development. The constructor parameter $gcProbability is a value from 0 and up,
where 0 disables the GC; 1 means it's run for every file write; 10 means it got a 10% probability of running; etc. It's
not recommended to turn it off.

```php
$phPsst = new PhPsst(new FileStorage('data/passwords', 10));
```

### RedisStorage
The recommended production storage class is the RedisStorage. It has great performance even during heavy use and
since it removes the passwords with expired TTL automatically, it's more secure than the other options.
It's important to note that if you're not reviewing the Redis configuration, it might purge entries even before the
item's TTL has expired (if it's memory limit is reached) and the items will only live for as long as the server is
running. This might be desired properties in certain cases, but you need to be aware of it when setting up the solution.

```php
$redis = new \Predis\Client(array(
    'host' => '10.0.0.1',
    'port' => 6380,
));
$phPsst = new PhPsst(new RedisStorage($redis));
```

### SqLiteStorage
If you don't have access to Redis, another storage engine that is suitable for production use is the SqLiteStorage. It's
not as secure as the RedisStorage, mainly because of it's dependency on a GC; as well as the possibility that the
SqLite DB file might be included in backups, etc. It's also not suitable for setups with several webservers without
access to a shared filesystem. The constructor parameter $gcProbability is the same as for the FileStorage.

```php
$db = new \SQLite3('path/to/sqlite.db');
$phPsst = new PhPsst(new SqLiteStorage($db, 10));
```

## Requirements
- PHP 5.6 or above.
- Redis (for the Redis Storage)

## Author
Felix Sandström <http://github.com/felixsand>

## Special thanks
Andreas <http://github.com/jandreasn> for peer review

## License
Licensed under the MIT License - see the `LICENSE` file for details.
