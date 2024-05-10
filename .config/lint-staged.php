<?php
/**
 * PHP Script to execute a callback for staged files.
 */

require 'vendor/autoload.php';

use Webmozart\Glob\Glob;

define('CONFIG_KEY', 'extra.lint-staged');
define('IGNORE_FILE', '.lint-stagedignore');

/**
 * Retrieves lint-staged configuration from composer.json.
 *
 * @return array Valid lint-staged configuration.
 */
function Get_config(): array
{
    $output = null;
    $resultCode = 0;
    $rawConfig = exec('composer config --json '.CONFIG_KEY.' 2>&1', $output, $resultCode);
    if (0 !== $resultCode || false === $rawConfig) {
        echo 'Error: no valid lint-stage configuration found. Make "'.CONFIG_KEY.'" is present on composer.json file and it is valid.'.PHP_EOL;
        exit(1);
    }

    $config = json_decode($rawConfig, true);
    if (!$config || !is_array($config)) {
        echo 'Warning: no valid lint-stage configuration found. Skipping...'.PHP_EOL;
        exit;
    }

    $filteredConfig = array_filter($config, fn ($cmd) => is_array($cmd) && (bool) count($cmd));
    $filteredConfig = array_map(fn ($cmd) => array_map('trim', $cmd), $filteredConfig);
    $filteredConfig = array_filter($filteredConfig, fn ($cmd) => (bool) count($cmd));

    if (!count($filteredConfig)) {
        echo 'Warning: no valid lint-stage configuration found. Skipping...'.PHP_EOL;
        exit;
    }

    return $filteredConfig;
}

/**
 * Retrieves the list of files to ignore from .lint-stagedignore.
 *
 * @return array List of ignore patterns.
 */
function Get_ignoreList(): array
{
    $contents = is_file('.lint-stagedignore') ? trim(file_get_contents(IGNORE_FILE)) : null;
    if (null === $contents) {
        return [];
    }

    $rules = array_filter(
        array_map('trim', explode(PHP_EOL, $contents)),
        fn ($item) => !empty($item) && !str_starts_with($item, '#')
    );

    return $rules;
}

/**
 * Checks if a file should be ignored based on ignore patterns.
 *
 * @param string $file           File to check.
 * @param array  $ignorePatterns List of ignore patterns.
 *
 * @return bool Whether the file should be ignored.
 */
function Should_ignore(string $file, array $ignorePatterns): bool
{
    foreach ($ignorePatterns as $pattern) {
        $paths = Glob::glob(__DIR__.DIRECTORY_SEPARATOR.$pattern);
        $result = iter\search(function ($path) use ($file) {
            if (is_dir($path)) {
                return str_starts_with(
                    dirname($file),
                    rtrim(
                        implode('', (array) str_replace(__DIR__.DIRECTORY_SEPARATOR, '', $path)),
                        DIRECTORY_SEPARATOR
                    )
                );
            }

            return $file === $path;
        }, $paths);

        if (null !== $result) {
            return true;
        }
    }

    return false;
}

/**
 * Executes a shell command.
 *
 * @param string $command Command to execute.
 *
 * @return bool Whether the command execution was successful.
 */
function Exec_command(string $command): bool
{
    $exitCode = 0;

    ob_start();
    passthru($command, $exitCode);
    $output = ob_get_contents();
    ob_end_clean();

    echo $output.PHP_EOL;

    return 0 === $exitCode;
}

/**
 * Runs the lint-staged process.
 *
 * @param array $arguments List of file arguments.
 */
function run(array $arguments): void
{
    $config = Get_config();
    $ignoreList = Get_ignoreList();

    $files = array_filter($arguments, 'is_file');
    if (!count($files)) {
        echo 'Error: no files to lint'.PHP_EOL;
        exit(1);
    }

    foreach ($config as $pattern => $commands) {
        $foundFiles = array_filter($files, fn ($file) => Glob::match(__DIR__.DIRECTORY_SEPARATOR.$file, __DIR__.DIRECTORY_SEPARATOR.$pattern));
        $filteredFiles = array_filter($foundFiles, fn ($file) => !Should_ignore($file, $ignoreList));
        $relativeFiles = array_map(fn ($f) => str_replace(__DIR__.DIRECTORY_SEPARATOR, '', $f), $filteredFiles);

        $lint = array_filter($files, fn ($file) => in_array($file, $relativeFiles));

        if (!count($lint)) {
            continue;
        }

        foreach ($commands as $command) {
            $cmd = escapeshellcmd($command.' '.implode(' ', array_map('escapeshellarg', $lint))).' >&1';
            if (!Exec_command($cmd)) {
                echo 'Error: lint aborted'.PHP_EOL;
                exit(1);
            }
        }
    }
}

run(array_slice($argv, 1));
