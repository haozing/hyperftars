<?php


namespace Hyperftars\Tars;


use Tars\report\ServerFWrapper;

class InitMonitorServer
{
    /**
     * 定时上报对象
     * @var ServerFWrapper
     */
    public $serverF;

    /**
     * @return ServerFWrapper
     */
    public function getServerF()
    {
        return $this->serverF;
    }

    /**
     * @param ServerFWrapper
     */
    public function setServerF(ServerFWrapper $serverFWrapper)
    {
        $this->serverF = $serverFWrapper;
    }
}