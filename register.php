<?php
require 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fio   = trim($_POST['fio'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!preg_match('/^[a-zA-Z0-9]{6,}$/', $login)) {
        $error = 'Логин: латиница и цифры, не менее 6 символов.';
    } elseif (mb_strlen($pass) < 8) {
        $error = 'Пароль: минимум 8 символов.';
    } elseif (!preg_match('/^[А-Яа-яЁё ]+$/u', $fio)) {
        $error = 'ФИО: только кириллица и пробелы.';
    } elseif (!preg_match('/^8\(\d{3}\)\d{3}-\d{2}-\d{2}$/', $phone)) {
        $error = 'Телефон: формат 8(XXX)XXX-XX-XX.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Некорректный e-mail.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE login = ?');
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $error = 'Логин занят.';
        } else {
            $pdo->prepare('INSERT INTO users (fio, login, password, phone, email) VALUES (?, ?, ?, ?, ?)')
                ->execute([$fio, $login, password_hash($pass, PASSWORD_DEFAULT), $phone, $email]);
            go('login.php');
        }
    }
}

$title = 'Регистрация';
require 'header.php';
?>
<div class="d-flex justify-content-center align-items-center" style="min-height:70vh">
    <div class="w-100" style="max-width:400px">
        <h1 class="h3 mb-3 text-center">Регистрация</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="card p-4">
            <input class="form-control mb-2" name="login" placeholder="Логин" data-testid="reg-login" required>
            <input class="form-control mb-2" type="password" name="password" placeholder="Пароль" data-testid="reg-password" required>
            <input class="form-control mb-2" name="fio" placeholder="ФИО" data-testid="reg-fullname" required>
            <input class="form-control mb-2" name="phone" placeholder="8(XXX)XXX-XX-XX" data-testid="reg-phone" required>
            <input class="form-control mb-2" type="email" name="email" placeholder="Email" data-testid="reg-email" required>
            <button class="btn btn-primary w-100" data-testid="reg-submit">Создать пользователя</button>
        </form>
        <p class="text-center mt-3 mb-0"><a href="login.php">Еще не зарегистрированы? Регистрация</a></p>
    </div>
</div>
<?php require 'footer.php'; ?>