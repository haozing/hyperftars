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

        //判断进程名称和本地是否一致
        $app_name = env('APP_NAME');
        if ($name !== env('APP_NAME')){
            echo "{$name}--{$app_name} 无法结束进程。请对比设置.env下APP_NAME和TARS服务名一致。"
                . PHP_EOL;

            return;
        }
        $ret = $this->getProcess($name);
        if ($ret['exist'] === false) {
            echo "{$name} stop  \033[34;40m [FAIL] \033[0m process not exists"
                . PHP_EOL;

            return;
        }

        $pidList = implode(' ', $ret['pidList']);
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
