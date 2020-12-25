# hyperftars-dev
注意：开发版bug多，不要用于生产。
### 目录结构

```shell script
scripts 
---tars2php.sh
src
----app
----bin
----config
----runtime
----test
----vendor
.
.
.
tars
----example.tars
----tars.proto.php
```


## 安装
```
composer require hyperftars/tars
```
### 同时需要使用 JSON RPC 服务端安装
```
composer require hyperf/rpc-server
```
### 快速生成配置文件
```shell script
php bin/hyperf.php vendor:publish hyperftars/tars
```
### 配置自己的tars文件
文件位置config/autoload
```php
<?php
return [
    'obj' => [
        'home-api' => 'App\TarsRpc\obj\AccountServiceServant',
        'protocolName' => 'tars', //http, json, tars or other
        'serverType' => 'tcp', //http(no_tars default), websocket, tcp(tars default), udp
    ],
];
```
其中home-api就是你放置根据协议生成的接口文件地址。

### 配置.env

```php
APP_NAME 字段要同tars服务名一致。
例如：
swapi·account  //应用·服务名
```
### 生产接口

自行定义/tars/example.tars文件

修改/tars/tars.proto.php配置

```php
<?php
/**
 * Created by PhpStorm.
 * User: liangchen
 * Date: 2018/2/24
 * Time: 下午3:43.
 */

return array(
    'appName' => 'swapi',
    'serverName' => 'account',
    'objName' => 'obj',
    'withServant' => true, //决定是服务端,还是客户端的自动生成
    'tarsFiles' => array(
        './example.tars',
    ),
    'dstPath' => '../src/app/TarsRpc',
    'namespacePrefix' => 'App\TarsRpc',
);
```
appName 和 serverName 字段要同tars服务名一致。

例如：

swapi·account  //应用·服务名

然后执行命令

```shell script
cd tars
php ../src/vendor/phptars/tars2php/src/tars2php.php ./tars.proto.php
```
### 实现接口
```php
<?php

namespace App\TarsRpc;
use Hyperf\RpcServer\Annotation\RpcService;
use App\TarsRpc\obj\AccountServiceServant;
/**
 * 路由注解
 * @RpcService(name="swapi.account.obj", protocol="tars", server="obj")
 */
class PHPServerServantImpl implements AccountServiceServant
{
    /**
     * @param string $username
     * @return bool
     */
    public function accountCheckUsername($username)
    {
        return true;
    }
}

```
使用注解路由的方式。
@RpcService(name="swapi.account.obj", protocol="tars", server="obj")
和之前的配置对应即可。

## 完成情况

- [x] 启动停止
- [x] 拉取配置
- [x] tcp tars协议
- [x] 存活上报
- [ ] http
- [ ] 协议的打包解包
- [ ] 其他RPC协议
- [ ] 代码自动生成
- [ ] 监控信息上报
- [ ] 启动多个Servant
- [ ] 多个实现类
- [ ] 未定义路由协议错误提示
