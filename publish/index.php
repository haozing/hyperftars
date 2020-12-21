<?php
$config_path = $argv[1];
$arg_cmd = $argv[2];
$arg_cmd = $arg_cmd ? "stop":"start";

$hy_base = dirname(__FILE__);
$hy_bin = $hy_base.'/bin/hyperf.php ';


$pos = strpos($config_path, '--config=');
$config_path = substr($config_path, $pos + 9);
$tarsConfig = parseFile($config_path);
$tarsServerConfigphp = $tarsConfig['tars']['application']['server']['php'];
$cmd = $tarsServerConfigphp ." " . $hy_bin . $arg_cmd;
$runtime = $hy_base."/runtime";
$stopcmd = "cat ".$runtime."/hyperf.pid | awk '{print $1}' | xargs kill -9 && rm -rf ".$runtime."/hyperf.pid && rm -rf ".$runtime."/container";

exec($stopcmd, $output, $r);
if ($arg_cmd == "start") {
    exec($cmd, $output, $r);
}



//ps -ef | grep "${projectName}" | grep -v "grep"| awk '{print $2}'| xargs kill
//ps -ef | grep skeleton | awk '{print $2}'|xargs kill -9
function parseFile($configPath)
{
    $text = file_get_contents($configPath);

    if (empty($text)) {
        echo ' start FAIL, config file missing';
        exit;
    }

    $conf = parseText($text);

    return $conf;
}
function parseText($text)
{
    $tarsAdapters = [];
    $tarsServer = [];
    $tarsClient = [];
    $objAdapter = [];
    $application = [];
    $lines = explode("\n", $text);

    $status = 0; //标识在application内
    foreach ($lines as $line) {
        $line = trim($line, " \r\0\x0B\t\n");
        if (empty($line)) {
            continue;
        }
        if ($line[0] == '#') {
            continue;
        }

        switch ($status) {
            case 0:{
                if (strstr($line, '<server>')) {
                    $status = 1;
                } elseif (strstr($line, '<client>')) {
                    $status = 3;
                } elseif (strstr($line, '=')) {
                    $pos = strpos($line, '=');
                    $name = substr($line, 0, $pos);
                    $value = substr($line, $pos + 1, strlen($line) - $pos);
                    $application[$name] = $value;
                }
                break;
            }
            // 在server内
            case 1:{
                if (strstr($line, '=')) {
                    $pos = strpos($line, '=');
                    $name = substr($line, 0, $pos);
                    $value = substr($line, $pos + 1, strlen($line) - $pos);
                    $tarsServer[$name] = $value;
                }
                // 还要兼容多个adapter的情况(终止的时候兼容即可)
                elseif (strstr($line, 'Adapter>')) {
                    $status = 2;
                    $adapterName = substr($line, 0, strlen($line) - 1);
                    $adapterName = substr($adapterName, 1, strlen($line) - 1);
                    $objAdapter['adapterName'] = $adapterName;
                } elseif (strstr($line, '<client>')) {
                    $status = 3;
                }
                break;
            }
            // 在adapter内
            case 2: {
                if (strstr($line, '=')) {
                    $pos = strpos($line, '=');
                    $name = substr($line, 0, $pos);
                    $value = substr($line, $pos + 1, strlen($line) - $pos);
                    $objAdapter[$name] = $value;
                }
                // 还要兼容多个adapter的情况(终止的时候兼容即可)
                elseif (strstr($line, '</')) {
                    $tarsAdapters[] = $objAdapter;
                    $objAdapter = [];
                    $status = 1;
                }
                break;
            }
            // 在client内
            case 3: {
                if (strstr($line, '=')) {
                    $pos = strpos($line, '=');
                    $name = substr($line, 0, $pos);
                    $value = substr($line, $pos + 1, strlen($line) - $pos);
                    $tarsClient[$name] = $value;
                }
                // 还要兼容多个adapter的情况(终止的时候兼容即可)
                elseif (strstr($line, '</client>')) {
                    $status = 0;
                }
                break;
            }
            default: {
                break;
            }
        }
    }

    //把not_tars协议的排序到最前面
    usort($tarsAdapters, function ($rowOne, $rowTwo) {
        if ($rowOne['protocol'] == 'not_tars' || $rowOne['protocol'] == 'not_taf') {
            return -1;
        }
        if ($rowTwo['protocol'] == 'not_tars' || $rowTwo['protocol'] == 'not_taf') {
            return 1;
        }
        return -1;
    });

    foreach ($tarsAdapters as $key => $tarsAdapter) {
        $tmp = getEndpointInfo($tarsAdapter['endpoint']);
        $tarsServer['listen'][] = $tmp;
        $tarsAdapters[$key]['listen'] = $tmp;
        $tarsAdapters[$key]['objName'] = explode('.', $tarsAdapter['servant'])[2];
    }

    $tarsServer['entrance'] = isset($tarsServer['entrance']) ? $tarsServer['entrance'] : $tarsServer['basepath'].'src/index.php';
    $setting['worker_num'] = max(array_column($tarsAdapters, 'threads'));
    $setting['task_worker_num'] = $tarsServer['task_worker_num'];
    $setting['dispatch_mode'] = $tarsServer['dispatch_mode'];
    $setting['daemonize'] = $tarsServer['daemonize'];

    if (isset($tarsServer['reactor_num'])) {
        $setting['reactor_num'] = $tarsServer['reactor_num'];
    }
    if (isset($tarsServer['max_request'])) {
        $setting['max_request'] = $tarsServer['max_request'];
    }
    if (isset($tarsServer['max_conn'])) {
        $setting['max_conn'] = $tarsServer['max_conn'];
    }
    if (isset($tarsServer['task_worker_num'])) {
        $setting['task_worker_num'] = $tarsServer['task_worker_num'];
    }
    if (isset($tarsServer['task_ipc_mode'])) {
        $setting['task_ipc_mode'] = $tarsServer['task_ipc_mode'];
    }
    if (isset($tarsServer['task_max_request'])) {
        $setting['task_max_request'] = $tarsServer['task_max_request'];
    }
    if (isset($tarsServer['task_tmpdir'])) {
        $setting['task_tmpdir'] = $tarsServer['task_tmpdir'];
    }
    if (isset($tarsServer['dispatch_func'])) {
        $setting['dispatch_func'] = $tarsServer['dispatch_func'];
    }
    if (isset($tarsServer['message_queue_key'])) {
        $setting['message_queue_key'] = $tarsServer['message_queue_key'];
    }
    if (isset($tarsServer['backlog'])) {
        $setting['backlog'] = $tarsServer['backlog'];
    }
    if (isset($tarsServer['loglevel'])) {
        $setting['log_level'] = $tarsServer['loglevel'];
    }
    if (isset($tarsServer['heartbeat_check_interval'])) {
        $setting['heartbeat_check_interval'] = $tarsServer['heartbeat_check_interval'];
    }
    if (isset($tarsServer['heartbeat_idle_time'])) {
        $setting['heartbeat_idle_time'] = $tarsServer['heartbeat_idle_time'];
    }
    if (isset($tarsServer['open_eof_check'])) {
        $setting['open_eof_check'] = $tarsServer['open_eof_check'];
    }
    if (isset($tarsServer['open_eof_split'])) {
        $setting['open_eof_split'] = $tarsServer['open_eof_split'];
    }
    if (isset($tarsServer['package_eof'])) {
        $setting['package_eof'] = $tarsServer['package_eof'];
    }
    if (isset($tarsServer['open_length_check'])) {
        $setting['open_length_check'] = $tarsServer['open_length_check'];
    }
    if (isset($tarsServer['package_length_type'])) {
        $setting['package_length_type'] = $tarsServer['package_length_type'];
    }
    if (isset($tarsServer['package_length_offset'])) {
        $setting['package_length_offset'] = $tarsServer['package_length_offset'];
    }
    if (isset($tarsServer['package_body_offset'])) {
        $setting['package_body_offset'] = $tarsServer['package_body_offset'];
    }
    if (isset($tarsServer['package_length_func'])) {
        $setting['package_length_func'] = $tarsServer['package_length_func'];
    }
    if (isset($tarsServer['package_max_length'])) {
        $setting['package_max_length'] = $tarsServer['package_max_length'];
    }
    if (isset($tarsServer['open_cpu_affinity'])) {
        $setting['open_cpu_affinity'] = $tarsServer['open_cpu_affinity'];
    }
    if (isset($tarsServer['open_tcp_nodelay'])) {
        $setting['open_tcp_nodelay'] = $tarsServer['open_tcp_nodelay'];
    }
    if (isset($tarsServer['buffer_output_size'])) {
        $setting['buffer_output_size'] = $tarsServer['buffer_output_size'];
    }
    if (isset($tarsServer['tcp_defer_accept'])) {
        $setting['tcp_defer_accept'] = $tarsServer['tcp_defer_accept'];
    }
    if (isset($tarsServer['ssl_cert_file'])) {
        $setting['ssl_cert_file'] = $tarsServer['ssl_cert_file'];
    }
    if (isset($tarsServer['ssl_method'])) {
        $setting['ssl_method'] = $tarsServer['ssl_method'];
    }
    if (isset($tarsServer['ssl_ciphers'])) {
        $setting['ssl_ciphers'] = $tarsServer['ssl_ciphers'];
    }
    if (isset($tarsServer['user'])) {
        $setting['user'] = $tarsServer['user'];
    }
    if (isset($tarsServer['group'])) {
        $setting['group'] = $tarsServer['group'];
    }
    if (isset($tarsServer['chroot'])) {
        $setting['chroot'] = $tarsServer['chroot'];
    }
    if (isset($tarsServer['pid_file'])) {
        $setting['pid_file'] = $tarsServer['pid_file'];
    }
    if (isset($tarsServer['pipe_buffer_size'])) {
        $setting['pipe_buffer_size'] = $tarsServer['pipe_buffer_size'];
    }
    if (isset($tarsServer['buffer_output_size'])) {
        $setting['buffer_output_size'] = $tarsServer['buffer_output_size'];
    }
    if (isset($tarsServer['socket_buffer_size'])) {
        $setting['socket_buffer_size'] = $tarsServer['socket_buffer_size'];
    }
    if (isset($tarsServer['enable_unsafe_event'])) {
        $setting['enable_unsafe_event'] = $tarsServer['enable_unsafe_event'];
    }
    if (isset($tarsServer['discard_timeout_request'])) {
        $setting['discard_timeout_request'] = $tarsServer['discard_timeout_request'];
    }
    if (isset($tarsServer['enable_reuse_port'])) {
        $setting['enable_reuse_port'] = $tarsServer['enable_reuse_port'];
    }
    if (isset($tarsServer['enable_delay_receive'])) {
        $setting['enable_delay_receive'] = $tarsServer['enable_delay_receive'];
    }
    if (isset($tarsServer['open_http_protocol'])) {
        $setting['open_http_protocol'] = $tarsServer['open_http_protocol'];
    }
    if (isset($tarsServer['open_http2_protocol'])) {
        $setting['open_http2_protocol'] = $tarsServer['open_http2_protocol'];
    }
    if (isset($tarsServer['open_websocket_protocol'])) {
        $setting['open_websocket_protocol'] = $tarsServer['open_websocket_protocol'];
    }
    if (isset($tarsServer['open_mqtt_protocol'])) {
        $setting['open_mqtt_protocol'] = $tarsServer['open_mqtt_protocol'];
    }
    if (isset($tarsServer['reload_async'])) {
        $setting['reload_async'] = $tarsServer['reload_async'];
    }
    if (isset($tarsServer['tcp_fastopen'])) {
        $setting['tcp_fastopen'] = $tarsServer['tcp_fastopen'];
    }
    if (isset($tarsServer['request_slowlog_file'])) {
        $setting['request_slowlog_file'] = $tarsServer['request_slowlog_file'];
    }

    $setting['log_file'] = $tarsServer['logpath'].$tarsServer['app'].'/'.$tarsServer['server'].'/'.
        $tarsServer['app'].'.'.$tarsServer['server'].'.log';
    $setting['log_path'] = $tarsServer['logpath'];

    $tarsServer['adapters'] = $tarsAdapters;
    $tarsServer['setting'] = $setting;

    $application['server'] = $tarsServer;
    $application['client'] = $tarsClient;

    $tarsConf = [
        'tars' => [
            'application' => $application,
        ],
    ];


    return $tarsConf;
}
function getEndpointInfo($endpoint)
{
    $parts = explode('-', $endpoint);
    $sHost = '';
    $sProtocol = '';
    $iPort = '';
    $iTimeout = '';
    $bIp = '';
    foreach ($parts as $part) {
        if (strstr($part, 'tcp')) {
            $sProtocol = 'tcp';
        } elseif (strstr($part, 'udp')) {
            $sProtocol = 'udp';
        } elseif (strpos($part, 'h') !== false) {
            $sHost = trim($part, " h\t\r");
        } elseif (strpos($part, 'b') !== false) {
            $bIp = trim($part, " b\t\r");
        } elseif (strpos($part, 'p') !== false) {
            $iPort = trim($part, " p\t\r");
        } elseif (strpos($part, 't') !== false) {
            $iTimeout = trim($part, " t\t\r");
        }
    }

    return [
        'sHost' => $sHost,
        'sProtocol' => $sProtocol,
        'iPort' => $iPort,
        'iTimeout' => $iTimeout,
        'bIp' => $bIp,
        'sIp' => $sHost,
    ];
}