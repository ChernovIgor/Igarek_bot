-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Май 19 2020 г., 17:16
-- Версия сервера: 10.3.13-MariaDB-log
-- Версия PHP: 7.3.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- База данных: `chernog1_irarek`
--

-- --------------------------------------------------------

--
-- Структура таблицы `transatcions`
--

CREATE TABLE `transatcions` (
  `id` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(20) UNSIGNED NOT NULL COMMENT 'id клиента',
  `balance` int(10) UNSIGNED NOT NULL DEFAULT 200,
  `state` varchar(16) NOT NULL DEFAULT '0' COMMENT 'определяет положение бота',
  `bank` varchar(100) DEFAULT NULL,
  `tel` bigint(20) DEFAULT NULL,
  `bet` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы таблицы `transatcions`
--
ALTER TABLE `transatcions`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);
COMMIT;

