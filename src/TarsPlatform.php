<?php


namespace Hyperftars\Tars;

use Hyperf\Contract\ContainerInterface;
use Tars\report\ServerInfo;
use Hyperf\Contract\StdoutLoggerInterface;

class TarsPlatform
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

    // 初始化的上报
    public function keepaliveInit()
    {
        $tarsConfig = $this->container->get(InitConfig::class)->getTarsConf();
        $tarsServerConf = $tarsConfig['tars']['application']['server'];
        $master_pid = getmypid();
        // 加载tars需要的文件 - 最好是通过autoload来加载
        // 初始化的上报
        $serverInfo = new ServerInfo();
        $serverInfo->application = $tarsServerConf['app'];
        $serverInfo->serverName = $tarsServerConf['server'];
        $serverInfo->pid = $master_pid;


        $serverF = $this->container->get(InitMonitorServer::class)->getServerF();
        try {
            foreach ($tarsServerConf['adapters'] as $adapterObj) {
                $serverInfo->adapter = $adapterObj['adapterName'];
                $serverF->keepAlive($serverInfo);
            }

            $serverInfo->adapter = 'AdminAdapter';
            $serverF->keepAlive($serverInfo);
        } catch (\Exception $e) {
            $this->logger->error((string)$e);
        }

    }

    public function keepaliveReport()
    {
        $tarsConfig = $this->container->get(InitConfig::class)->getTarsConf();
        $tarsServerConf = $tarsConfig['tars']['application']['server'];
        $master_pid = getmypid();
        // 加载tars需要的文件 - 最好是通过autoload来加载
        // 初始化的上报
        $application = $tarsServerConf['app'];
        $serverName = $tarsServerConf['server'];
        $adapters = array_column($tarsServerConf['adapters'], 'adapterName');
        $masterPid = $master_pid;
        // 进行一次上报
        $serverInfo = new ServerInfo();
        $serverInfo->application = $application;
        $serverInfo->serverName = $serverName;
        $serverInfo->pid = $masterPid;

        try {
            $serverF = $this->container->get(InitMonitorServer::class)->getServerF();
            foreach ($adapters as $adapter) {
                $serverInfo->adapter = $adapter;
                $serverF->keepAlive($serverInfo);
            }

            $adminServerInfo = new ServerInfo();
            $adminServerInfo->adapter = 'AdminAdapter';
            $adminServerInfo->application = $application;
            $adminServerInfo->serverName = $serverName;
            $adminServerInfo->pid = $masterPid;
            $serverF->keepAlive($adminServerInfo);
        } catch (\Exception $e) {
            $this->logger->error((string)$e);
        }
    }

}