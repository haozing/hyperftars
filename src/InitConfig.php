<?php


namespace Hyperftars\Tars;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Server\Server;
use Hyperf\Server\SwooleEvent;
use Hyperftars\Tars\Exception\TarsNotFoundException;
use Hyperf\Contract\ContainerInterface;
use Tars\Utils;

class InitConfig
{
    protected $conf = [];
    /**
     * @var \Hyperf\Contract\ContainerInterface
     */
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if (!TARS_CONFIG_PATH){
            //方便测试使用
            $tars_conf = BASE_PATH . '/conf/' . env('APP_NAME') . '.config.conf';
        }else{
            $tars_conf = TARS_CONFIG_PATH;
        }

        if (is_file($tars_conf)) {
            $this->conf = Utils::parseFile($tars_conf);
        } else {
            $this->conf = [];
        }
    }

    public function getTarsConf()
    {
        return $this->conf;
    }

    public function setTarsServer()
    {
        $config = $this->container->get(ConfigInterface::class);
        $server = $config->get("server.servers");

        if (empty($this->conf)){
            return;
        }
        //TODO:: 判断协议、服务器配置等
        foreach ($this->conf['tars']['application']["server"]['adapters'] as $obj){

            $server[] =         [
                'name' => $obj['objName'],
                'type' => Server::SERVER_BASE,
                'host' => '0.0.0.0',
                'port' => (int)$obj['listen']['iPort'],
                'sock_type' => SWOOLE_SOCK_TCP,
                'callbacks' => [
                    SwooleEvent::ON_RECEIVE => [\Hyperftars\Tars\TcpServer::class, 'onReceive'],
                ],
                'settings' => [
                    'open_length_check' => true,
                    'package_length_type' => 'N',
                    'package_length_offset' => 0,
                    'package_body_offset' => 0,
                    'package_max_length' => 1024 * 1024 * 2,
                ],
            ];
        }
        $config->set("server.servers",$server);
    }
}