-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- 호스트: localhost
-- 처리한 시간: 13-07-23 14:45
-- 서버 버전: 5.1.41-community
-- PHP 버전: 5.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 데이터베이스: `pmc_test`
--
CREATE DATABASE IF NOT EXISTS `pmc_test` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `pmc_test`;

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_article`
--

CREATE TABLE IF NOT EXISTS `pmc_article` (
  `no` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `board_id` int(11) unsigned NOT NULL,
  `category` varchar(20) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `writer_id` int(11) unsigned NOT NULL,
  `top_no` int(11) unsigned DEFAULT NULL,
  `order_key` tinytext,
  `is_secret` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_notice` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `allow_comment` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `upload_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  `files` tinytext,
  PRIMARY KEY (`no`),
  KEY `board_id` (`board_id`),
  KEY `writer_id` (`writer_id`),
  KEY `top_no` (`top_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=16 ;

--
-- 테이블의 덤프 데이터 `pmc_article`
--

INSERT INTO `pmc_article` (`no`, `board_id`, `category`, `title`, `content`, `writer_id`, `top_no`, `order_key`, `is_secret`, `is_notice`, `allow_comment`, `upload_time`, `hits`, `files`) VALUES
(1, 1, NULL, '안녕하세요. 게시판을 오픈했습니다', '컨텐츠', 1, 1, NULL, 0, 1, 1, '2013-03-06 08:40:14', 0, NULL),
(2, 1, NULL, '알려드립니다', 'ㅇㅇ', 1, 2, NULL, 0, 0, 1, '2013-03-20 12:51:29', 0, NULL),
(3, 1, NULL, '질문있습니다.', 'ㅇㅁㄴㅇ', 1, 2, 'AA', 0, 0, 1, '2013-03-20 12:52:47', 0, NULL),
(4, 1, NULL, '저도있습니다!', 'ㅇㅁㄴ', 1, 2, 'AAAA', 0, 0, 1, '2013-03-24 12:36:57', 0, NULL),
(6, 1, '안내', '게시판 안내', 'ㅇㅁㄴㅇㅁ', 1, 6, NULL, 0, 0, 1, '2013-03-24 12:41:40', 0, NULL),
(9, 1, NULL, '운영진이 알립니다', 'dasdasd', 1, 9, NULL, 0, 0, 1, '2013-03-24 14:04:09', 0, NULL),
(12, 1, NULL, 'ㅁㄴㅇㅁㄴㅇㅁㄴㅇ', 'ㅁㄴ', 1, 12, NULL, 0, 0, 1, '2013-03-24 14:25:35', 0, NULL),
(13, 1, NULL, 'ㅇㅁㅇㅁㄴㅁㅇ2', 'ㅇㅇㅁ', 1, 13, NULL, 0, 0, 1, '2013-03-24 14:28:44', 0, NULL),
(14, 1, NULL, 'ㅇㅁㄴㅇㅁㄴㅇㅁㅇ2', '', 1, 14, NULL, 0, 0, 1, '2013-03-24 14:30:18', 0, NULL),
(15, 1, NULL, 'ㅇㅁㅇㅁㄴㅇㅁㄴㅇㅁ3', '', 1, 15, NULL, 0, 0, 1, '2013-06-21 13:59:28', 0, NULL);

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_article_comment`
--

CREATE TABLE IF NOT EXISTS `pmc_article_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_no` int(11) unsigned NOT NULL,
  `content` tinytext NOT NULL,
  `writer_id` int(11) unsigned NOT NULL,
  `regtime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `writer_id` (`writer_id`),
  KEY `article_no` (`article_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- 테이블의 덤프 데이터 `pmc_article_comment`
--

INSERT INTO `pmc_article_comment` (`id`, `article_no`, `content`, `writer_id`, `regtime`) VALUES
(1, 2, 'ㅇㅁㄴㅇ', 1, '2013-03-20 12:51:39');

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_board`
--

CREATE TABLE IF NOT EXISTS `pmc_board` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `name_locales` tinytext NOT NULL,
  `categorys` tinytext,
  `read_permission` tinytext,
  `comment_permission` tinytext,
  `write_permission` tinytext,
  `extra_vars` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_en` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 테이블의 덤프 데이터 `pmc_board`
--

INSERT INTO `pmc_board` (`id`, `name`, `name_locales`, `categorys`, `read_permission`, `comment_permission`, `write_permission`, `extra_vars`) VALUES
(1, 'freeboard', '{"en":"Freeboard", "kr":"자유게시판"}', NULL, '["*"]', '["*"]', '["*"]', NULL),
(2, 'notice', '{"en":"Notice", "kr":"공지사항"}', NULL, '["*"]', '["*"]', '["*"]', NULL);

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_login_log`
--

CREATE TABLE IF NOT EXISTS `pmc_login_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `input_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `succeed` tinyint(1) NOT NULL,
  `auto_login` tinyint(1) NOT NULL,
  `login_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=44 ;

--
-- 테이블의 덤프 데이터 `pmc_login_log`
--

INSERT INTO `pmc_login_log` (`id`, `ip_address`, `input_id`, `succeed`, `auto_login`, `login_time`) VALUES
(1, '127.0.0.1', 'tester', 1, 1, '2013-07-18 15:46:22'),
(2, '127.0.0.1', 'tester', 1, 1, '2013-07-18 18:23:14'),
(3, '127.0.0.1', 'tester', 1, 1, '2013-07-18 18:30:56'),
(4, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:06:07'),
(5, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:23:08'),
(6, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:30:12'),
(7, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:30:45'),
(8, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:31:03'),
(9, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:31:55'),
(10, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:32:32'),
(11, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:33:09'),
(12, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:36:57'),
(13, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:44:10'),
(14, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:45:07'),
(15, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:45:24'),
(16, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:45:52'),
(17, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:49:05'),
(18, '127.0.0.1', 'tester', 1, 1, '2013-07-21 03:59:23'),
(19, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:14:51'),
(20, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:16:33'),
(21, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:16:45'),
(22, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:17:14'),
(23, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:17:39'),
(24, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:18:00'),
(25, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:19:22'),
(26, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:19:41'),
(27, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:21:32'),
(28, '127.0.0.1', 'tester', 1, 1, '2013-07-21 04:53:43'),
(29, '127.0.0.1', 'tester', 1, 1, '2013-07-21 05:03:22'),
(30, '127.0.0.1', 'tester', 1, 1, '2013-07-21 05:04:37'),
(31, '127.0.0.1', 'tester', 1, 1, '2013-07-21 05:08:38'),
(32, '127.0.0.1', 'tester', 1, 1, '2013-07-21 05:10:17'),
(33, '127.0.0.1', 'tester', 1, 1, '2013-07-21 09:09:15'),
(34, '127.0.0.1', 'tester', 1, 1, '2013-07-21 10:30:23'),
(35, '127.0.0.1', 'tester', 1, 1, '2013-07-21 10:35:21'),
(36, '127.0.0.1', 'tester', 1, 1, '2013-07-21 10:39:15'),
(37, '127.0.0.1', 'tester', 1, 1, '2013-07-21 11:16:28'),
(38, '127.0.0.1', 'tester', 1, 1, '2013-07-21 11:30:18'),
(39, '127.0.0.1', 'tester', 1, 1, '2013-07-21 11:30:40'),
(40, '127.0.0.1', 'tester', 1, 1, '2013-07-21 11:30:47'),
(41, '127.0.0.1', 'tester', 1, 1, '2013-07-21 11:33:53'),
(42, '127.0.0.1', 'tester', 1, 1, '2013-07-23 04:10:16'),
(43, '127.0.0.1', 'tester', 1, 1, '2013-07-23 05:45:02');

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_menu`
--

CREATE TABLE IF NOT EXISTS `pmc_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `title_locales` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `level` tinyint(1) unsigned NOT NULL,
  `is_index` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `parent_id` int(10) unsigned DEFAULT NULL,
  `css_property` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `css_hover_property` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `css_active_property` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `module` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `action` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  `extra_vars` tinytext CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- 테이블의 덤프 데이터 `pmc_menu`
--

INSERT INTO `pmc_menu` (`id`, `title`, `title_locales`, `level`, `is_index`, `visible`, `parent_id`, `css_property`, `css_hover_property`, `css_active_property`, `module`, `action`, `extra_vars`) VALUES
(1, 'home', '{"en":"Home", "kr":"홈"}', 1, 1, 1, NULL, NULL, NULL, NULL, 'index', NULL, NULL),
(2, 'notice', '{"en":"Notice", "kr":"공지사항"}', 1, 0, 1, NULL, NULL, NULL, NULL, 'board', NULL, NULL),
(3, 'freeboard', '{"en":"Free Board", "kr":"자유게시판"}', 1, 0, 1, NULL, NULL, NULL, NULL, 'board', NULL, NULL),
(4, 'others', '{"en":"Others", "kr":"기타"}', 1, 0, 1, NULL, NULL, NULL, NULL, 'page', NULL, NULL),
(5, 'about', '{"en":"About", "kr":"정보"}', 2, 0, 1, 4, NULL, NULL, NULL, 'page', NULL, NULL),
(6, 'libraries', '{"en":"Libraries", "kr":"라이브러리"}', 2, 0, 1, 4, NULL, NULL, NULL, 'page', NULL, NULL);

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_session`
--

CREATE TABLE IF NOT EXISTS `pmc_session` (
  `session_key` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `expire_time` datetime NOT NULL,
  `ip_address` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(10) unsigned NOT NULL,
  `extra_vars` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`session_key`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 테이블의 덤프 데이터 `pmc_session`
--

INSERT INTO `pmc_session` (`session_key`, `expire_time`, `ip_address`, `last_update`, `user_id`, `extra_vars`) VALUES
('5d0eb843cf5e9585da0b0dfd80c3bd818b9a60e4', '2013-07-28 13:19:22', '127.0.0.1', '2013-07-21 04:19:22', 1, NULL),
('62f298848a62db8696399a3e953e45a6696c1d52', '2013-07-28 12:59:23', '127.0.0.1', '2013-07-21 03:59:23', 1, NULL),
('6ccac2c6b4482c0373828b5208454f1e5c107dff', '2013-07-30 14:45:02', '127.0.0.1', '2013-07-23 05:45:02', 2, NULL),
('71d60ee6726197fb4d6f36673667bd5ed733ec54', '2013-07-28 14:03:22', '127.0.0.1', '2013-07-21 05:03:22', 1, NULL),
('89660b0720a7b83db7d6469fe5c6ecfdfa5b499d', '2013-07-28 13:16:45', '127.0.0.1', '2013-07-21 04:16:45', 1, NULL),
('89b2bd27cb754f9f23d47db8cd4d6719d995694c', '2013-07-28 13:53:43', '127.0.0.1', '2013-07-21 04:53:43', 1, NULL),
('8ca4f864af8616fb1a7b32c73d4976acaae1ba27', '2013-07-28 13:17:39', '127.0.0.1', '2013-07-21 04:17:39', 1, NULL),
('8e3e88e7b66bbc89ca3749eadb92ce9659729fad', '2013-07-28 13:21:32', '127.0.0.1', '2013-07-21 04:21:32', 1, NULL),
('8fa6ae04b486011c997fd807a7b6220a3629f66b', '2013-07-26 03:30:56', '127.0.0.1', '2013-07-18 18:30:56', 1, NULL),
('996df65f83663ff256e6a1618bc1788f79b4a6f0', '2013-07-28 13:18:00', '127.0.0.1', '2013-07-21 04:18:00', 1, NULL),
('a9156d598c06f8685c69437744868fb2a560d640', '2013-07-28 13:14:51', '127.0.0.1', '2013-07-21 04:14:51', 1, NULL),
('dd9aadd1286b9167d6cc0f854b515c71521ae0fc', '2013-07-28 13:17:14', '127.0.0.1', '2013-07-21 04:17:14', 1, NULL),
('e4cc72d2f6cbdb70a1f8ddbb42fbbcb515b60ab6', '2013-07-28 13:16:33', '127.0.0.1', '2013-07-21 04:16:33', 1, NULL),
('e73589952b1446996adea4af73037d6dd4c486a1', '2013-07-28 13:19:41', '127.0.0.1', '2013-07-21 04:19:41', 1, NULL);

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_user`
--

CREATE TABLE IF NOT EXISTS `pmc_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `input_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'SHA256',
  `password_salt` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'MD5',
  `nick_name` varchar(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `user_name` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `email_address` varchar(60) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `phone_number` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `last_logined_ip` varchar(15) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `extra_vars` text CHARACTER SET utf8 COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`input_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 테이블의 덤프 데이터 `pmc_user`
--

INSERT INTO `pmc_user` (`id`, `input_id`, `password`, `password_salt`, `nick_name`, `user_name`, `email_address`, `phone_number`, `last_logined_ip`, `extra_vars`) VALUES
(1, 'admin', '875bdbdd2cdb7326981de9c27bf9d76d52c75cd9bb1299417b1135b69a748b69', 'f98c94ebb87dc80be2a26991e3d5cc62', 'Admin', '어드민', 'admin@parmeter.kr', '010-1234-5678', '127.0.0.1', NULL),
(2, 'tester', '2827e05770ec174da512daf5af4ce49f5e07209d82e2ed90b2ee565886e7b521', '8f4031bfc7640c5f267b11b6fe0c2507', '테스터', '테스터', 'tester@parameter.kr', '010-1234-5678', '127.0.0.1', NULL);

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_user_group`
--

CREATE TABLE IF NOT EXISTS `pmc_user_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` tinytext NOT NULL,
  `name_locales` text NOT NULL,
  `is_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 테이블의 덤프 데이터 `pmc_user_group`
--

INSERT INTO `pmc_user_group` (`id`, `name`, `name_locales`, `is_default`) VALUES
(1, 'admin', '{"en":"Admin Group", "kr":"관리그룹"}', 0),
(2, 'general', '{"en":"General","kr":"일반회원"}', 1);

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_user_group_user`
--

CREATE TABLE IF NOT EXISTS `pmc_user_group_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 테이블의 덤프 데이터 `pmc_user_group_user`
--

INSERT INTO `pmc_user_group_user` (`id`, `group_id`, `user_id`) VALUES
(1, 1, 1),
(2, 2, 2);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pmc_article`
--
ALTER TABLE `pmc_article`
  ADD CONSTRAINT `pmc_article_ibfk_1` FOREIGN KEY (`board_id`) REFERENCES `pmc_board` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pmc_article_ibfk_2` FOREIGN KEY (`writer_id`) REFERENCES `pmc_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pmc_article_ibfk_3` FOREIGN KEY (`top_no`) REFERENCES `pmc_article` (`no`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pmc_article_comment`
--
ALTER TABLE `pmc_article_comment`
  ADD CONSTRAINT `pmc_article_comment_ibfk_1` FOREIGN KEY (`article_no`) REFERENCES `pmc_article` (`no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pmc_article_comment_ibfk_2` FOREIGN KEY (`writer_id`) REFERENCES `pmc_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pmc_menu`
--
ALTER TABLE `pmc_menu`
  ADD CONSTRAINT `pmc_menu_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `pmc_menu` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pmc_user_group_user`
--
ALTER TABLE `pmc_user_group_user`
  ADD CONSTRAINT `pmc_user_group_user_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `pmc_user_group` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pmc_user_group_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `pmc_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
