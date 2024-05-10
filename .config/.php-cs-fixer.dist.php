<?php
/**
 * php-cs-fixer configuration file
 * @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/config.rst
 */

use PhpCsFixer\Finder;
use PhpCsFixer\Config;

/** @psalm-suppress UndefinedClass */
$finder = (new Finder())
    ->in(['src', 'tests'])
    ->exclude(['.history', '.config'])
;

/** @psalm-suppress UndefinedClass */
return (new Config())
    // Other rulesets can be found here https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/ruleSets/index.rst
    // List of built-in rules https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/rules/index.rst
    ->setRules([
        '@PSR12' => true,
        'phpdoc_annotation_without_dot' => false,

        // this rule is important to avoid messing with psalm suppress errors declarations
        'phpdoc_to_comment' => [
            'ignored_tags' => ['psalm-suppress'],
        ],
    ])
    ->setFinder($finder)
;
