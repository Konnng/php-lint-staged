<?php
/**
 * Post install script, after installing all composer packages.
 */

/*
 * URL for pslam bin file
 */
define('PSALM_INSTALL_URL', 'https://github.com/vimeo/psalm/releases/latest/download/psalm.phar');
define('ROOT_DIR', realpath(__DIR__.DIRECTORY_SEPARATOR.'..'));

/**
 * Installs vimeo/psalm stand alone bin.
 */
function Install_psalm(): void
{
    $file = basename(PSALM_INSTALL_URL);

    echo 'Installing psalm code analysis tool...'.PHP_EOL;

    $ch = curl_init(PSALM_INSTALL_URL);
    if (false === $ch) {
        echo 'Error: there was an error preparing to download psalm. Aborting.'.PHP_EOL;
        exit(1);
    }

    $fp = fopen($file, 'w+');
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    if (false === curl_exec($ch)) {
        echo 'Error: error downloading file: '.curl_error($ch).PHP_EOL;
        exit(1);
    }
    curl_close($ch);
    fclose($fp);

    if (!is_file($file)) {
        echo 'Error: psalm executable not found. Aborting install'.PHP_EOL;
        exit(1);
    } elseif (!chmod($file, 0755)) {
        // Set executable permissions
        echo 'Error: error setting correct permissions.'.PHP_EOL;
        exit(1);
    }

    echo 'Install psalm complete'.PHP_EOL;
}

/**
 * Setup git hooks.
 */
function Setup_hooks(): void
{
    $hooksDir = ROOT_DIR.DIRECTORY_SEPARATOR.'.git'.DIRECTORY_SEPARATOR.'hooks';
    $hooks = [
        'pre-commit',
    ];

    echo 'Installing git hooks...'.PHP_EOL;
    if (!is_dir($hooksDir) && !@mkdir($hooksDir)) {
        echo 'Error: there was an error creating git hooks directory'.PHP_EOL;
        exit(1);
    }

    foreach ($hooks as $hook) {
        $source = ROOT_DIR.DIRECTORY_SEPARATOR.'.config'.DIRECTORY_SEPARATOR.'.hooks'.DIRECTORY_SEPARATOR.$hook;
        $dest = $hooksDir.DIRECTORY_SEPARATOR.$hook;

        @unlink($dest);
        if (!@copy($source, $dest)) {
            echo "Error: there was an error installing {$hook} hook".PHP_EOL;
            exit(1);
        }

        if (!chmod($dest, 0755)) {
            echo "Error: there was an error installing {$hook} hook".PHP_EOL;
            exit(1);
        }
    }

    echo 'Install hooks complete'.PHP_EOL;
}

/**
 * Copy relevant configuration files.
 */
function Copy_config(): void
{
    echo 'Copying necessary configuration...'.PHP_EOL;

    if (!@copy(__DIR__.DIRECTORY_SEPARATOR.'psalm.xml', ROOT_DIR.DIRECTORY_SEPARATOR.'psalm.xml')) {
        echo 'Error: error creating psalm configuration. Aborting.'.PHP_EOL;
        exit(1);
    }

    if (!@copy(__DIR__.DIRECTORY_SEPARATOR.'.php-cs-fixer.dist.php', ROOT_DIR.DIRECTORY_SEPARATOR.'.php-cs-fixer.dist.php')) {
        echo 'Error: error creating php-cs-fixer configuration. Aborting.'.PHP_EOL;
        exit(1);
    }

    echo 'Config complete.'.PHP_EOL;
}

/**
 * Post install setup.
 *
 * @return void
 */
function setup()
{
    echo 'Running post install script...'.str_repeat(PHP_EOL, 2);

    Install_psalm();
    echo PHP_EOL;
    Setup_hooks();
    Copy_config();
    echo PHP_EOL;

    echo PHP_EOL;
    echo 'Post install complete'.PHP_EOL;
}

setup();
