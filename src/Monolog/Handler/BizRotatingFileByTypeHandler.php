<?php

namespace Codeages\Biz\Framework\Monolog\Handler;

// use Monolog\Handler\StreamHandler;

/**
 * 根据logger方法调用时传入的type写入对应日志文件，同时带有切割，压缩，清理多余文件能力
 */
class BizRotatingFileByTypeHandler extends BizRotatingFileHandler
{
    //主入口
    protected function write(array $record)
    {
        if (empty($record['type'])) {
            return;
        }
        $this->handleFilenameByType($record['type']);
        parent::write($record);
    }

    protected function handleFilenameByType($type)
    {
        $fileInfo = pathinfo($this->filename);
        $this->filename = $fileInfo['dirname'].'/'.$type.'.log';
        $this->url = $fileInfo['dirname'].'/'.$type.'.log';
    }
}
