<?php
/**
 * Koneksi Database MySQL Lokal untuk Website Sekolah
 * Menggunakan MySQLi dengan Prepared Statements untuk keamanan
 */

$host = 'localhost';
$dbname = 'db_sekolah';
$username = 'root';
$password = ''; // Default XAMPP password kosong

try {
    // Koneksi dengan PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db_connected = true;
} catch (PDOException $e) {
    $db_connected = false;
    $db_error = $e->getMessage();
}

// Fungsi helper untuk query sederhana
function db_query($sql, $params = []) {
    global $pdo, $db_connected;
    if (!$db_connected) return false;
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        return false;
    }
}

function db_fetch_all($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetchAll() : [];
}

function db_fetch_one($sql, $params = []) {
    $stmt = db_query($sql, $params);
    return $stmt ? $stmt->fetch() : null;
}

function db_insert($table, $data) {
    global $pdo, $db_connected;
    if (!$db_connected) return false;
    
    $columns = implode(', ', array_keys($data));
    $placeholders = ':' . implode(', :', array_keys($data));
    
    try {
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);
        return $pdo->lastInsertId();
    } catch (PDOException $e) {
        error_log("Insert Error: " . $e->getMessage());
        return false;
    }
}

function db_update($table, $data, $where, $where_params = []) {
    global $pdo, $db_connected;
    if (!$db_connected) return false;
    
    $set = [];
    foreach (array_keys($data) as $col) {
        $set[] = "$col = :$col";
    }
    $set_clause = implode(', ', $set);
    
    try {
        $sql = "UPDATE $table SET $set_clause WHERE $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array_merge($data, $where_params));
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Update Error: " . $e->getMessage());
        return false;
    }
}

function db_delete($table, $where, $params = []) {
    global $pdo, $db_connected;
    if (!$db_connected) return false;
    
    try {
        $sql = "DELETE FROM $table WHERE $where";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log("Delete Error: " . $e->getMessage());
        return false;
    }
}
?>
