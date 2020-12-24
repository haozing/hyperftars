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
use Hyperf\Framework\Event\BeforeWorkerStart;
use Hyperf\Framework\Event\BootApplication;
use Hyperftars\Tars\DataFormatter;
use Hyperftars\Tars\TarsRpcTransporter;
use Hyperftars\Tars\Packer\TarsEofPacker;
use Hyperftars\Tars\ParseAnnotation\TarsParse;
use Hyperftars\Tars\PathGenerator;
use Hyperf\Rpc\ProtocolManager;
use Hyperf\Utils\Packer\JsonPacker;

class RegisterProtocolListener implements ListenerInterface
{
    /**
     * @var ProtocolManager
     */
    private $protocolManager;

    public function __construct(ProtocolManager $protocolManager)
    {
        $this->protocolManager = $protocolManager;
    }

    public function listen(): array
    {
        return [
            BootApplication::class,
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
        $this->protocolManager->register('tars', [
            'packer' => TarsEofPacker::class,
            //'transporter' => TarsRpcTransporter::class,
            'path-generator' => PathGenerator::class,
            'data-formatter' => DataFormatter::class,
            'tars-parse' => TarsParse::class,
        ]);
    }
}
