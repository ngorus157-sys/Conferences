<?php
require 'config.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if ($login === ADMIN_LOGIN && $pass === ADMIN_PASSWORD) {
        $_SESSION['user'] = ['id' => 0, 'fio' => 'Администратор', 'login' => ADMIN_LOGIN];
        $_SESSION['is_admin'] = true;
        go('admin.php');
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE login = ?');
    $stmt->execute([$login]);
    $u = $stmt->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['user'] = ['id' => $u['id'], 'fio' => $u['fio'], 'login' => $u['login']];
        go('requests.php');
    }
    $error = 'Неверный логин или пароль.';
}

$title = 'Вход';
require 'header.php';
?>
<div class="d-flex justify-content-center align-items-center" style="min-height:70vh">
    <div class="w-100" style="max-width:400px">
        <h1 class="h3 mb-3 text-center">Вход</h1>
        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
        <form method="post" class="card p-4">
            <input class="form-control mb-2" name="login" placeholder="Логин" data-testid="login-login" required>
            <input class="form-control mb-2" type="password" name="password" placeholder="Пароль" data-testid="login-password" required>
            <button class="btn btn-primary w-100" data-testid="login-submit">Войти</button>
        </form>
        <p class="text-center mt-3 mb-0"><a href="register.php">Еще не зарегистрированы? Регистрация</a></p>
    </div>
</div>
<?php require 'footer.php'; ?>