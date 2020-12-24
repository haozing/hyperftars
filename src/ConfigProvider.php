<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperftars\Tars;

use Hyperftars\Tars\Listener\MonitorListener;
use Hyperftars\Tars\Listener\InitMonitorListener;
use Hyperftars\Tars\Listener\RegisterProtocolListener;
use Hyperftars\Tars\Listener\RegisterParseListener;
use Hyperftars\Tars\Listener\RegularReportListener;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
            ],
            'commands' => [
            ],
            'listeners' => [
                MonitorListener::class,
                InitMonitorListener::class,
                RegularReportListener::class,
                RegisterProtocolListener::class,
                RegisterParseListener::class,
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'publish' => [
                [
                    'id' => 'index',
                    'description' => 'The tars for index.',
                    'source' => __DIR__ . '/../publish/index.php',
                    'destination' => BASE_PATH . '/index.php',
                ],
                [
                    'id' => 'tars',
                    'description' => 'The tars for tars.',
                    'source' => __DIR__ . '/../publish/tars.php',
                    'destination' => BASE_PATH . '/config/autoload/tars.php',
                ],
            ],
        ];
    }
}
