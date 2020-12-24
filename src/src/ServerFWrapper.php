<?php


namespace Hyperftars\Tars;


use Tars\report\ServerFSync;
use Tars\report\ServerInfo;

class ServerFWrapper
{
    private $_serverF;
    public function __construct(
        $host,
        $port,
        $objName
    ) {
        $this->_serverF = new ServerFSync($host, $port, $objName);
    }

    public function keepAlive(ServerInfo $serverInfo) {
        $this->_serverF->keepAlive($serverInfo);
    }

}