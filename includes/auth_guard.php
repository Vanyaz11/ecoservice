<?php
// Заглушка авторизации. В реальном проекте замените проверкой сессии.
if (!isset($_SESSION['user_id'])) {
  // демо-данные: студент с id=1
  $_SESSION['user_id'] = 1;
  $_SESSION['role'] = 'student'; // values: student|teacher|admin
}
