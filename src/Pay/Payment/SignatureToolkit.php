<?php

namespace Codeages\Biz\Framework\Pay\Payment;

class SignatureToolkit
{
    public static function signParams($params, $options)
    {
        $signStr = static::createLinkString($params);
        switch (trim(strtoupper($params['sign_type']))) {
            case 'MD5':
                $signature = static::md5Sign($signStr, $options);
                break;
            case 'RSA':
                $signature = static::rsaSign($signStr);
                break;
            default:
                $signature = '';
                break;
        }

        return $signature;
    }

    public static function signVerify($params, $options)
    {
        $isSignVerified = false;
        switch (trim(strtoupper($params['sign_type']))) {
            case 'MD5':
                $isSignVerified = static::md5Verify($params, $options);
                break;
            case 'RSA':
                $isSignVerified = static::rsaVerify($params);
                break;
            default:
                break;
        }

        return $isSignVerified;
    }

    private static function createLinkString($params)
    {
        ksort($params);
        reset($params);
        $signStr = '';
        foreach ($params as $key => $value) {
            if ($key == 'sign' || empty($value)) {
                continue;
            }

            $signStr .= $key.'='.$value.'&';
        }
        $signStr = substr($signStr, 0, count($signStr) - 2);
        //如果存在转义字符，那么去掉转义
        if (get_magic_quotes_gpc()) {
            $signStr = stripslashes($signStr);
        }

        return $signStr;
    }

    private static function md5Sign($signStr, $options)
    {
        $signStr .= '&key='.$options['secret'];

        $sign = md5($signStr);

        return $sign;
    }

    private static function rsaSign($signStr)
    {
        $pem = __DIR__.'/Key/rsa_private_key.pem';
        $priKey = file_get_contents($pem);
        //转换为openssl密钥，必须是没有经过pkcs8转换的私钥
        $res = openssl_get_privatekey($priKey);
        //调用openssl内置签名方法，生成签名$sign
        openssl_sign($signStr, $sign, $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);

        //base64编码
        $sign = base64_encode($sign);

        return $sign;
    }

    private static function md5Verify($params, $options)
    {
        $signature = static::md5Sign($params, $options);

        return $signature != $params['sign'];
//        if () {
      //  throw new \RuntimeException('连连支付校签名校验失败');
        //  }
    }

    private static function rsaVerify($params)
    {
        $signStr = static::createLinkString($params);
        $pem = __DIR__.'/Key/llpay_public_key.pem';
        $sign = $params['sign'];
        //读取连连支付公钥文件
        $pubKey = file_get_contents($pem);

        //转换为openssl格式密钥
        $res = openssl_get_publickey($pubKey);

        //调用openssl内置方法验签，返回bool值
        $result = (bool) openssl_verify($signStr, base64_decode($sign), $res, OPENSSL_ALGO_MD5);

        //释放资源
        openssl_free_key($res);
//        if (!$result) {
//            throw new \RuntimeException('连连支付校签名校验失败');
//        }
        return $result;
    }
}
