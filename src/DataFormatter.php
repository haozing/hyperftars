<?php
namespace Hyperftars\Tars;
use Hyperf\Rpc\Contract\DataFormatterInterface;

class DataFormatter implements DataFormatterInterface
{
    /**
     * @param array $data [$path, $params, $id]
     * @return array
     */
    public function formatRequest($data)
    {
        return $data;
    }

    /**
     * @param array $data [$id, $result]
     * @return array
     */
    public function formatResponse($data)
    {
        return $data;
    }

    /**
     * @param array $data [$id, $code, $message, $exception]
     * @return array
     */
    public function formatErrorResponse($data)
    {
        return $data;
    }

}