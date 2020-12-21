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
            //获取当前存活的worker数目
            $processName = $application . '.' . $serverName;
            $cmd = "ps wwaux | grep '" . $processName . "' | grep 'event worker process' | grep -v grep  | awk '{ print $2}'";
            exec($cmd, $ret);
            $workerNum = count($ret);

            if ($workerNum >= 1) {
                $this->container->get(TarsPlatform::class)->keepaliveReport();
            } //worker全挂，不上报存活 等tars重启
            else {
                $this->logger->error(__METHOD__ . " All workers are not alive any more.");
            }
        });
    }
}