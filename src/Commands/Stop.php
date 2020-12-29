<?php
/**
 * Created by PhpStorm.
 * User: dingpanpan
 * Date: 2017/12/2
 * Time: 16:02.
 */

namespace Hyperftars\Tars\Commands;

class Stop extends CommandBase
{
    public function __construct($configPath)
    {
        parent::__construct($configPath);
    }

    public function execute()
    {
        $tarsConfig = $this->tarsConfig;

        //判断master进程是否存在
        if (empty($tarsConfig['tars']['application']['server']['app'])
            || empty($tarsConfig['tars']['application']['server']['server'])) {
            echo "AppName or ServerName empty! Please check config!" . PHP_EOL;
            exit;
        }

        $name = $tarsConfig['tars']['application']['server']['app'] .
            '.' . $tarsConfig['tars']['application']['server']['server'];

        $serverPath = BASE_PATH . '/config/autoload/server.php';
        if (!file_exists($serverPath) || !is_readable($serverPath)) {
            echo "No configuration file found：config/autoload/server.php";
            return;
        }
        $config = require $serverPath;
        $configServer = is_array($config) ? $config : [];

        if (!isset($configServer['settings']['pid_file'])){
            echo "No configuration file found：config/autoload/server.php,Option pid_file is not set";
            return;
        }
        if(!file_exists($configServer['settings']['pid_file'])){
            echo "pid_file file does not exist";
            return;
        }
        $masterPid = (int) \file_get_contents($configServer['settings']['pid_file']);

        if (!$masterPid){
            echo "Files not found：server.php pid_file，Please check！";
            return;
        }
        $CMdret = $this->getProcessName($masterPid);
        $name =$CMdret['ProcessName'];
        //todo kill -TERM 8771 命令可以杀死所有的进程
        $cmd = "kill -TERM {$masterPid}";
        exec($cmd, $output, $r);

        //查找其他的，再杀一遍。

        $ret = $this->getProcess($name);
        if ($ret['exist'] === false) {
            echo "{$name} stop  \033[34;40m [FAIL] \033[0m process not exists"
                . PHP_EOL;

            return;
        }

        $pidList = implode(' ', $ret['pidList']);

        //todo kill -TERM 8771 命令可以杀死所有的进程
        $cmd = "kill -9 {$pidList}";
        exec($cmd, $output, $r);

        if ($r === false) { // kill失败时
            echo "{$name} stop  \033[34;40m [FAIL] \033[0m posix exec fail"
                . PHP_EOL;
            exit;
        }

        echo "{$name} stop  \033[32;40m [SUCCESS] \033[0m" . PHP_EOL;
    }
}
