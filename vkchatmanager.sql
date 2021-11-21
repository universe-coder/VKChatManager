-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 21 2021 г., 15:14
-- Версия сервера: 5.7.20-log
-- Версия PHP: 7.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `vkchatmanager`
--

-- --------------------------------------------------------

--
-- Структура таблицы `bans`
--

CREATE TABLE `bans` (
  `id` int(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `chat_id` int(255) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `chats`
--

CREATE TABLE `chats` (
  `id` int(255) NOT NULL,
  `peer_id` int(255) NOT NULL,
  `is_active` int(255) NOT NULL,
  `last_active` int(255) NOT NULL,
  `date` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `chat_admins`
--

CREATE TABLE `chat_admins` (
  `id` int(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `chat_id` int(255) NOT NULL,
  `added_id` int(255) NOT NULL,
  `date` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `control_chats`
--

CREATE TABLE `control_chats` (
  `id` int(255) NOT NULL,
  `local_chat_id` int(255) NOT NULL,
  `security` int(11) NOT NULL DEFAULT '0',
  `links` int(11) NOT NULL DEFAULT '0',
  `invites` int(11) NOT NULL DEFAULT '0',
  `bots` int(11) DEFAULT '0',
  `nude_security` int(255) NOT NULL DEFAULT '0',
  `added_usr_id` int(255) NOT NULL,
  `date_add` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `kick_logs`
--

CREATE TABLE `kick_logs` (
  `id` int(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `admin_id` int(255) NOT NULL,
  `chat_id` int(255) NOT NULL,
  `date` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `kick_polls`
--

CREATE TABLE `kick_polls` (
  `id` int(255) NOT NULL,
  `chat_id` int(255) NOT NULL,
  `author_id` int(255) NOT NULL,
  `kick_usr_id` int(255) NOT NULL,
  `needed_votes` int(255) NOT NULL,
  `current_votes` int(255) NOT NULL,
  `reresolved` int(11) NOT NULL DEFAULT '0',
  `date_create` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `kick_poll_votes`
--

CREATE TABLE `kick_poll_votes` (
  `id` int(255) NOT NULL,
  `poll_id` int(255) NOT NULL,
  `author_id` int(255) NOT NULL,
  `date` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `last_activity`
--

CREATE TABLE `last_activity` (
  `id` int(255) NOT NULL,
  `user_id` int(255) NOT NULL,
  `chat_id` int(255) NOT NULL,
  `date_last_acivity` int(255) NOT NULL,
  `date` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `message_logs`
--

CREATE TABLE `message_logs` (
  `id` int(255) NOT NULL,
  `from_id` int(255) NOT NULL,
  `chat_id` int(255) NOT NULL,
  `text` text NOT NULL,
  `len_text` int(255) NOT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `proxy`
--

CREATE TABLE `proxy` (
  `id` int(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `port` varchar(255) NOT NULL,
  `protocol` varchar(255) NOT NULL,
  `country` varchar(255) NOT NULL,
  `is_active` int(255) NOT NULL DEFAULT '0',
  `date` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `bans`
--
ALTER TABLE `bans`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `chats`
--
ALTER TABLE `chats`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `chat_admins`
--
ALTER TABLE `chat_admins`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `control_chats`
--
ALTER TABLE `control_chats`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `kick_logs`
--
ALTER TABLE `kick_logs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `kick_polls`
--
ALTER TABLE `kick_polls`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `kick_poll_votes`
--
ALTER TABLE `kick_poll_votes`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `last_activity`
--
ALTER TABLE `last_activity`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `message_logs`
--
ALTER TABLE `message_logs`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `proxy`
--
ALTER TABLE `proxy`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `bans`
--
ALTER TABLE `bans`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chats`
--
ALTER TABLE `chats`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `chat_admins`
--
ALTER TABLE `chat_admins`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `control_chats`
--
ALTER TABLE `control_chats`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `kick_logs`
--
ALTER TABLE `kick_logs`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `kick_polls`
--
ALTER TABLE `kick_polls`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `kick_poll_votes`
--
ALTER TABLE `kick_poll_votes`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `last_activity`
--
ALTER TABLE `last_activity`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `message_logs`
--
ALTER TABLE `message_logs`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `proxy`
--
ALTER TABLE `proxy`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
