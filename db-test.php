<?php

$password = readline("Masukkan password MariaDB root: ");

try {
    $pdo = new PDO(
        'mysql:host=localhost;port=3306;dbname=gaji_hub',
        'root',
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ]
    );

    echo PHP_EOL . "KONEKSI PHP -> MARIADB BERHASIL!" . PHP_EOL;

} catch (PDOException $e) {
    echo PHP_EOL . "KONEKSI GAGAL:" . PHP_EOL;
    echo $e->getMessage() . PHP_EOL;
}