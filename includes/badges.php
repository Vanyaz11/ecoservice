<?php
require_once __DIR__ . '/db.php';

function ensure_badge($code, $name, $description = null) {
  $pdo = db();
  $st = $pdo->prepare("SELECT id FROM badges WHERE code = ?");
  $st->execute([$code]);
  $id = $st->fetchColumn();
  if (!$id) {
    $ins = $pdo->prepare("INSERT INTO badges (code, name, description) VALUES (?,?,?) RETURNING id");
    $ins->execute([$code, $name, $description]);
    $id = $ins->fetchColumn();
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
  $ins = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?,?) ON CONFLICT DO NOTHING");
  $ins->execute([$user_id, $bid]);
  return $ins->rowCount() > 0 ? $name : false;
}

function user_badges($user_id) {
  $pdo = db();
  $st = $pdo->prepare("SELECT b.code, b.name, b.description, ub.awarded_at FROM user_badges ub JOIN badges b ON b.id = ub.badge_id WHERE ub.user_id = ? ORDER BY ub.awarded_at DESC");
  $st->execute([$user_id]);
  return $st->fetchAll();
}

/**
 * Правила начисления бейджей. Вызывается после добавления эко-записи,
 * записи на курс или мероприятие. Повторная выдача исключена в award_badge().
 */
function check_and_award_for_entry($user_id) {
  $pdo = db();
  $new = [];
  $give = function($code, $name, $desc) use ($user_id, &$new) { if ($n = award_badge($user_id, $code, $name, $desc)) $new[] = $n; };
  // общее число записей в эко-дневнике
  $cnt = $pdo->prepare("SELECT COUNT(*) FROM eco_entries WHERE user_id = ?");
  $cnt->execute([$user_id]);
  $n = (int)$cnt->fetchColumn();

  if ($n >= 1) $give('first_entry', 'Первый шаг', 'Первая запись в эко‑дневнике');
  if ($n >= 5) $give('five_entries', 'Пять поступков', 'Сделано 5 записей в эко‑дневник');
  if ($n >= 10) $give('ten_entries', 'Эко‑активист', 'Сделано 10 записей в эко‑дневник');

  // тематический бейдж: отказ от пластика
  $plast = $pdo->prepare("SELECT COUNT(*) FROM eco_entries WHERE user_id=? AND type ILIKE '%пластик%'");
  $plast->execute([$user_id]);
  if ((int)$plast->fetchColumn() >= 3) {
    $give('plastic_free', 'Меньше пластика', 'Три и более действий по отказу от одноразового пластика');
  }

  // участие в мероприятиях
  $evt = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE user_id=?");
  $evt->execute([$user_id]);
  if ((int)$evt->fetchColumn() >= 1) {
    $give('first_event', 'Вместе на природе', 'Участие хотя бы в одном эко‑событии');
  }

  // обучение: записи на курсы
  $crs = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE user_id=?");
  $crs->execute([$user_id]);
  $c = (int)$crs->fetchColumn();
  if ($c >= 1) $give('first_course', 'Начал обучение', 'Запись хотя бы на один курс');
  if ($c >= 3) $give('three_courses', 'Любознательный', 'Запись на 3 курса');
  return $new;
}

/** Проверка условий после записи на мероприятие (тот же набор правил). */
function check_and_award_for_event($user_id) {
  return check_and_award_for_entry($user_id);
}

/** Проверка условий после записи на курс. */
function check_and_award_for_course($user_id) {
  return check_and_award_for_entry($user_id);
}
