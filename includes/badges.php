<?php
require_once __DIR__ . '/db.php';

function ensure_badge($code, $name, $description = null) {
  $pdo = db();
  $st = $pdo->prepare("SELECT id FROM badges WHERE code = ?");
  $st->execute([$code]);
  $id = $st->fetchColumn();
  if (!$id) {
    $ins = $pdo->prepare("INSERT INTO badges (code, name, description) VALUES (?,?,?)");
    $ins->execute([$code, $name, $description]);
    $id = $pdo->lastInsertId();
  }
  return $id;
}

function award_badge($user_id, $code, $name, $description = null) {
  $pdo = db();
  $bid = ensure_badge($code, $name, $description);
  // check if already awarded
  $chk = $pdo->prepare("SELECT 1 FROM user_badges WHERE user_id = ? AND badge_id = ?");
  $chk->execute([$user_id, $bid]);
  if ($chk->fetchColumn()) return false;
  $ins = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?,?)");
  $ins->execute([$user_id, $bid]);
  return true;
}

function user_badges($user_id) {
  $pdo = db();
  $st = $pdo->prepare("SELECT b.code, b.name, b.description, ub.awarded_at FROM user_badges ub JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ? ORDER BY ub.awarded_at DESC");
  $st->execute([$user_id]);
  return $st->fetchAll();
}

/**
 * Rule engine for eco diary submissions and other activities.
 * Called after creating an entry or other actions.
 */
function check_and_award_for_entry($user_id) {
  $pdo = db();
  // total number of diary entries
  $cnt = $pdo->prepare("SELECT COUNT(*) FROM eco_entries WHERE user_id = ?");
  $cnt->execute([$user_id]);
  $n = (int)$cnt->fetchColumn();

  if ($n >= 1) award_badge($user_id, 'first_entry', 'Первый шаг', 'Первая запись в эко‑дневнике');
  if ($n >= 5) award_badge($user_id, 'five_entries', 'Пять поступков', 'Сделано 5 записей в эко‑дневник');
  if ($n >= 10) award_badge($user_id, 'ten_entries', 'Эко‑активист', 'Сделано 10 записей в эко‑дневник');

  // Thematic badge: пластик
  $plast = $pdo->prepare("SELECT COUNT(*) FROM eco_entries WHERE user_id=? AND type LIKE '%пластик%'");
  $plast->execute([$user_id]);
  if ((int)$plast->fetchColumn() >= 3) {
    award_badge($user_id, 'plastic_free', 'Меньше пластика', 'Три и более действий по отказу от одноразового пластика');
  }

  // Participation in events
  $evt = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE user_id=?");
  $evt->execute([$user_id]);
  if ((int)$evt->fetchColumn() >= 1) {
    award_badge($user_id, 'first_event', 'Вместе на природе', 'Участие хотя бы в одном эко‑событии');
  }

  // Learning: enrollments
  $crs = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id=?");
  $crs->execute([$user_id]);
  $c = (int)$crs->fetchColumn();
  if ($c >= 1) award_badge($user_id, 'first_course', 'Начал обучение', 'Запись хотя бы на один курс');
  if ($c >= 3) award_badge($user_id, 'three_courses', 'Любознательный', 'Запись на 3 курса');

}
