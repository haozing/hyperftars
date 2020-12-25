#!/usr/bin/env php
<?php

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('memory_limit', '1G');

error_reporting(E_ALL);

! defined('BASE_PATH') && define('BASE_PATH', dirname(__FILE__));

! defined('SWOOLE_HOOK_FLAGS') && define('SWOOLE_HOOK_FLAGS', SWOOLE_HOOK_ALL);

require BASE_PATH . '/vendor/autoload.php';

use \Hyperftars\Tars\Commands\Stop;
//php tarsCmd.php  conf restart
$config_path = $argv[1];
$pos = strpos($config_path, '--config=');
$config_path = substr($config_path, $pos + 9);
! defined('TARS_CONFIG_PATH') && define('TARS_CONFIG_PATH',$config_path);
if (!$config_path){
    echo "Execute the command without adding the configuration file address parameter";
}
if (isset($argv[2])){
    $cmd = strtolower($argv[2]);

    if ($cmd === 'stop') {
        $class = new Stop($config_path);
        $class->execute();
        return;
    }elseif ($cmd === 'restart'){
        $class = new Stop($config_path);
        $class->execute();
    }
}
//hyperf 启动命令
$phpfile = $_SERVER['argv'][0];
$_SERVER['argv'] = [];
$_SERVER['argv'][0] = $phpfile;
$_SERVER['argv'][1] = "start";

// Self-called anonymous function that creates its own scope and keep the global namespace clean.
(function () {
    Hyperf\Di\ClassLoader::init();
    /** @var \Psr\Container\ContainerInterface $container */
    $container = require BASE_PATH . '/config/container.php';

    $application = $container->get(\Hyperf\Contract\ApplicationInterface::class);
    $application->run();
})();