<?php

namespace Codeages\Biz\Framework\Dao;

/**
 * Dao的缓存策略接口
 */
interface CacheStrategy
{
    /**
     * 在Dao的get系列方法调用之前调用，以获取缓存。
     *
     * @param string $table Dao对应的表名
     * @param  string $method 调用的Dao方法名
     * @param  array $arguments 调用的Dao参数
     * 
     * @return mixed 缓存存在返回结果集，否则返回false。
     */
    public function beforeGet($table, $method, $arguments);

    /**
     * 在Dao的get系列方法调用之后调用，可以缓存结果。
     *
     * @param string $table Dao对应的表名
     * @param  string $method 调用Dao方法名
     * @param  array $arguments 调用Dao参数
     * @param  mixed $row 调用Dao后的结果
     */
    public function afterGet($table, $method, $arguments, $row);

    /**
     * 在Dao的find系列方法调用之前调用，以获取缓存。
     *
     * @param string $table Dao对应的表名
     * @param  string $method 调用Dao方法名
     * @param  array $arguments 调用Dao参数
     * 
     * @return mixed 缓存存在返回结果集，否则返回false。
     */
    public function beforeFind($table, $methd, $arguments);

    /**
     * 在Dao的find系列方法调用之后调用，可以缓存结果集。
     *
     * @param string $table Dao对应的表名
     * @param  string $method 调用Dao方法名
     * @param  array $arguments 调用Dao参数
     * @param  mixed $rows 调用Dao后的结果
     */
    public function afterFind($table, $methd, $arguments, array $rows);

    /**
     * 在Dao的search系列方法调用之前调用，以获取缓存。
     *
     * @param string $table Dao对应的表名
     * @param  string $method 调用Dao方法名
     * @param  array $arguments 调用Dao参数
     * 
     * @return mixed 缓存存在返回结果集，否则返回false。
     */
    public function beforeSearch($table, $methd, $arguments);

    /**
     * 在Dao的search系列方法调用之后调用，可以缓存结果集。
     *
     * @param string $table Dao对应的表名
     * @param  string $method 调用Dao方法名
     * @param  array $arguments 调用Dao参数
     * @param  mixed $rows 调用Dao后的结果
     */
    public function afterSearch($table, $methd, $arguments, array $rows);

    /**
     * 在Dao的create系列方法调用之后调用，可以缓存结果集。
     *
     * @param string $table Dao对应的表名
     * @param  string $method 调用Dao方法名
     * @param  array $arguments 调用Dao参数
     * @param  mixed $row 调用Dao后的结果
     */
    public function afterCreate($table, $methd, $arguments, $row);

    /**
     * 在Dao的update系列方法调用之后调用，可以缓存结果集。
     *
     * @param string $table Dao对应的表名
     * @param  string $method 调用Dao方法名
     * @param  array $arguments 调用Dao参数
     * @param  mixed $row 调用Dao后的结果
     */
    public function afterUpdate($table, $methd, $arguments, $row);

    /**
     * 在Dao的delete系列方法调用之后调用，可以删除缓存。
     *
     * @param string $table Dao对应的表名
     * @param  string $method 调用Dao方法名
     * @param  array $arguments 调用Dao参数
     */
    public function afterDelete($table, $methd, $arguments);
}