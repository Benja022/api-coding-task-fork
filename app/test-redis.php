<?php

try {
    $redis = new Redis();
    $redis->connect('cache', 6379);
    echo "Conexión exitosa a Redis\n";

    $redis->set('test', 'Hello Redis');
    $value = $redis->get('test');
    echo "Valor recuperado: $value\n";

    $redis->close();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
