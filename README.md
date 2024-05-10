# php-lint-staged

Lint your staged files using `composer` and a `pre-commit` hook. This strategy mimics (sort of) `npm` `husky` and `lint-staged` packages.

This is a little experiment to test availability of using the same approach we have when developing JS using `node` and `npm`.

> Alternatively, you can make use of the regular approach using such `node` packages [[reference]](https://sebastiandedeyne.com/running-php-cs-fixer-on-every-commit-with-husky-and-lint-staged).

## Requirements

* PHP application written using composer to manage packages.
* For now, it only runs with `composer` installed globally and on `*nix` systems (MacOS, Linux and Windows WSL).
