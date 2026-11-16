<?php
require 'config.php';
if (!user()) go('login.php');
if (is_admin()) go('admin.php');

$rooms = $pdo->query('SELECT * FROM rooms ORDER BY type, title')->fetchAll();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)($_POST['room_id'] ?? 0);
    $date    = trim($_POST['start_date'] ?? '');
    $payment = $_POST['payment'] ?? '';

    if (!$room_id || !$date || !in_array($payment, ['Очное посещение', 'СБП'], true)) {
        $error = 'Заполните все поля корректно.';
    } else {
        $pdo->prepare('INSERT INTO requests (user_id, room_id, start_date, payment) VALUES (?, ?, ?, ?)')
            ->execute([user()['id'], $room_id, $date, $payment]);
        go('requests.php');
    }
}

$title = 'Новая заявка';
require 'header.php';
?>
<div class="d-flex justify-content-center">
    <div class="w-100" style="max-width:1100px">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h1 class="h3 mb-0">Формирование заявки</h1>
        </div>

        <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>

        <form method="post">
            <h2 class="h5 mb-2">1. Выберите помещение</h2>
            <div class="row g-3 mb-4">
                <?php foreach ($rooms as $r): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <label class="card h-100 shadow-sm border-0" style="cursor:pointer;">
                            <!-- Проверяем, существует ли файл, чтобы не было битых иконок -->
                            <?php $img_path = 'img/' . $r['image']; ?>
                            <?php if (!empty($r['image']) && file_exists($img_path)): ?>
                                <img src="<?= e($img_path) ?>" class="card-img-top"
                                     style="height:160px; object-fit:cover;" alt="<?= e($r['title']) ?>">
                            <?php else: ?>
                                <div class="bg-secondary-subtle d-flex align-items-center justify-content-center"
                                     style="height:160px; border-top-left-radius: var(--bs-card-border-radius); border-top-right-radius: var(--bs-card-border-radius);">
                                    <span class="text-secondary small">Нет фото</span>
                                </div>
                            <?php endif; ?>

                            <div class="card-body p-3 d-flex flex-column">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="room_id"
                                           value="<?= (int)$r['id'] ?>" id="room_<?= (int)$r['id'] ?>"
                                           data-testid="req-room" required>
                                    <label class="form-check-label fw-bold" for="room_<?= (int)$r['id'] ?>">
                                        <?= e($r['title']) ?>
                                    </label>
                                </div>
                                <span class="badge bg-secondary mt-auto align-self-start"><?= e($r['type']) ?></span>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <h2 class="h5 mb-2">2. Дата и оплата</h2>
            <div class="card p-4">
                <label class="form-label">Дата начала конференции</label>
                <input class="form-control mb-3" type="date" name="start_date" data-testid="req-date" required>

                <label class="form-label">Способ оплаты</label>
                <select class="form-select mb-3" name="payment" data-testid="req-payment" required>
                    <option>Очное посещение</option>
                    <option>СБП</option>
                </select>

                <button class="btn btn-primary w-100" data-testid="req-submit">Отправить</button>
            </div>
        </form>
        <p class="text-center mt-3 mb-0"><a href="requests.php">К моим заявкам</a></p>
    </div>
</div>
<?php require 'footer.php'; ?>