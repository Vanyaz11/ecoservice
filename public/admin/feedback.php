<?php
$pageTitle = 'Обращения';
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/roles.php';
require_admin();
require __DIR__ . '/_nav.php';
$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status']) && in_array($_POST['status'], ['new','in_progress','closed'], true)) {
  $pdo->prepare("UPDATE feedback SET status = ? WHERE id = ?")->execute([$_POST['status'], (int)$_POST['id']]);
  header('Location: ' . url('admin/feedback.php')); exit;
}
$filter = $_GET['status'] ?? '';
$st = $pdo->prepare("SELECT f.*, u.name AS user_name FROM feedback f LEFT JOIN users u ON u.id = f.user_id WHERE (? = '' OR f.status = ?) ORDER BY f.status = 'new' DESC, f.created_at DESC");
$st->execute([$filter, $filter]);
$rows = $st->fetchAll();
$labels = ['new' => 'новое', 'in_progress' => 'в работе', 'closed' => 'закрыто'];
?>
<div class="card">
  <h3>Обращения через форму обратной связи</h3>
  <p>
    <a class="btn <?php echo $filter===''?'':'secondary'; ?>" href="<?php echo url('admin/feedback.php'); ?>">Все</a>
    <?php foreach ($labels as $k => $v): ?><a class="btn <?php echo $filter===$k?'':'secondary'; ?>" href="<?php echo url('admin/feedback.php?status='.$k); ?>"><?php echo $v; ?></a><?php endforeach; ?>
  </p>
  <?php if (!$rows): ?><p class="small">Обращений нет.</p><?php endif; ?>
  <?php foreach ($rows as $r): ?>
    <div class="card">
      <div style="display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px">
        <div><b><?php echo h($r['name']); ?></b> &lt;<?php echo h($r['email']); ?>&gt; <?php if ($r['user_name']): ?><span class="small">(пользователь: <?php echo h($r['user_name']); ?>)</span><?php endif; ?></div>
        <div class="small"><?php echo date('d.m.Y H:i', strtotime($r['created_at'])); ?> · <span class="badge"><?php echo $labels[$r['status']]; ?></span></div>
      </div>
      <p><?php echo nl2br(h($r['message'])); ?></p>
      <form method="post" style="display:flex;gap:6px;align-items:center">
        <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
        <select class="input" name="status" style="width:auto"><?php foreach ($labels as $k=>$v): ?><option value="<?php echo $k; ?>" <?php echo $r['status']===$k?'selected':''; ?>><?php echo $v; ?></option><?php endforeach; ?></select>
        <button class="btn secondary" type="submit">Изменить статус</button>
        <a class="btn secondary" href="mailto:<?php echo h($r['email']); ?>?subject=Ответ на обращение в Эко-сервис">Ответить письмом</a>
      </form>
    </div>
  <?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
