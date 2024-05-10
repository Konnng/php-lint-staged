# php-lint-staged

Lint your staged files using `composer` and a `pre-commit` hook. This strategy mimics (sort of) `npm` `husky` and `lint-staged` packages.

This is a little experiment to test availability of using the same approach we have when developing JS using `node` and `npm`.

> Alternatively, you can make use of the regular approach using such `node` packages [[reference]](https://sebastiandedeyne.com/running-php-cs-fixer-on-every-commit-with-husky-and-lint-staged).

## Requirements

* PHP application written using [composer](https://getcomposer.org) to manage packages.
* PHP version >= `8.1`
* For now, it only runs with `composer` installed globally and on `*nix` environments (MacOS, Linux and Windows WSL).

## Installation

### For a new project

* Clone this repo or [download](https://github.com/Konnng/php-lint-staged/archive/refs/heads/main.zip) the repository files
* Edit `composer.json` file to create your project.

> Keep in mind the composer file contains dummy information, such as `name`, `description` and `autoload` entries.

Composer configuratrion reference: https://getcomposer.org/doc/01-basic-usage.md

### For a existing project

Case you have an ongoing project that you would like to implement this strategy, you need to perform the following steps

#### Step 1: make sure to install `webmozart/glob` and `nikic/iter` as dev dependencies.

```sh
composer install --dev nikic/iter webmozart/glob
```

> Note: These packages are necessary in order to run the php script that will lint your files when performing a commit.

#### Step 2: copy directory `./config` to your root project

> Note: this is the core of this strategy, this folder contains the necessary configuration template and scripts used to install requirements and run the linter when performing a commit.

#### Step 3: copy both `setup`, `lint` and `pre-update-cmd` script entries to your composer configuration.

```json
{
  //...
  "scripts": {
    //...
    "setup": "test ! -f composer.lock && php .config/post-install.php || echo 'Skipping setup...'",
    "lint": ["php php-cs-fixer.phar fix --config=.php-cs-fixer.dist.php", "php psalm.phar"],
    "pre-update-cmd": "composer run-script setup"
  }
}
```

> Note: These scripts are necessary in order to install all the necessary tools in order to lint your files.

#### Step 4: copy the `extra` entry to your composer configuration.

```json
{
  //...
  "extra": {
      "lint-staged": {
          "**/*.php": [
              "composer run-script lint"
          ]
      }
  }
}
```

> Note: This entry is necessary in order to tell the lint script which files and what commands it needs to run when performing a commit.
> The logic is pretty similar to `lint-staged` npm package. [[reference]](https://www.npmjs.com/package/lint-staged#configuration)


#### Step 5: run the install

It wil be necessary to remove your `compose.lock` file to force install the necessary dependencies and extra configuration.

```sh
> rm -f composer.lock && composer install
```

Removing the lock file will trigger the `setup` script and will run the post installation setup to copy relevant configuration and download the necessary utilities.

> Note: if everything goes whell, you should notice `psalm.phar` and `php-cs-fixer.phar` utilities and their respective configuration files.

## Basic Usage


Make sure to check `.php-cs-fixer.dist.php` configuration to add/remove directories you might want to check, alongside with `psalm.xml`.

You can either run manually `composer run-script lint` to check and fix your entire directory or when performing a commit containing php files.

## Advanced usage

Case you are familiar with this strategy, you can change `lint-staged` entry on your configuration and add your conditions and what to execute. Just make sure to:

* Make sure to add the `extra.lint-staged` entry on your configuration and to create your own rules to execute.
* You can skip adding `setup` and `pre-update-cmd` script entries.
* Install the git `pre-commit` hook and make sure to call `.config/lint-staged.php` script.
* `webmozart/glob` and `nikic/iter` packages are still required in order to run the lint-staged script.

> If you decided to use `psalm` and `php-cs-fixer`, make sure to add/edit `.php-cs-fixer.dist.php` and `psalm.xml` files to check the rules and add/exclude directories.

### Skip certain files when performing a commit

We all have situations that we are working with "special" files/directories that would necessary to skip the lint validation.

In order to address this situation, the lint script will check if there is a `.lingstaged-ignore` `(TODO: chose a better name)` file and use it to check for these particular entries on the list of staged files.

Example:

```
.history
vendor
```

This entries will make sure to skip both `.history` and `vendor` directories.


> Note: you won't be able to use patterns like a regular `.gitignore` file, such as negative lookup (`!`), just regular Ant-like globbing patterns (check **Limitations**).

## Limitations

Since this is a experiment, there is a considerable set of limitations to use this strategy, such as:

* Your local PHP setup: a configuration or a extension necessary could be disabled and  your are not aware of it.
* The `lint-staged` entires: it needs to follow Ant-like globbing patterns, otherwise it may fail or produce wrong results. [[reference]](https://github.com/webmozarts/glob?tab=readme-ov-file#webmozart-glob)
* The strategy used to ignore files using `lingstaged-ignore` is pretty simple. I didn't find any useful PHP package to act as `git` does with `.gitignore`, neither to write my on implmementation of it. That is not the goal here.
* Environment: as mentioned, it works **only** on *nix environments (WSL included).
* Utilities: Both `psalm` and `php-cs-fixer` as their own limitations and shenanigans. Configure it carefully.
