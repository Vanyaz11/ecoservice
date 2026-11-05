</main>
<footer class="footer">
  <div class="container">
    <p>© <?php echo date('Y'); ?> Московский университет имени С. Ю. Витте, Научно-образовательный центр устойчивого развития</p>
    <p class="small">Веб-сервис формирования экологической культуры обучающихся. Автор работы: <?php echo h(app_config()['app']['author'] ?? ''); ?>. <a href="<?php echo url('help.php'); ?>">Справка</a></p>
  </div>
</footer>
</body>
</html>
