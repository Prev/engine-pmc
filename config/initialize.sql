-- phpMyAdmin SQL Dump
-- version 4.0.4.1
-- http://www.phpmyadmin.net
--
-- 호스트: localhost
-- 처리한 시간: 13-08-13 21:54
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
  `content` text,
  `writer_id` int(11) unsigned NOT NULL,
  `top_no` int(11) unsigned DEFAULT NULL,
  `order_key` tinytext,
  `is_secret` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `is_notice` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `allow_comment` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `upload_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hits` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`no`),
  KEY `board_id` (`board_id`),
  KEY `writer_id` (`writer_id`),
  KEY `top_no` (`top_no`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_article_comment`
--

CREATE TABLE IF NOT EXISTS `pmc_article_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_no` int(11) unsigned NOT NULL,
  `content` tinytext NOT NULL,
  `writer_id` int(11) unsigned NOT NULL,
  `write_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `top_id` int(10) unsigned DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `is_secret` tinyint(1) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `writer_id` (`writer_id`),
  KEY `article_no` (`article_no`),
  KEY `top_id` (`top_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_article_files`
--

CREATE TABLE IF NOT EXISTS `pmc_article_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `article_no` int(10) unsigned NOT NULL,
  `file_id` int(10) unsigned NOT NULL,
  `file_name` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `article_no` (`article_no`),
  KEY `file_id` (`file_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_board`
--

CREATE TABLE IF NOT EXISTS `pmc_board` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `name_locales` tinytext NOT NULL,
  `categorys` tinytext COMMENT '말머리/JSON Array',
  `readable_group` tinytext COMMENT '읽기 가능 그룹/JSON Array/NULL 시 모두가 읽을 수 있음',
  `commentable_group` tinytext COMMENT '덧글 가능 그룹/JSON Array/NULL 시 모두가 덧글을 달 수 있음',
  `writable_group` tinytext COMMENT '글 쓰기 가능 그룹/JSON Array/NULL 시 모두가 글을 쓸 있음',
  `admin_group` tinytext COMMENT '게시판 관리자 그룹/JSON Array/공지 설정 가능/게시글 및 덧글 삭제 가능/NULL시 관리 그룹 없음',
  `hide_secret_article` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `extra_vars` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_en` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_files`
--

CREATE TABLE IF NOT EXISTS `pmc_files` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_binary` tinyint(1) NOT NULL,
  `uploaded_url` tinytext NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `file_hash` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
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

--
-- 테이블의 덤프 데이터 `pmc_session`
--

INSERT INTO `pmc_session` (`session_key`, `expire_time`, `ip_address`, `last_update`, `user_id`, `extra_vars`) VALUES
('3030def0203532dd85d03fba07f999cd80347952', '2013-08-16 09:25:23', '127.0.0.1', '2013-08-09 00:25:23', 2, NULL),
('369dc0b58de16c55a60d30a56082b8d61d506705', '2013-08-16 09:26:24', '127.0.0.1', '2013-08-09 00:26:24', 2, NULL),
('406708941d532943d0a95919a15a8d0f554994e3', '2013-08-07 14:30:23', '127.0.0.1', '2013-07-31 05:30:23', 2, NULL),
('5205a5800b6f2fdacd73a677166bd6cfe03fe417', '2013-08-15 19:46:31', '127.0.0.1', '2013-08-08 10:46:31', 1, NULL),
('5e82e7e89ab342bc0d1e480169d8a808f1c680e9', '2013-08-07 14:30:08', '127.0.0.1', '2013-07-31 05:30:08', 2, NULL),
('791ef1272c7de2e216654b8b363cb128d32ef17f', '2013-08-07 14:45:19', '127.0.0.1', '2013-07-31 05:45:19', 2, NULL),
('8245d4d24fbcba5c9c503d7801906d061dc3aa8e', '2013-08-16 14:37:27', '127.0.0.1', '2013-08-09 05:37:27', 2, NULL),
('9bbc08f7e378e67b879608a67c50f2fcb613596d', '2013-08-07 14:29:55', '127.0.0.1', '2013-07-31 05:29:55', 2, NULL),
('a98afe8006bbf79a7f196972442c34c3b1c4df96', '2013-08-05 15:43:12', '127.0.0.1', '2013-07-29 06:43:12', 2, NULL),
('afe440666b9b70a06d684007b83d1ed2e2c576f8', '2013-08-05 14:44:21', '127.0.0.1', '2013-07-29 05:44:21', 2, NULL),
('cd486c4eb0753040cee902e94621b2d359a68709', '2013-08-12 17:52:56', '127.0.0.1', '2013-08-05 08:52:56', 2, NULL),
('db1c46dc3b36b08792d0b3be8e611b54ca3b4367', '2013-08-19 17:05:17', '127.0.0.1', '2013-08-12 08:05:17', 2, NULL),
('ee35cf57302a69d2914a33028221217646c3b519', '2013-08-07 14:29:33', '127.0.0.1', '2013-07-31 05:29:33', 2, NULL),
('f16fa49230be4d717ed0ffb09f2f3387d59880cb', '2013-08-16 10:14:52', '127.0.0.1', '2013-08-09 01:14:52', 2, NULL),
('f51ba7c08ef35411eaf3a3a7fd02009d9ab27fa0', '2013-08-15 10:14:11', '127.0.0.1', '2013-08-08 01:14:11', 2, NULL),
('f734592e67b679102c78dd13eece420ac3158502', '2013-08-16 14:37:05', '127.0.0.1', '2013-08-09 05:37:05', 1, NULL),
('f97300330ecaffb16b3c83f6975472e640c89482', '2013-08-09 09:35:28', '127.0.0.1', '2013-08-02 00:35:28', 2, NULL);

-- --------------------------------------------------------

--
-- 테이블 구조 `pmc_user`
--

CREATE TABLE IF NOT EXISTS `pmc_user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `input_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'SHA256',
  `password_salt` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'MD5',
  `nick_name` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
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
(1, 'admin', '875bdbdd2cdb7326981de9c27bf9d76d52c75cd9bb1299417b1135b69a748b69', 'f98c94ebb87dc80be2a26991e3d5cc62', '어드민', '어드민', 'admin@parmeter.kr', '010-1234-5678', '127.0.0.1', NULL),
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
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- 테이블의 덤프 데이터 `pmc_user_group`
--

INSERT INTO `pmc_user_group` (`id`, `name`, `name_locales`, `is_default`, `is_admin`) VALUES
(1, 'admin', '{"en":"Admin Group", "kr":"관리그룹"}', 0, 1),
(2, 'general', '{"en":"General","kr":"일반회원"}', 1, 0);

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
  ADD CONSTRAINT `pmc_article_ibfk_1` FOREIGN KEY (`board_id`) REFERENCES `pmc_board` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pmc_article_ibfk_2` FOREIGN KEY (`writer_id`) REFERENCES `pmc_user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pmc_article_ibfk_3` FOREIGN KEY (`top_no`) REFERENCES `pmc_article` (`no`);

--
-- Constraints for table `pmc_article_comment`
--
ALTER TABLE `pmc_article_comment`
  ADD CONSTRAINT `pmc_article_comment_ibfk_1` FOREIGN KEY (`article_no`) REFERENCES `pmc_article` (`no`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pmc_article_comment_ibfk_2` FOREIGN KEY (`writer_id`) REFERENCES `pmc_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pmc_article_comment_ibfk_3` FOREIGN KEY (`top_id`) REFERENCES `pmc_article_comment` (`id`),
  ADD CONSTRAINT `pmc_article_comment_ibfk_4` FOREIGN KEY (`parent_id`) REFERENCES `pmc_article_comment` (`id`);

--
-- Constraints for table `pmc_article_files`
--
ALTER TABLE `pmc_article_files`
  ADD CONSTRAINT `pmc_article_files_ibfk_1` FOREIGN KEY (`article_no`) REFERENCES `pmc_article` (`no`) ON DELETE CASCADE,
  ADD CONSTRAINT `pmc_article_files_ibfk_2` FOREIGN KEY (`file_id`) REFERENCES `pmc_files` (`id`);

--
-- Constraints for table `pmc_menu`
--
ALTER TABLE `pmc_menu`
  ADD CONSTRAINT `pmc_menu_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `pmc_menu` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pmc_session`
--
ALTER TABLE `pmc_session`
  ADD CONSTRAINT `pmc_session_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `pmc_user` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pmc_user_group_user`
--
ALTER TABLE `pmc_user_group_user`
  ADD CONSTRAINT `pmc_user_group_user_ibfk_1` FOREIGN KEY (`group_id`) REFERENCES `pmc_user_group` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pmc_user_group_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `pmc_user` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
