-- phpMyAdmin SQL Dump
-- version 4.0.6deb1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Фев 03 2014 г., 05:21
-- Версия сервера: 5.5.35-0ubuntu0.13.10.1
-- Версия PHP: 5.5.3-1ubuntu2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `cheferee`
--
CREATE DATABASE IF NOT EXISTS `cheferee` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `cheferee`;

-- --------------------------------------------------------

--
-- Структура таблицы `grid`
--

DROP TABLE IF EXISTS `grid`;
CREATE TABLE IF NOT EXISTS `grid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pairId` int(11) NOT NULL,
  `playerId` int(11) NOT NULL,
  `startElo` int(4) NOT NULL,
  `startScore` int(4) NOT NULL COMMENT 'startScore x10',
  `color` enum('black','white') NOT NULL,
  `lastColor` enum('black','white') NOT NULL,
  `lastColorCount` int(2) NOT NULL,
  `tour` int(4) NOT NULL,
  `scoreGroup` int(4) NOT NULL COMMENT 'scoreGroup x10',
  `rivalId` int(11) NOT NULL,
  `rivalElo` int(4) NOT NULL,
  `tourDone` int(1) NOT NULL DEFAULT '0' COMMENT 'Tour done flag',
  `resultScore` int(4) NOT NULL COMMENT 'resultScore x10',
  `resultElo` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `playerIdTour` (`playerId`,`tour`) COMMENT 'one player one tour',
  UNIQUE KEY `pairIdPlayerId` (`pairId`,`playerId`) COMMENT 'one pair one player',
  KEY `tourTourDone` (`tour`,`tourDone`) COMMENT 'tour and tour done'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Очистить таблицу перед добавлением данных `grid`
--

TRUNCATE TABLE `grid`;
--
-- Триггеры `grid`
--
DROP TRIGGER IF EXISTS `deleteRival`;
DELIMITER //
CREATE TRIGGER `deleteRival` AFTER DELETE ON `grid`
 FOR EACH ROW DELETE FROM `rival` WHERE `playerId` = OLD.`playerId` and `rivalId` = OLD.`rivalId`
//
DELIMITER ;

-- --------------------------------------------------------

--
-- Структура таблицы `player`
--

DROP TABLE IF EXISTS `player`;
CREATE TABLE IF NOT EXISTS `player` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(256) NOT NULL,
  `nickname` varchar(256) NOT NULL,
  `birthyear` year(4) NOT NULL,
  `elo` int(11) NOT NULL,
  `logo` varchar(1024) NOT NULL,
  `leave` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Registred players' AUTO_INCREMENT=11 ;

--
-- Очистить таблицу перед добавлением данных `player`
--

TRUNCATE TABLE `player`;
--
-- Дамп данных таблицы `player`
--

INSERT INTO `player` (`id`, `name`, `nickname`, `birthyear`, `elo`, `logo`, `leave`) VALUES
(1, 'Andriasian Zaven', 'Andriasian Zaven', 1989, 2611, '', 0),
(2, 'Dragun Kamil', 'Dragun Kamil', 1995, 2517, '', 0),
(3, 'Gordievsky Dmitry', 'Gordievsky Dmitry', 1996, 2444, '', 0),
(4, 'Belous Vladimir', 'Belous Vladimir', 1993, 2569, '', 0),
(5, 'Eliseev Urii', 'Eliseev Urii', 1996, 1996, '', 0),
(6, 'Artemiev Vladislav', 'Artemiev Vladislav', 1998, 2595, '', 0),
(7, 'Vavulin Maksim', 'Vavulin Maksim', 1998, 2390, '', 0),
(8, 'Bernadskiy Vitaliy', 'Bernadskiy Vitaliy', 1994, 2565, '', 0),
(9, 'Stukopin Andrey', 'Stukopin Andrey', 1994, 2517, '', 0),
(10, 'Bajarani Ulvi', 'Bajarani Ulvi', 1995, 2504, '', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `rival`
--

DROP TABLE IF EXISTS `rival`;
CREATE TABLE IF NOT EXISTS `rival` (
  `playerId` int(11) NOT NULL,
  `rivalId` int(11) NOT NULL,
  PRIMARY KEY (`playerId`,`rivalId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='pair already playing';

--
-- Очистить таблицу перед добавлением данных `rival`
--

TRUNCATE TABLE `rival`;
-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` char(64) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='referee users' AUTO_INCREMENT=2 ;

--
-- Очистить таблицу перед добавлением данных `user`
--

TRUNCATE TABLE `user`;
--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `type`, `timestamp`) VALUES
(1, 'admin', '$2a$13$Abf3kj5iW02YH/.KpTgcTuZzGKpNF0snffZjuL3UHHweELgPR13/u', 0, '0000-00-00 00:00:00');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
