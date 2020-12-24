<?php

namespace Hyperftars\Tars\ParseAnnotation;
class TarsParse
{
    public function parseAnnotation($docblock)
    {
        $docblock = trim($docblock, '/** ');
        $lines = explode('*', $docblock);
        $validLines = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $validLines[] = $line;
            }
        }
        // 对解析出来的annotation进行处理
        $index = 0;
        $inParams = [];
        $outParams = [];
        $returnParam = [];

        foreach ($validLines as $validLine) {
            // 说明是参数类型
            if (strstr($validLine, '@param')) {
                ++$index;
                // 说明是输出参数
                if (strstr($validLine, '=out=')) {
                    $parts = explode(' ', $validLine);

                    $outParams[] = [
                        'type' => $parts[1],
                        'proto' => $parts[3],
                        'name' => trim($parts[2], '$'),
                        'tag' => (string)$index,
                    ];
                } // 输入参数
                else {
                    $parts = explode(' ', $validLine);

                    $inParams[] = [
                        'type' => $parts[1],
                        'proto' => isset($parts[3]) ? $parts[3] : '',
                        'name' => trim($parts[2], '$'),
                        'tag' => (string)$index,
                    ];
                }
            } // 说明是返回类型
            else {
                $parts = explode(' ', $validLine);

                if (count($parts) > 2) {
                    $returnParam = [
                        'type' => $parts[1],
                        'proto' => $parts[2],
                        'tag' => "0",
                    ];
                } else {
                    $returnParam = [
                        'type' => $parts[1],
                        'tag' => "0",
                    ];
                }
            }
        }

        return [
            'inParams' => $inParams,
            'outParams' => $outParams,
            'return' => $returnParam,
        ];
    }
}