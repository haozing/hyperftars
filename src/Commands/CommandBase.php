<?php
/**
 * Created by PhpStorm.
 * User: dingpanpan
 * Date: 2017/12/2
 * Time: 16:20.
 */

namespace Hyperftars\Tars\Commands;

use Tars\Utils;

class CommandBase
{
    public $configPath;
    public $tarsConfig;

    public function __construct($configPath)
    {
        $this->configPath = $configPath;

        $tarsConfig = Utils::parseFile($configPath);
        $this->tarsConfig = $tarsConfig;
    }

    /**
     * @param $processName
     * @return array
     */
    public function getProcess($processName)
    {
        $cmd = "ps aux | grep '" . $processName . "' | grep -v grep | grep -v php | awk '{ print $2}'";
        exec($cmd, $ret);

        if (empty($ret)) {
            return [
                'exist' => false,
            ];
        } else {
            return [
                'exist' => true,
                'pidList' => $ret,
            ];
        }
    }
    /**
     * @param $pid
     * @return array
     */
    public function getProcessName($pid)
    {
        $cmd = "ps aux | grep '" . $pid . "' | grep -v grep | grep -v php | awk '{ print $11}'";
        exec($cmd, $ret);

        if (empty($ret)) {
            return [
                'exist' => false,
            ];
        } else {
            $ProcessName = substr($ret[0], 0, -6);
            return [
                'exist' => true,
                'ProcessName' => $ProcessName,
            ];
        }
    }
}
