<?php
require 'config.php';
if (!user()) go('login.php');
if (is_admin()) go('admin.php');

$stmt = $pdo->prepare(
    'SELECT r.*, rm.title AS room_title, rm.image AS room_image
     FROM requests r
     JOIN rooms rm ON rm.id = r.room_id
     WHERE r.user_id = ? ORDER BY r.id DESC'
);
$stmt->execute([user()['id']]);
$requests = $stmt->fetchAll();

$title = 'Мои заявки';
require 'header.php';
?>
<div class="d-flex justify-content-center">
    <div class="w-100" style="max-width:1000px">
        <div class="d-flex align-items-center gap-2 mb-3">
            <img src="img/logo2.jpg" alt="" style="height:28px; object-fit:contain;">
            <h1 class="h3 mb-0">Мои заявки</h1>
        </div>

        <?php if (!$requests): ?>
            <div class="alert alert-secondary text-center">
                Заявок нет. <a href="request_create.php">Создать заявку</a>
            </div>
        <?php else: ?>
            <div class="table-responsive" data-testid="req-list">
                <table class="table bg-white align-middle">
                    <tr>
                        <th>№</th><th>Помещение</th><th>Дата</th>
                        <th>Оплата</th><th>Статус</th><th>Отзыв</th>
                    </tr>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td><?= (int)$r['id'] ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if (!empty($r['room_image'])): ?>
                                        <img src="img/<?= e($r['room_image']) ?>" alt=""
                                             style="width:56px; height:56px; object-fit:cover; border-radius:.4rem;">
                                    <?php endif; ?>
                                    <span><?= e($r['room_title']) ?></span>
                                </div>
                            </td>
                            <td><?= e(date('d.m.Y', strtotime($r['start_date']))) ?></td>
                            <td><?= e($r['payment']) ?></td>
                            <td><?= e($r['status']) ?></td>
                            <td>
                                <?php if ($r['status'] === 'Завершено'): ?>
                                    <form method="post" action="review.php" class="d-flex flex-column gap-1">
                                        <input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
                                        <textarea class="form-control form-control-sm" name="text" rows="2"
                                                  placeholder="Отзыв" data-testid="req-review" required></textarea>
                                        <select class="form-select form-select-sm" name="rating">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                                <option value="<?= $i ?>"><?= $i ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <button class="btn btn-sm btn-primary">Оставить отзыв</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-secondary small">После завершения</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php require 'footer.php'; ?>