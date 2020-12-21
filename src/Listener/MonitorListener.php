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
use Hyperf\Framework\Event\OnManagerStart;
use Hyperftars\Tars\TarsPlatform;
use Hyperftars\Tars\InitConfig;
use Hyperf\Contract\ContainerInterface;

class MonitorListener implements ListenerInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
    /**
     * @return string[] returns the events that you want to listen
     */
    public function listen(): array
    {
        return [
            OnManagerStart::class,
        ];
    }

    /**
     * Handle the Event when the event is triggered, all listeners will
     * complete before the event is returned to the EventDispatcher.
     */
    public function process(object $event)
    {
        //拉取配置文件
        $this->container->get(InitConfig::class)->getTarsConf();

        // 初始化的一次上报
        $this->container->get(TarsPlatform::class)->keepaliveInit();

    }
}
