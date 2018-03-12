<?php

namespace Codeages\Biz\Framework\Monolog\Handler;

use Monolog\Handler\StreamHandler;

/**
 * 切割相关功能，切割，压缩，清理多余文件，压缩暂时未做
 */
class BizRotatingFileHandler extends StreamHandler
{
    protected $filename;
    protected $maxFiles;
    protected $mustRotate;
    protected $nextRotation;
    protected $filenameFormat;

    public function __construct($filename, $maxFiles = 0, $maxSize = 1024, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false)
    {
        $this->filename = $filename;
        $this->maxFiles = (int) $maxFiles;
        $this->nextRotation = $maxSize;
        $this->filenameFormat = '{filename}.{extension}.{num}';

        parent::__construct($filename, $level, $bubble, $filePermission, $useLocking);
    }

    //主入口
    protected function write(array $record)
    {
        // on the first record written, if the log is new, we should rotate (once per 1mb)
        if (null === $this->mustRotate) {
            $this->mustRotate = !file_exists($this->url);
        }
        if (file_exists($this->url) && $this->nextRotation < filesize($this->url)) {
            $this->mustRotate = true;
            $this->close();
        }
        parent::write($record);
    }

    public function close()
    {
        parent::close();

        if (true === $this->mustRotate) {
            $this->rotate();
        }
    }

    //检测是否需要清理老文件
    protected function rotate()
    {
        //检测文件数量，超过最大限制则直接删除biz.log.1并重命名排序
        $logFiles = glob($this->getGlobPattern());
        if ($this->maxFiles <= count($logFiles)) {
            usort($logFiles, function ($a, $b) {
                return strcmp($b, $a);
            });

            foreach (array_slice($logFiles, $this->maxFiles - 1) as $file) {
                if (is_writable($file)) {
                    // suppress errors here as unlink() might fail if two processes
                    // are cleaning up/rotating at the same time
                    set_error_handler(function ($errno, $errstr, $errfile, $errline) {});
                    unlink($file);
                    restore_error_handler();
                }
            }
            //重命名排序
            $num = 1;
            $logFiles = glob($this->getGlobPattern());
            foreach ($logFiles as $logFile) {
                if (file_exists($logFile)) {
                    $newName = $this->getFilename($num);
                    rename($logFile, $newName);
                    ++$num;
                }
            }
        }
        //如果biz.log文件大于单文件最大限制则直接修改biz.log->biz.log.10
        if (file_exists($this->url) && $this->nextRotation < filesize($this->url)) {
            $logFiles = glob($this->getGlobPattern());
            $newName = $this->getFilename(count($logFiles) + 1);
            rename($this->url, $newName);
        }
        $this->mustRotate = false;
    }

    //获取实际文件名
    protected function getFilename($num)
    {
        $fileInfo = pathinfo($this->filename);

        return str_replace(
            array('{filename}', '{extension}', '{num}'),
            array($fileInfo['filename'], 'log', $this->formateNum($num)),
            $fileInfo['dirname'].'/'.$this->filenameFormat
        );
    }

    //根据特定文件格式，获取全部文件列表
    protected function getGlobPattern()
    {
        $fileInfo = pathinfo($this->filename);
        $glob = str_replace(
            array('{filename}', '{extension}', '{num}'),
            array($fileInfo['filename'], 'log', '*'),
            $fileInfo['dirname'].'/'.$this->filenameFormat
        );

        return $glob;
    }

    protected function formateNum($num)
    {
        $numlen = strlen($num);
        $maxlen = strlen((string) $this->maxFiles);
        for ($i = 0; $i < $maxlen - $numlen; ++$i) {
            $num = '0'.$num;
        }

        return $num;
    }
}
