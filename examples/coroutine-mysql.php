<?php
include '../vendor/autoload.php';

use Smf\ConnectionPool\ConnectionPool;
use Smf\ConnectionPool\Connectors\CoroutineMySQLConnector;
use Swoole\Coroutine\MySQL;

go(function () {
    // All MySQL connections: [10, 30]
    $pool = new ConnectionPool(
        [
            'minActive'         => 10,
            'maxActive'         => 30,
            'maxWaitTime'       => 5,
            'maxIdleTime'       => 20,
            'idleCheckInterval' => 10,
        ],
        new CoroutineMySQLConnector,
        [
            'host'        => '127.0.0.1',
            'port'        => '3306',
            'user'        => 'root',
            'password'    => 'xy123456',
            'database'    => 'mysql',
            'timeout'     => 10,
            'charset'     => 'utf8mb4',
            'strict_type' => true,
            'fetch_mode'  => true,
        ]
    );
    echo "Initialize connection pool\n";
    $pool->init();
    defer(function () use ($pool) {
        echo "Close connection pool\n";
        $pool->close();
    });

    /**@var MySQL $connection */
    $connection = $pool->borrow();
    defer(function () use ($pool, $connection) {
        echo "Return the connection to pool\n";
        $pool->return($connection);
    });
    $status = $connection->query('SHOW STATUS LIKE "Threads_connected"');
    var_dump($status);
});
