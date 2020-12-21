<?php


namespace Hyperftars\Tars;
use Hyperf\Contract\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;

class RegularReport
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
    public function task()
    {
        $getTarsConf = $this->container->get(InitConfig::class)->getTarsConf();
        $serverName = $getTarsConf['server'];
        $application = $getTarsConf['app'];
        $this->logger->info("定时上报：".$application.$serverName);
        \Swoole\Timer::tick(10000, function() use ($serverName, $application) {
            $this->container->get(TarsPlatform::class)->keepaliveReport();
        });
    }
}