<?php
namespace Codeages\Biz\Framework\Token\Service;

interface TokenService
{
    /**
     * 生成一个一次性的Token
     *
     * @param string $type Token类型
     * @param array  $args 生成Token的一些限制规则
     *
     * @return array 生成的Token
     */
    public function generate($place, $lifetime, $times = 0, $data = null);

    /**
     * 校验Token
     *
     * @param string $type Token类型
     * @param string $key  Token的值
     *
     * @return bool 该Token值是否OK
     */
    public function verify($place, $key);

    /**
     * 作废一个Token
     *
     * @param [type] $value 要摧毁的Token的值
     */
    public function destroy($place, $value);

}
