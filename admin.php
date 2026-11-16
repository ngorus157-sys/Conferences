<?php
require 'config.php';
if (!is_admin()) { http_response_code(403); exit('Доступ запрещён'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['status'])) {
    $allowed = ['Новая', 'Мероприятие назначено', 'Завершено'];
    if (in_array($_POST['status'], $allowed, true)) {
        $pdo->prepare('UPDATE requests SET status = ? WHERE id = ?')
            ->execute([$_POST['status'], (int)$_POST['request_id']]);
    }
    go('admin.php?' . http_build_query($_GET));
}

$filter_status = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = [];
$params = [];

if ($filter_status && in_array($filter_status, ['Новая', 'Мероприятие назначено', 'Завершено'], true)) {
    $where[] = 'r.status = ?';
    $params[] = $filter_status;
}

if ($search !== '') {
    $where[] = '(u.fio LIKE ? OR rm.title LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql = 'SELECT r.*, u.fio, rm.title AS room_title, rm.image AS room_image
        FROM requests r
        JOIN users u ON u.id = r.user_id
        JOIN rooms rm ON rm.id = r.room_id';

if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY r.id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$requests = $stmt->fetchAll();

$stats = $pdo->query('SELECT status, COUNT(*) as count FROM requests GROUP BY status')->fetchAll();
$stats_map = [];
foreach ($stats as $s) $stats_map[$s['status']] = $s['count'];

$reviews = $pdo->query(
    'SELECT rv.*, u.fio, rm.title AS room_title FROM reviews rv
     JOIN users u ON u.id = rv.user_id
     JOIN requests r ON r.id = rv.request_id
     JOIN rooms rm ON rm.id = r.room_id
     ORDER BY rv.id DESC'
)->fetchAll();

$title = 'Админка';
require 'header.php';
?>
<div class="d-flex justify-content-center">
    <div class="w-100" style="max-width:1100px">
        <div class="d-flex align-items-center justify-content-center gap-2 mb-4">
            <img src="img/logo2.png" alt="" style="height:36px; object-fit:contain;">
            <h1 class="h3 mb-0">Панель администратора</h1>
        </div>

        <div class="row g-2 mb-4 text-center">
            <div class="col-4">
                <div class="card p-2 border-0 shadow-sm">
                    <span class="text-secondary small">Новые</span>
                    <strong class="fs-5"><?= (int)($stats_map['Новая'] ?? 0) ?></strong>
                </div>
            </div>
            <div class="col-4">
                <div class="card p-2 border-0 shadow-sm">
                    <span class="text-secondary small">Назначено</span>
                    <strong class="fs-5"><?= (int)($stats_map['Мероприятие назначено'] ?? 0) ?></strong>
                </div>
            </div>
            <div class="col-4">
                <div class="card p-2 border-0 shadow-sm">
                    <span class="text-secondary small">Завершено</span>
                    <strong class="fs-5"><?= (int)($stats_map['Завершено'] ?? 0) ?></strong>
                </div>
            </div>
        </div>

        <form method="get" class="card p-3 mb-4 shadow-sm border-0">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label small mb-1">Поиск (ФИО / Помещение)</label>
                    <input type="text" name="search" class="form-control form-control-sm" 
                           value="<?= e($search) ?>" placeholder="Введите текст...">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small mb-1">Статус</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Все статусы</option>
                        <?php foreach (['Новая', 'Мероприятие назначено', 'Завершено'] as $s): ?>
                            <option value="<?= e($s) ?>" <?= $filter_status === $s ? 'selected' : '' ?>>
                                <?= e($s) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 d-flex gap-2">
                    <button class="btn btn-sm btn-primary w-100">Применить</button>
                    <a href="admin.php" class="btn btn-sm btn-outline-secondary w-100">Сбросить</a>
                </div>
            </div>
        </form>

        <h2 class="h5 mb-2">Все заявки <span class="text-secondary fs-6">(<?= count($requests) ?>)</span></h2>
        
        <?php if (!$requests): ?>
            <div class="alert alert-secondary text-center">По вашему запросу ничего не найдено.</div>
        <?php else: ?>
            <div class="table-responsive mb-5">
                <table class="table bg-white align-middle shadow-sm">
                    <tr>
                        <th>№</th><th>Пользователь</th><th>Помещение</th>
                        <th>Дата</th><th>Оплата</th><th>Статус</th>
                    </tr>
                    <?php foreach ($requests as $r): ?>
                        <tr data-testid="admin-row">
                            <td><?= (int)$r['id'] ?></td>
                            <td><?= e($r['fio']) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php $img_path = 'img/' . $r['room_image']; ?>
                                    <?php if (!empty($r['room_image']) && file_exists($img_path)): ?>
                                        <img src="<?= e($img_path) ?>" alt=""
                                             style="width:48px; height:48px; object-fit:cover; border-radius:.4rem;">
                                    <?php endif; ?>
                                    <span><?= e($r['room_title']) ?></span>
                                </div>
                            </td>
                            <td><?= e(date('d.m.Y', strtotime($r['start_date']))) ?></td>
                            <td><?= e($r['payment']) ?></td>
                            <td>
                                <form method="post" class="d-flex gap-1">
                                    <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                    <select name="status" class="form-select form-select-sm" data-testid="admin-status">
                                        <?php foreach (['Новая', 'Мероприятие назначено', 'Завершено'] as $s): ?>
                                            <option <?= $s === $r['status'] ? 'selected' : '' ?>><?= e($s) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-primary">OK</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>

        <h2 class="h5 mb-2">Отзывы</h2>
        <?php if (!$reviews): ?>
            <div class="alert alert-secondary text-center">Отзывов пока нет.</div>
        <?php else: ?>
            <table class="table bg-white shadow-sm">
                <tr><th>№</th><th>Пользователь</th><th>Помещение</th><th>Оценка</th><th>Текст</th></tr>
                <?php foreach ($reviews as $rv): ?>
                    <tr>
                        <td><?= (int)$rv['id'] ?></td>
                        <td><?= e($rv['fio']) ?></td>
                        <td><?= e($rv['room_title']) ?></td>
                        <td><?= (int)$rv['rating'] ?>/5</td>
                        <td><?= e($rv['text']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php require 'footer.php'; ?>