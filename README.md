# DataValues Number

Library containing value objects to represent numeric information, parsers to turn user input
into such value objects, and formatters to turn them back into user consumable representations.

It is part of the [DataValues set of libraries](https://github.com/DataValues).

[![Build Status](https://secure.travis-ci.org/wmde/Number.png?branch=master)](http://travis-ci.org/wmde/Number)
[![Code Coverage](https://scrutinizer-ci.com/g/wmde/Number/badges/coverage.png?s=a62dd85d05eaf0c5505deed4e2bd53d34e50d158)](https://scrutinizer-ci.com/g/wmde/Number/)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/wmde/Number/badges/quality-score.png?s=03279530fa55439de3ce094b985f861959ee7162)](https://scrutinizer-ci.com/g/wmde/Number/)

On [Packagist](https://packagist.org/packages/data-values/number):
[![Latest Stable Version](https://poser.pugx.org/data-values/number/version.png)](https://packagist.org/packages/data-values/number)
[![Download count](https://poser.pugx.org/data-values/number/d/total.png)](https://packagist.org/packages/data-values/number)

## Installation

The recommended way to use this library is via [Composer](http://getcomposer.org/).

### Composer

To add this package as a local, per-project dependency to your project, simply add a
dependency on `data-values/number` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file that just defines a dependency on
version 0.8 of this package:

    {
        "require": {
            "data-values/number": "0.12.*"
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

DataValues Number was created by [Wikimedia Deutschland](https://www.wikimedia.de/) employees for
the [Wikidata project](https://www.wikidata.org/).

## Release notes

### 0.12.3 (2022-10-24)

* Allow use with data-values/common 1.1.0 and data-values/interfaces 1.x

### 0.12.2 (2022-10-21)

* Fix `QuantityValue` and `UnboundedQuantityValue` hashes to be identical to version 0.11.1.

### 0.12.1 (2022-10-21)

* Allow use with data-values/data-values 3.1.0

### 0.12.0 (2022-10-21)

* Improve compatibility with PHP 8.1;
  in particular, the new `__serialize`/`__unserialize` methods are implemented now
  (in addition to the still supported `Serializable` interface).
  Make sure to also use `data-values/data-values` version 3.1.0 (or later) to keep hashes stable.
* Remove the `DATAVALUES_NUMBER_VERSION` constant.

### 0.11.1 (2021-03-31)

* Fix `DecimalMath::productWithoutBC` for products larger than 2^63-1 (the maximum value of a signed 64 bit integer).

### 0.11.0 (2021-03-15)

* Drop support for php versions older than 7.2 and HHVM
 
### 0.10.2 (2021-03-15)

* Allow use with data-values/common 1.0.0
* Allow use with data-values/interfaces 1.0.0
* Allow use with data-values/data-values 3.0.0

### 0.10.1 (2018-10-31)

* Allow installation together with DataValues 2.x
* DecimalMath products now get rounded to 127 characters to avoid a fatal error

### 0.10.0 (2018-04-11)

* Changed the float to string conversion algorithm for `DecimalValue`, `QuantityValue`, and
  `UnboundedQuantityValue`. Instead of a hundred mostly irrelevant decimal places it now uses PHP's
  "serialize_precision" default of 17 significant digits.
* Drop compatibility with data-values/interfaces 0.1 and data-values/common 0.2

### 0.9.1 (2017-08-09)

* Allow use with data-values/common 0.4

### 0.9.0 (2017-08-09)

* Remove MediaWiki integration
* Strip all whitespace in DecimalParser
* Use Wikibase's CodeSniffer instead of MediaWiki's

### 0.8.3 (2017-06-26)

* Fixed `UnboundedQuantityValue::newFromArray` not accepting mixed values.
* Deprecated `DecimalValue::newFromArray` and `UnboundedQuantityValue::newFromArray`.
* Updated minimal required PHP version from 5.3 to 5.5.9.

### 0.8.2 (2016-11-17)

* Fixed `QuantityFormatter` suppressing ±0 for `QuantityValue`s.
* Fixed HTML escaping in `QuantityHtmlFormatter`.

### 0.8.1 (2016-08-02)

* `UnboundedQuantityValue::newFromArray` and `QuantityValue::newFromArray` both accept
  serializations without and with an uncertainty interval.

### 0.8.0 (2016-08-01)

* Added `DecimalValue::getTrimmed`.
* Added `UnboundedQuantityValue`.
    * `QuantityValue` extends `UnboundedQuantityValue`.
    * `QuantityParser` returns `UnboundedQuantityValue`s instead of always guessing an uncertainty
      interval.
    * `QuantityFormatter` also accepts `UnboundedQuantityValue`s.
* `QuantityParser` defaults to ±0.5 instead of ±1 when asked to guess an uncertainty interval, e.g.
  `1~` becomes `1±0.5`.
* `QuantityFormatter` does not round any more when the value is rendered with a known uncertainty
  interval.
* Fixed rounding algorithm in `DecimalMath` (rounded 1.45 to 2 instead of 1).
* `DecimalValue` constructor optionally accepts strings with no leading plus sign.
* Removed `QuantityValue::getSignificantFigures`.
* Removed `QuantityValue::newFromDecimal` (deprecated since 0.1).
* The `$vocabularyUriFormatter` parameter in the `QuantityFormatter` constructor is not nullable any more.

### 0.7.0 (2016-04-25)

#### Breaking changes
* Removed deprecated `QuantityUnitFormatter` interface.
* Removed deprecated `BasicQuantityUnitFormatter`.

#### Other changes
* Fixed `DecimalValue` and `QuantityValue` allowing values with a newline at the end.
* `DecimalValue` strings are trimmed now, allowing any number of leading and trailing whitespace.
* Added explicit compatibility with data-values/common 0.2 and 0.3.

### 0.6.0 (2015-09-09)

#### Breaking changes
* `QuantityFormatter` constructor parameters changed in an incompatible way.
* `BasicNumberUnlocalizer::getUnitRegex` returns an empty string. A `QuantityParser` using this
	does not accept units as part of the input any more.

#### Additions
* Added `QuantityHtmlFormatter`.
* `QuantityFormatter` supports an optional format string to concatenate number and unit.

#### Other changes
* Deprecated `QuantityUnitFormatter` interface.
* Deprecated `BasicQuantityUnitFormatter`.
* `QuantityParser` now always trims the unit it gets via option.
* The component can now be installed together with DataValues Interfaces 0.2.x.

### 0.5.0 (2015-06-11)

#### Breaking changes
* `QuantityFormatter` constructor parameters changed in an incompatible way

#### Additions
* Added `QuantityUnitFormatter` interface
* Added `BasicQuantityUnitFormatter`
* Added `QuantityFormatter::OPT_APPLY_UNIT` option
* Added `QuantityParser::OPT_UNIT` option
* Added `DecimalParser::applyDecimalExponent`
* Added `DecimalParser::splitDecimalExponent`

#### Other changes
* `QuantityParser` now correctly detects precision for scientific notation
* Made constructor parameters optional in `DecimalFormatter` and `QuantityFormatter`
* Updated DataValues Interfaces dependency to 0.1.5

### 0.4.1 (2014-10-09)

* The component can now be installed together with DataValues 1.x

### 0.4 (2014-04-24)

* Unlocalizer interface renamed to NumberUnlocalizer
* Localizer interface renamed to NumberLocalizer
* BasicUnlocalizer interface renamed to BasicNumberUnlocalizer
* BasicLocalizer interface renamed to BasicNumberLocalizer
* Introduced FORMAT_NAME class constants on ValueParsers in order to use them as
	expectedFormat
* Changed ValueParsers to pass rawValue and expectedFormat when constructing
	a ParseException

### 0.3 (2014-03-12)

* Unlocalizer: added getNumberRegex() and getUnitRegex()
* Unlocalizer: replaced unlocalize() with unlocalizeNumber()
* Localizer: replaced localize() with localizeNumber()
* Localizer and Unlocalizer: no longer require the target language and options in method calls
* QuantityParser: fixed parsing of internationalized quantity strings

### 0.2 (2013-12-16)

#### Removals

* IntParser got moved to data-values/common
* FloatParser got moved to data-values/common

#### Additions

* DecimalMath::min
* DecimalMath::max
* DecimalMath::shift
* Added option to force displaying the sign in DecimalFormatter

#### Improvements

* QuantityParser and DecimalParser now support scientific notation
* DecimalParser now supports localized parsing of values
* DecimalFormatter now supports localization of values

#### Bug fixes

* Floating point errors that occurred when manipulating decimal values have been fixed.
([bug 56682](https://bugzilla.wikimedia.org/show_bug.cgi?id=56682))

### 0.1 (2013-11-17)

Initial release with these features:

* DecimalMath
* DecimalValue
* QuantityValue
* DecimalFormatter
* QuantityFormatter
* DecimalParser
* FloatParser
* IntParser
* QuantityParser

## Links

* [DataValues Number on Packagist](https://packagist.org/packages/data-values/number)
* [DataValues Number on TravisCI](https://travis-ci.org/wmde/Number)
