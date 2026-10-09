-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Окт 09 2026 г., 16:51
-- Версия сервера: 5.7.39
-- Версия PHP: 7.4.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `ecoservice`
--

-- --------------------------------------------------------

--
-- Структура таблицы `badges`
--

CREATE TABLE `badges` (
  `id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `badges`
--

INSERT INTO `badges` (`id`, `code`, `name`, `description`, `created_at`) VALUES
(1, 'FIRST_ENTRY', 'Первый шаг', 'Первая запись в эко-дневнике', '2026-09-11 20:10:00'),
(2, 'EVENT_HELPER', 'Помощник событий', 'Участие хотя бы в одном эко-событии', '2026-09-11 20:10:00'),
(3, 'WASTE_SORTER_10', 'Сортировщик-10', '10 и более записей о сортировке отходов', '2026-09-11 20:10:00'),
(4, 'five_entries', 'Пять поступков', 'Сделано 5 записей в эко‑дневник', '2026-10-09 12:54:20'),
(5, 'ten_entries', 'Эко‑активист', 'Сделано 10 записей в эко‑дневник', '2026-10-09 12:54:20'),
(6, 'plastic_free', 'Меньше пластика', 'Три и более действий по отказу от одноразового пластика', '2026-10-09 12:54:20'),
(7, 'first_event', 'Вместе на природе', 'Участие хотя бы в одном эко‑событии', '2026-10-09 12:54:20'),
(8, 'first_course', 'Начал обучение', 'Запись хотя бы на один курс', '2026-10-09 12:54:20'),
(9, 'three_courses', 'Любознательный', 'Запись на 3 курса', '2026-10-09 12:54:20');

-- --------------------------------------------------------

--
-- Структура таблицы `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `short_desc` varchar(500) DEFAULT NULL,
  `content` mediumtext,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `courses`
--

INSERT INTO `courses` (`id`, `title`, `short_desc`, `content`, `created_at`) VALUES
(1, 'Введение в экологическую культуру', 'Базовый курс о принципах устойчивого развития', '<p>Этот курс знакомит с основами экологии и устойчивого развития. Тесты и задания будут добавлены позже.</p>', '2025-09-10 17:46:20'),
(2, 'Раздельный сбор отходов', 'Практики сортировки и переработки', '<p>Узнайте, как организовать раздельный сбор дома и в университете.</p>', '2025-09-10 17:46:20');

-- --------------------------------------------------------

--
-- Структура таблицы `course_sections`
--

CREATE TABLE `course_sections` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` mediumtext,
  `position` int(11) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `eco_entries`
--

CREATE TABLE `eco_entries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` varchar(120) NOT NULL,
  `value` decimal(10,2) DEFAULT '0.00',
  `note` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `eco_entries`
--

INSERT INTO `eco_entries` (`id`, `user_id`, `type`, `value`, `note`, `created_at`) VALUES
(2, 2, 'Сортировка отходов', '12.00', 'Батарейки', '2025-09-11 18:45:50'),
(3, 1, 'Сортировка отходов', '10.00', 'Произведена сортировка', '2026-10-09 12:47:25'),
(4, 1, 'Сортировка отходов', '10.00', 'Утилизировано 10 батареек', '2026-10-09 12:47:40'),
(5, 1, 'Мобильность без авто', '12.00', 'Пройдено без машины', '2026-10-09 12:47:58');

-- --------------------------------------------------------

--
-- Структура таблицы `enrollments`
--

CREATE TABLE `enrollments` (
  `user_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrolled_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `enrollments`
--

INSERT INTO `enrollments` (`user_id`, `course_id`, `enrolled_at`) VALUES
(2, 2, '2025-09-10 17:50:53');

-- --------------------------------------------------------

--
-- Структура таблицы `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `location` varchar(200) DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `seats` int(11) NOT NULL DEFAULT '100',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `location`, `starts_at`, `seats`, `created_at`) VALUES
(1, 'Субботник во дворе главного корпуса', 'Убираем территорию и высаживаем деревья.', 'Главный корпус, вход А', '2026-09-24 20:46:20', 60, '2026-09-10 17:21:20'),
(2, 'Лекция «Энергосбережение дома»', 'Практические советы по экономии энергии.', 'Аудитория 3-12', '2026-10-01 20:46:20', 120, '2026-09-10 17:46:20');

-- --------------------------------------------------------

--
-- Структура таблицы `event_registrations`
--

CREATE TABLE `event_registrations` (
  `user_id` int(11) NOT NULL,
  `event_id` int(11) NOT NULL,
  `registered_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `event_registrations`
--

INSERT INTO `event_registrations` (`user_id`, `event_id`, `registered_at`) VALUES
(2, 1, '2025-09-11 18:45:55');

-- --------------------------------------------------------

--
-- Структура таблицы `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `section_id` int(11) DEFAULT NULL,
  `title` varchar(200) NOT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `type` enum('link','file','video') NOT NULL DEFAULT 'link',
  `url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `uploaded_by` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `materials`
--

INSERT INTO `materials` (`id`, `course_id`, `section_id`, `title`, `description`, `type`, `url`, `created_at`, `uploaded_by`) VALUES
(1, 1, NULL, 'Памятка по устойчивому развитию', 'Краткое руководство по ключевым принципам', 'link', 'https://example.org/sd-memo', '2025-09-11 20:00:00', 0),
(2, 2, NULL, 'Плакат по раздельному сбору', 'Инфографика по сортировке отходов', 'file', '/uploads/materials/sorting-poster.pdf', '2025-09-11 20:05:00', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('student','admin','teacher') NOT NULL DEFAULT 'student',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'Петров А.Г.', 'petrov@mail.ru', '$2y$12$lXRhO5l4WIzvcllu8X2kOuYG6dYmDnIcziwhUML/pNeNB3H/PPT6C', 'student', '2025-09-10 17:49:49'),
(2, 'Самойленко А.С.', 'ya@mail.ru', '$2y$12$lXRhO5l4WIzvcllu8X2kOuYG6dYmDnIcziwhUML/pNeNB3H/PPT6C', 'teacher', '2025-09-10 17:50:31'),
(3, 'Сидоров А.Н.', 'sidorov@mail.ru', '$2y$12$lXRhO5l4WIzvcllu8X2kOuYG6dYmDnIcziwhUML/pNeNB3H/PPT6C', 'admin', '2025-09-11 19:33:33');

-- --------------------------------------------------------

--
-- Структура таблицы `user_badges`
--

CREATE TABLE `user_badges` (
  `user_id` int(11) NOT NULL,
  `badge_id` int(11) NOT NULL,
  `awarded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `user_badges`
--

INSERT INTO `user_badges` (`user_id`, `badge_id`, `awarded_at`) VALUES
(1, 1, '2026-10-09 12:47:25'),
(2, 2, '2025-09-11 20:15:00');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `badges`
--
ALTER TABLE `badges`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Индексы таблицы `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `course_sections`
--
ALTER TABLE `course_sections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Индексы таблицы `eco_entries`
--
ALTER TABLE `eco_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`user_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Индексы таблицы `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD PRIMARY KEY (`user_id`,`event_id`),
  ADD KEY `event_id` (`event_id`);

--
-- Индексы таблицы `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Индексы таблицы `user_badges`
--
ALTER TABLE `user_badges`
  ADD PRIMARY KEY (`user_id`,`badge_id`),
  ADD KEY `badge_id` (`badge_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `badges`
--
ALTER TABLE `badges`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT для таблицы `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `course_sections`
--
ALTER TABLE `course_sections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `eco_entries`
--
ALTER TABLE `eco_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `course_sections`
--
ALTER TABLE `course_sections`
  ADD CONSTRAINT `course_sections_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `eco_entries`
--
ALTER TABLE `eco_entries`
  ADD CONSTRAINT `eco_entries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `event_registrations`
--
ALTER TABLE `event_registrations`
  ADD CONSTRAINT `event_registrations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `event_registrations_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `course_sections` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ограничения внешнего ключа таблицы `user_badges`
--
ALTER TABLE `user_badges`
  ADD CONSTRAINT `user_badges_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_badges_ibfk_2` FOREIGN KEY (`badge_id`) REFERENCES `badges` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
