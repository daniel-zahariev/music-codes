[![License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](https://github.com/daniel-zahariev/music-codes/blob/master/COPYING)
[![Build Status](https://app.travis-ci.com/daniel-zahariev/music-codes.svg?branch=master)](https://app.travis-ci.com/github/daniel-zahariev/music-codes)
[![Coverage Status](https://coveralls.io/repos/github/daniel-zahariev/music-codes/badge.svg?branch=master)](https://coveralls.io/github/daniel-zahariev/music-codes?branch=master)

# Music Codes


Music Codes PHP library provides handy tools for codes in the music industry

Isrc
====

**Note 1: for the country code portion of the ISRC the library only checks if it is comprised of 2 alphabetical characters** 
**Note 2: ISRC with id 0 are not considered valid; example: `GB-A1B-11-00000`** 

Setting ISRC parts:

```php

$isrc = new Isrc();
$isrc->setCountryCode('GB');
$isrc->setIssuerCode('A1B');
$isrc->setYear(11);
$isrc->setId(3);

// Prefix is the combination of country code and issuer code
// and is now adopted as standard wording in ISRC specification
// in order to allow allocation of ranges of ISRCs
$isrc->setPrefix('GB-A1B'); // can be used with or without the dash   

// will output 'Valid'
echo ($isrc->isValid() ? 'Valid' : 'Not valid') . PHP_EOL;

// By default, the ids equal to zero are not considered as valid
// so to enable this globally you should call the following method:
Isrc::treatZeroIdsAsValid(true);

$isrc = new Isrc('GB-A1B-11-00000')
// will output 'Valid'
echo ($isrc->isValid() ? 'Valid' : 'Not valid') . PHP_EOL; 

```

Different formatting options:

```php

$isrc = new Isrc('GB-A1B-11-00003');

// outputs dashed ISRC: 'GB-A1B-11-00003'
echo $isrc->getIsrc(true) . PHP_EOL;

// outputs ISRC without dashes:'GBA1B1100003'
echo $isrc->getIsrc(false) . PHP_EOL;

// outputs ISRC withot dashes and prefixed: 'ISRC GBA1B1100003'
echo $isrc->getIsrc(false, true) . PHP_EOL;

```

Navigating up & down:

```php

$isrc = new Isrc('GB-A1B-11-00003');

// go one down and if it's valid output it
// outputs 'GB-A1B-11-00002'
if ($isrc->previous()) {
	echo $isrc->getIsrc(true) . PHP_EOL;
}

// reset
$isrc->load('GB-A1B-11-00003');

// go one up
$isrc->next();

// go one up and if it's valid output it
// outputs 'GB-A1B-11-00004'
if ($isrc->next()) {
	echo $isrc->getIsrc(true) . PHP_EOL;
}

```

ISWC
====

There are various ways to instantiate an ISWC object:

```php

$iswc = new Iswc('T-034.524.680-1'); // use either all separators
$iswc = new Iswc('T0345246801'); // or none

$iswc = Iswc::fromId('034.524.680');
$iswc = Iswc::fromId('034524680');

```

Navigating up & down:

```php

$iswc = new Iswc('T-034.524.680-1');

// Bumps up the id and auto-calculates the check digit
// the ISWC is now: T-034.524.681-2
$iswc->next();

// reset
$iswc->load('T-034.524.680-1');

// the ISWC is now: T-034.524.679-8
$iswc->previous();

```

Formatting:

```php

$iswc = new Iswc('T-034.524.680-1');

echo $iswc; // outputs 'T-034.524.680-1'

echo $iswc->getIswc(true); // outputs 'T-034.524.680-1'
echo $iswc->getIswc(false); // outputs 'T0345246801'

```


Label Code
====

There are various ways to instantiate an LabelCode object:

```php

$label_code = new LabelCode('LC-1234'); // dashes will be stripped by default
$label_code = new LabelCode('LC1234');

$label_code = LabelCode::fromParts('LC', 1234);
$label_code = LabelCode::fromParts('LC-', '1234');

```

Navigating up & down:

```php

$label_code = new LabelCode('LC-1234');

// Bumps up the id
// the Label Code is now: LC-1235
$label_code->next();

// reset
$label_code->load('LC-1234');

// the Label Code is now: LC-1233
$label_code->previous();

```

Formatting:

```php

$label_code = new LabelCode('LC-1234');

echo $label_code; // outputs 'LC1234'

echo $label_code->gecLabelCode(true); // outputs 'LC-1234'
echo $label_code->gecLabelCode(false); // outputs 'LC1234'

```


Changelog
=========

**v1.3.1**
- add method `treatZeroIdsAsValid` to Isrc class to enable validation of ISRC codes where the id is 0
- add param to `isValid` method in Isrc class to enable revalidation on demand


License
=======

MIT License

See [COPYING](COPYING) to see the full text.