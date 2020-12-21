<?php


namespace Hyperftars\Tars;


use Hyperftars\Tars\Exception\TarsNotFoundException;
use Tars\Utils;

class InitConfig
{
    protected $conf = [];
    public function __construct()
    {
        $tars_conf = dirname(BASE_PATH, 2) . '/conf/' . env('PNAME') . '.config.conf';

        var_dump($tars_conf);
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
}