<?php

return array(
    'env' => 'test',
    'debug' => true,
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'edusoho-test',
        'user' => 'root',
        'password' => '',
        'charset'  => 'utf8'
    ),
    'cache'    => array(
        'default' => array(
            "host"           => "127.0.0.1",
            "port"           => 6378,
            "timeout"        => 1,
            "reserved"       => null,
            "retry_interval" => 100
        )
    )
);
