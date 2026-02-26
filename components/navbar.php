<?php
$menu = require __DIR__ . '/../includes/menu.php';
$user_role = $_SESSION['role'] ?? 'student';

function allowed_item($item, $role) {
  if (!isset($item['roles'])) return true;
  return in_array($role, $item['roles'], true);
}
?>
<header class="topbar">
  <div class="brand"><a href="/index.php">EcoService</a></div>
  <nav class="mainmenu" aria-label="Основное меню">
    <ul>
      <?php foreach($menu as $it): if(!allowed_item($it, $user_role)) continue; ?>
        <li><a href="<?= htmlspecialchars($it['url']) ?>"><?= htmlspecialchars($it['title']) ?></a></li>
      <?php endforeach; ?>
    </ul>
  </nav>
</header>
<style>
.topbar { display:flex; align-items:center; gap:16px; padding:10px 16px; background:#0b3d2e; color:#fff; }
.topbar .brand a { color:#fff; text-decoration:none; font-weight:700; }
.mainmenu ul { list-style:none; display:flex; gap:12px; margin:0; padding:0; }
.mainmenu a { color:#e6ffe6; text-decoration:none; padding:6px 10px; border-radius:8px; }
.mainmenu a:hover { background:#115c45; }
.sidebar { width:260px; background:#f6fbf7; border-right:1px solid #d9e8df; padding:10px; }
.sidebar ul { list-style:none; padding:0; margin:0; }
.sidebar a { display:block; padding:8px 10px; border-radius:8px; color:#0b3d2e; text-decoration:none; }
.sidebar a:hover { background:#e1f3ea; }
.breadcrumbs { padding:8px 16px; font-size:14px; }
.breadcrumbs ol { list-style:none; display:flex; flex-wrap:wrap; gap:6px; margin:0; padding:0; }
.breadcrumbs li::after { content:"/"; margin:0 6px; color:#6a8f7f; }
.breadcrumbs li.active::after { content:""; }
.breadcrumbs a { color:#0b3d2e; text-decoration:none; }
</style>
