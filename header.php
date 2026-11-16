<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <title><?= e($title ?? 'Конференции.РФ') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand navbar-light bg-white border-bottom mb-4">
    <div class="container">
    <img src="img/logo2.png" alt="Конференции.РФ" style="height:36px; width:auto;">
        <a class="navbar-brand" href="index.php">Конференции.РФ</a>
        <div class="ms-auto d-flex align-items-center gap-2">
            <?php if (user()): ?>
                <a class="btn btn-sm btn-outline-secondary" href="requests.php">Мои заявки</a>
                <a class="btn btn-sm btn-outline-secondary" href="request_create.php">Новая заявка</a>
                <?php if (is_admin()): ?>
                    <a class="btn btn-sm btn-outline-secondary" href="admin.php">Админка</a>
                <?php endif; ?>
                <span class="navbar-text small"><?= e(user()['fio']) ?></span>
                <a class="btn btn-sm btn-outline-secondary" href="logout.php">Выйти</a>
            <?php else: ?>
                <a class="btn btn-sm btn-outline-secondary" href="login.php">Вход</a>
                <a class="btn btn-sm btn-outline-secondary" href="register.php">Регистрация</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container pb-5">