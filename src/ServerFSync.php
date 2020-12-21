<?php


namespace Hyperftars\Tars;


use Tars\report\ServerInfo;

class ServerFSync
{
    private $_ip;
    private $_port;
    private $_objName;

    public function __construct($ip = '', $port = '', $objName = '')
    {
        $this->_ip = $ip;
        $this->_port = $port;

        $this->_objName = $objName;
    }

    public function keepAlive(ServerInfo $serverInfo)
    {
        try {
            $structBuffer = \TUPAPI::putStruct('serverInfo', $serverInfo);

            $encodeBufs['serverInfo'] = $structBuffer;

            $iVersion = 3;
            $iRequestId = 1;
            $servantName = $this->_objName;
            $funcName = __FUNCTION__;
            $cPacketType = 0;
            $iMessageType = 0;
            $tarsTimeout = 2000;
            $contexts = [];
            $statuses = [];

            $tarsRequestBuf = \TUPAPI::encode($iVersion, $iRequestId, $servantName,
                $funcName, $cPacketType, $iMessageType, $tarsTimeout, $contexts, $statuses, $encodeBufs);

            $client = new \swoole_client(SWOOLE_SOCK_TCP);

            $timeout = 2;
            if (!$client->connect($this->_ip, $this->_port, $timeout)) {
                return 0;
            }

            if (!$client->send($tarsRequestBuf)) {
                $client->close();
                return 0;
            }

            return 0;
        } catch (\Exception $e) {
            return -1;
        }
    }
}