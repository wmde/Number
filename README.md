# DataValues Number

Library containing value objects to represent numeric information, parsers to turn user input
into such value objects, and formatters to turn them back into user consumable representations.

[![Build Status](https://secure.travis-ci.org/DataValues/Number.png?branch=master)](http://travis-ci.org/DataValues/Number)

On [Packagist](https://packagist.org/packages/data-values/number):
[![Latest Stable Version](https://poser.pugx.org/data-values/number/version.png)](https://packagist.org/packages/data-values/number)
[![Download count](https://poser.pugx.org/data-values/number/d/total.png)](https://packagist.org/packages/data-values/number)

## Installation

The recommended way to use this library is via [Composer](http://getcomposer.org/).

### Composer

To add this package as a local, per-project dependency to your project, simply add a
dependency on `data-values/number` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file that just defines a dependency on
version 1.0 of this package:

    {
        "require": {
            "data-values/number": "1.0.*"
        }
    }

### Manual

Get the code of this package, either via git, or some other means. Also get all dependencies.
You can find a list of the dependencies in the "require" section of the composer.json file.
Then take care of autoloading the classes defined in the src directory.

## Tests

This library comes with a set up PHPUnit tests that cover all non-trivial code. You can run these
tests using the PHPUnit configuration file found in the root directory. The tests can also be run
via TravisCI, as a TravisCI configuration file is also provided in the root directory.

## Authors

DataValues Number has been written by the Wikidata team, as [Wikimedia Germany]
(https://wikimedia.de) employees for the [Wikidata project](https://wikidata.org/).

## Release notes

### 0.1 (dev)

Initial release with these features:

	* QuantityValue
	* DecimalFormatter
	* QuantityFormatter
	* DecimalParser
	* FloatParser
	* IntParser
	* QuantityParser

## Links

* [DataValues Number on Packagist](https://packagist.org/packages/data-values/number)
* [DataValues Number on TravisCI](https://travis-ci.org/DataValues/Number)