<?php
require 'config.php';
if (!user()) go('login.php');
if (is_admin()) go('admin.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rid = (int)($_POST['request_id'] ?? 0);
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $text = trim($_POST['text'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM requests WHERE id = ? AND user_id = ? AND status = "Завершено"');
    $stmt->execute([$rid, user()['id']]);
    $r = $stmt->fetch();

    if ($r && $text !== '') {
        $pdo->prepare('INSERT INTO reviews (user_id, request_id, rating, text) VALUES (?, ?, ?, ?)')
            ->execute([user()['id'], $rid, $rating, $text]);
    }
}
go('requests.php');