<?php

/*
 * This file is part of the Codeages Biz Framework.
 *
 * (c) Codeages Team <dev@codeages.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Codeages\Biz\Framework\Logger;

use Doctrine\DBAL\Logging\SQLLogger;
use Psr\Log\LoggerInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * 支持记录 SQL 执行时间的 DBAL Logger
 * 
 * 该 Logger 在原有 SQL 日志功能基础上增加了执行时间记录，
 * 帮助开发者更好地分析和优化数据库查询性能。
 * 
 * @author Biz Framework Team
 */
class TimedDbalLogger implements SQLLogger
{
    /**
     * 参数字符串最大长度
     */
    public const MAX_STRING_LENGTH = 32;

    /**
     * 二进制数据占位符
     */
    public const BINARY_DATA_VALUE = '(binary value)';

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @var Stopwatch|null
     */
    protected $stopwatch;
    
    /**
     * SQL 查询开始时间（微秒时间戳）
     * 
     * @var float|null
     */
    private $startTime = null;
    
    /**
     * 当前执行的 SQL 语句
     * 
     * @var string|null
     */
    private $currentSql = null;
    
    /**
     * 当前 SQL 语句的参数
     * 
     * @var array|null
     */
    private $currentParams = null;

    /**
     * 构造函数
     *
     * @param LoggerInterface|null $logger PSR-3 兼容的日志记录器
     * @param Stopwatch|null $stopwatch Symfony Stopwatch 组件（可选）
     */
    public function __construct(?LoggerInterface $logger = null, ?Stopwatch $stopwatch = null)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
    }

    /**
     * 开始记录 SQL 查询
     * 
     * @param string $sql SQL 查询语句
     * @param array|null $params 查询参数
     * @param array|null $types 参数类型（暂未使用）
     * 
     * @return void
     */
    public function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        try {
            $this->startTime = microtime(true);
            $this->currentSql = $sql;
            $this->currentParams = $params;
            
            if (null !== $this->stopwatch) {
                $this->stopwatch->start('doctrine', 'doctrine');
            }
        } catch (\Throwable $e) {
            // 防止 Logger 异常影响主业务逻辑
            // 静默处理，不影响 SQL 执行
        }
    }

    /**
     * 停止记录 SQL 查询并记录执行时间
     * 
     * @return void
     */
    public function stopQuery()
    {
        try {
            if (null !== $this->stopwatch) {
                $this->stopwatch->stop('doctrine');
            }

            if (null !== $this->logger && null !== $this->startTime) {
                $executionTime = (microtime(true) - $this->startTime) * 1000; // 转换为毫秒
                $this->log($this->currentSql, $this->currentParams, $executionTime);
            }
        } catch (\Throwable $e) {
            // 防止 Logger 异常影响主业务逻辑
            // 静默处理，不影响 SQL 执行
        } finally {
            // 无论是否出现异常，都要重置状态
            $this->resetState();
        }
    }

    /**
     * 重置内部状态
     * 
     * @return void
     */
    private function resetState()
    {
        $this->startTime = null;
        $this->currentSql = null;
        $this->currentParams = null;
    }

    /**
     * 记录带有执行时间的 SQL 日志
     *
     * @param string|null $sql SQL 查询语句
     * @param array|null $params 查询参数
     * @param float $executionTimeMs 执行时间（毫秒）
     * 
     * @return void
     */
    protected function log($sql, ?array $params, $executionTimeMs)
    {
        if (null === $sql || null === $this->logger) {
            return;
        }

        $normalizedParams = null === $params ? [] : $this->normalizeParams($params);
        
        // 格式化日志消息，包含执行时间
        $logMessage = sprintf(
            '[%.2fms] %s',
            $executionTimeMs,
            $sql
        );
        
        $this->logger->debug($logMessage, $normalizedParams);
    }

    /**
     * 标准化查询参数，处理二进制数据和过长字符串
     *
     * @param array $params 原始参数数组
     * 
     * @return array 标准化后的参数数组
     */
    private function normalizeParams(array $params)
    {
        foreach ($params as $index => $param) {
            // 递归处理数组参数
            if (\is_array($param)) {
                $params[$index] = $this->normalizeParams($param);
                continue;
            }

            // 跳过非字符串类型的参数
            if (!\is_string($params[$index])) {
                continue;
            }

            // 处理非 UTF-8 字符串（通常是二进制数据）
            if (!preg_match('//u', $params[$index])) {
                $params[$index] = self::BINARY_DATA_VALUE;
                continue;
            }

            // 截断过长的字符串
            if (self::MAX_STRING_LENGTH < mb_strlen($params[$index], 'UTF-8')) {
                $params[$index] = mb_substr($params[$index], 0, self::MAX_STRING_LENGTH - 6, 'UTF-8').' [...]';
                continue;
            }
        }

        return $params;
    }
} 