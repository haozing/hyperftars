<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperftars\Tars\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BootApplication;
use Hyperftars\Tars\InitMonitorServer;
use Tars\report\ServerFWrapper;
use Tars\Utils;
use Hyperftars\Tars\InitConfig;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;
class MonitorListener implements ListenerInterface
{
    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;
    /**
     * @var ContainerInterface
     */
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->logger = $container->get(StdoutLoggerInterface::class);
    }
    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            BootApplication::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        //初始化配置
        $conf = $this->container->get(InitConfig::class)->getTarsConf();
        $this->logger->info("初始化配置");
        $result = Utils::parseNodeInfo($conf['tars']['application']['server']['node']);
        $objName = $result['objName'];
        $host = $result['host'];
        $port = $result['port'];
        $serverF = new ServerFWrapper($host, $port, $objName);
        // 初始化服务
        $this->container->get(InitMonitorServer::class)->setServerF($serverF);
        $this->logger->info("初始化服务");
    }
}
