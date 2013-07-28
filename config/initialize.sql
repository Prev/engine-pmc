-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- 호스트: localhost
-- 처리한 시간: 13-07-28 20:39
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
-- 테이블 구조 `pmc_files`
--

CREATE TABLE IF NOT EXISTS `pmc_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_binary` tinyint(1) NOT NULL,
  `uploaded_url` tinytext NOT NULL,
  `file_name` tinytext NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `file_hash` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

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

INSERT INTO `pmc_menu` (`id`, `title`, `title_locales`, `level`, `is_index`, `visible`, `parent_id`, `module`, `action`, `extra_vars`) VALUES
(1, 'home', '{"en":"Home", "kr":"홈"}', 1, 1, 1, NULL, 'index', NULL, NULL),
(2, 'notice', '{"en":"Notice", "kr":"공지사항"}', 1, 0, 1, NULL, 'board', NULL, NULL),
(3, 'freeboard', '{"en":"Free Board", "kr":"자유게시판"}', 1, 0, 1, NULL, 'board', NULL, NULL),
(4, 'others', '{"en":"Others", "kr":"기타"}', 1, 0, 1, NULL, 'page', NULL, NULL),
(5, 'about', '{"en":"About", "kr":"정보"}', 2, 0, 1, 4, 'page', NULL, NULL),
(6, 'libraries', '{"en":"Libraries", "kr":"라이브러리"}', 2, 0, 1, 4, 'page', NULL, NULL);

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
