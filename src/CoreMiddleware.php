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
namespace Hyperftars\Tars;

use Closure;
use Hyperf\HttpServer\Router\Dispatched;
use Hyperf\Rpc\Protocol;
use Hyperftars\Tars\ParamInfos;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CoreMiddleware extends \Hyperf\RpcServer\CoreMiddleware
{
    /**
     * @var ResponseBuilder
     */
    protected $responseBuilder;

    public function __construct(ContainerInterface $container, Protocol $protocol, ResponseBuilder $builder, string $serverName)
    {
        parent::__construct($container, $protocol, $serverName);
        $this->responseBuilder = $builder;
    }

    protected function handleFound(Dispatched $dispatched, ServerRequestInterface $request)
    {
        $data = $request->getParsedBody();
        if ($dispatched->handler->callback instanceof Closure) {
            $response = call($dispatched->handler->callback);
        } else {

            [$controller, $action] = $this->prepareHandler($dispatched->handler->callback);

            $controllerInstance = $this->container->get($controller);

            if (! method_exists($controller, $action)) {

                return $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::INTERNAL_ERROR);
            }
            try {
                $args = $data['args'];
                $response = $controllerInstance->{$action}(...$args);
                $data['args'] = $args;

            } catch (\Throwable $exception) {
                $response = $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::SERVER_ERROR, $exception);
                $this->responseBuilder->persistToContext($response);

                throw $exception;
            }
        }

        $responseData["unpackResult"]=$data;
        $responseData["returnVal"]=$response;

        return $responseData;
    }

    protected function handleNotFound(ServerRequestInterface $request)
    {

        return $this->responseBuilder->buildErrorResponse($request, ResponseBuilder::METHOD_NOT_FOUND);
    }

    protected function handleMethodNotAllowed(array $routes, ServerRequestInterface $request)
    {
        return $this->handleNotFound($request);
    }

    protected function transferToResponse($response, ServerRequestInterface $request): ResponseInterface
    {

        return $this->responseBuilder->buildResponse($request, $response);
    }

    protected function parseParameters(string $controller, string $action, array $arguments): array
    {

        return $arguments;
    }
}
