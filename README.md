# Wikibase DataModel Services

[![Build Status](https://github.com/wmde/WikibaseDataModelServices/actions/workflows/lint-and-test.yaml/badge.svg?branch=master)](https://github.com/wmde/WikibaseDataModelServices/actions/workflows/lint-and-test.yaml)
[![Download count](https://poser.pugx.org/wikibase/data-model-services/d/total.png)](https://packagist.org/packages/wikibase/data-model-services)
[![License](https://poser.pugx.org/wikibase/data-model-services/license.svg)](https://packagist.org/packages/wikibase/data-model-services)

[![Latest Stable Version](https://poser.pugx.org/wikibase/data-model-services/version.png)](https://packagist.org/packages/wikibase/data-model-services)
[![Latest Unstable Version](https://poser.pugx.org/wikibase/data-model-services/v/unstable.svg)](//packagist.org/packages/wikibase/data-model-services)

**Wikibase DataModel Services** is a collection of services around
[Wikibase DataModel](https://github.com/wmde/WikibaseDataModel).
It is part of the [Wikibase software](http://wikiba.se/).


Recent changes can be found in the [release notes](RELEASE-NOTES.md).

Note that this repository is a mirror of part of the upstream [Wikibase project](https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/Wikibase/+/refs/heads/master/lib/packages/wikibase/data-model-services/) on Gerrit.
Contributions should be made to the directories there using MediaWiki's [Gerrit process](https://www.mediawiki.org/wiki/Gerrit).

## Library contents

In order to be allowed in this package, code needs to:

* Be using Wikibase DataModel and deal with the core Wikibase domain
* Not belong to a more specific component
* Not introduce heavy dependencies to this component
* Not be presentation code

## Installation

You can use [Composer](http://getcomposer.org/) to download and install
this package as well as its dependencies. Alternatively you can simply clone
the git repository and take care of loading yourself.

### Composer

To add this package as a local, per-project dependency to your project, simply add a
dependency on `wikibase/data-model-services` to your project's `composer.json` file.
Here is a minimal example of a `composer.json` file that just defines a dependency on
Wikibase DataModel Services 3.x:

```js
{
    "require": {
        "wikibase/data-model-services": "~5.0"
    }
}
```

### Manual

Get the Wikibase DataModel Services code, either via git, or some other means. Also get all dependencies.
You can find a list of the dependencies in the "require" section of the composer.json file.
The "autoload" section of this file specifies how to load the resources provide by this library.

## Tests

This library comes with a set up PHPUnit tests that cover all non-trivial code. Additionally, code
style checks by PHPCS and PHPMD are supported. The configuration for all 3 these tools can be found
in the root directory. You can use the tools in their standard manner, though can run all checks
required by our CI by executing `composer ci`. To just run tests use `composer test`, and to just
run style checks use `composer cs`.

## Contributing

This repository is a mirror of part of the upstream [Wikibase project](https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/Wikibase/+/refs/heads/master/lib/packages/wikibase/data-model-services/) on gerrit.
Contributions should be made to the directories there using MediaWiki's [Gerrit process](https://www.mediawiki.org/wiki/Gerrit).

## Links

* [Wikibase DataModel Services on Packagist](https://packagist.org/packages/wikibase/data-model-services)

## See also

* [Wikibase DataModel](https://github.com/wmde/WikibaseDataModel)
* [Wikibase DataModel Serialization](https://github.com/wmde/WikibaseDataModelSerialization)
* [Wikibase Internal Serialization](https://github.com/wmde/WikibaseInternalSerialization)

