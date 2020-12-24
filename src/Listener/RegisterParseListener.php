<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Hyperftars\Tars\Listener;

use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Event\BeforeServerStart;
use Hyperftars\Tars\ParseAnnotation\TarsParse;

use Hyperftars\Tars\ParamInfos;


class RegisterParseListener implements ListenerInterface
{
    /**
     * @var ParamInfos
     */
    private $ParamInfos;

    public function __construct(ParamInfos $ParamInfos)
    {
        $this->ParamInfos = $ParamInfos;
    }

    public function listen(): array
    {
        return [
            BeforeServerStart::class,
        ];
    }

    /**
     * All official rpc protocols should register in here,
     * and the others non-official protocols should register in their own component via listener.
     *
     * @param BeforeWorkerStart $event
     */
    public function process(object $event)
    {
        $this->ParamInfos->register();
    }
}
