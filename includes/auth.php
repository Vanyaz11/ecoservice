<?php
require_once __DIR__ . '/db.php';
session_start();

function current_user() {
  return $_SESSION['user'] ?? null;
}

function require_login() {
  if (!current_user()) {
    header('Location: login.php');
    exit();
  }
}


function require_role($roles) {
  $u = current_user();
  if (!$u) {
    header('Location: login.php');
    exit();
  }
  if (is_string($roles)) $roles = [$roles];
  if (!in_array($u['role'], $roles, true)) {
    http_response_code(403);
    echo "Доступ запрещён";
    exit();
  }
}

function register_user($name, $email, $password) {
  $pdo = db();
  $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
  $stmt->execute([$email]);
  if ($stmt->fetch()) return "Пользователь с таким email уже существует";
  $hash = password_hash($password, PASSWORD_DEFAULT);
  $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?,?,?, 'student')");
  $stmt->execute([$name, $email, $hash]);
  return null;
}

function login_user($email, $password) {
  $pdo = db();
  $stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  if (!$user || !password_verify($password, $user['password_hash'])) {
    return "Неверный email или пароль";
  }
  $_SESSION['user'] = ['id'=>$user['id'], 'name'=>$user['name'], 'email'=>$user['email'], 'role'=>$user['role']];
  return null;
}

function logout_user() {
  $_SESSION = [];
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );
  }
  session_destroy();
}
