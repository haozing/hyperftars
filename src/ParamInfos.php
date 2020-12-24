<?php


namespace Hyperftars\Tars;


use Hyperf\Contract\ConfigInterface;
use Hyperf\Rpc\ProtocolManager;
use Psr\Container\ContainerInterface;

class ParamInfos
{
    private $paramInfos;
    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var \Hyperf\Contract\ConfigInterface
     */
    private $config;
    /**
     * @var ProtocolManager
     */
    protected $protocolManager;

    public function __construct(ContainerInterface $container,ConfigInterface $config,ProtocolManager $protocolManager)
    {
        $this->config = $config;
        $this->protocolManager = $protocolManager;
        $this->container = $container;
    }
    public function register()
    {
        $tarss = $this->config->get('tars');

        foreach ($tarss as $tarsname => $tarsinfo){
            switch ($tarsinfo['serverType']) {
                case 'tcp' :
                case 'udp' :
                case 'grpc':
                    $interface = new \ReflectionClass($tarsinfo['home-api']);
                    $methods = $interface->getMethods();

                    foreach ($methods as $method) {
                        $docBlock = $method->getDocComment();
                        // 对于注释也应该有自己的定义和解析的方式
                        $protocol = $this->protocolManager->getProtocol($tarsinfo["protocolName"])["tars-parse"];
                        var_dump($protocol);
                        $protocol = $this->container->get($protocol);
                        $this->paramInfos[$tarsname][$method->name] = $protocol->parseAnnotation($docBlock);
                        var_dump($this->paramInfos[$tarsname][$method->name]);
                    }
                    break;
                case 'websocket' :

                    break;
                default : //http
                    break;
            }
        }
    }

    public function getParamInfo(string $sFuncName)
    {
        return $this->paramInfos[$sFuncName];
    }

}