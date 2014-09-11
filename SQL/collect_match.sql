-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        5.5.22-log - MySQL Community Server (GPL)
-- 服务器操作系统:                      Win32
-- HeidiSQL 版本:                  8.1.0.4545
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出 kaoder3 的数据库结构
CREATE DATABASE IF NOT EXISTS `kaoder3` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `kaoder3`;


-- 导出  表 kaoder3.kaoder_collect_match 结构
CREATE TABLE IF NOT EXISTS `kaoder_collect_match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL COMMENT '关联kaoder_collect表',
  `match` varchar(255) NOT NULL,
  `pos` int(2) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:title  1:content',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8 COMMENT='抓取字段匹配规则数据表';

-- 正在导出表  kaoder3.kaoder_collect_match 的数据：~72 rows (大约)
/*!40000 ALTER TABLE `kaoder_collect_match` DISABLE KEYS */;
INSERT INTO `kaoder_collect_match` (`id`, `cid`, `match`, `pos`, `type`) VALUES
	(1, 2, 'div.articalTitle', 0, 0),
	(2, 2, 'h2', 0, 0),
	(3, 2, '#sina_keyword_ad_area2', 0, 1),
	(4, 4, '#artibodyTitle', 0, 0),
	(5, 4, '#artibody', 0, 1),
	(6, 5, '#artibodyTitle', 0, 0),
	(7, 5, '#artibody', 0, 1),
	(8, 6, '#wordCon', 0, 0),
	(9, 6, 'h1', 0, 0),
	(10, 6, '#artCon', 0, 1),
	(18, 18, 'div.hd', 0, 0),
	(19, 18, 'h1', 0, 0),
	(20, 18, 'div#Cnt-Main-Article-QQ', 0, 1),
	(21, 19, '#C-Main-Article-QQ', 0, 0),
	(22, 19, 'h1', 0, 0),
	(23, 19, '#Cnt-Main-Article-QQ', 0, 1),
	(24, 22, '#C-Main-Article-QQ', 0, 0),
	(25, 22, 'h1', 0, 0),
	(26, 22, '#Cnt-Main-Article-QQ', 0, 1),
	(27, 23, '#p_title', 0, 0),
	(28, 23, '#p_content', 0, 1),
	(29, 24, '#artical_topic', 0, 0),
	(30, 24, 'div#artical_real', 0, 1),
	(31, 25, 'div#i-article', 0, 0),
	(32, 25, 'h2', 0, 0),
	(33, 25, 'div#i-article1', 0, 1),
	(34, 26, 'div#post-title-single', 0, 0),
	(35, 26, 'h1', 0, 0),
	(36, 26, 'div.post-content', 0, 1),
	(37, 27, 'div.title', 0, 0),
	(38, 27, 'h1', 0, 0),
	(39, 27, 'div#content', 0, 1),
	(40, 28, 'span.tcnt', 0, 0),
	(41, 28, 'div.nbw-blog', 0, 1),
	(42, 29, 'h1.entry-title', 0, 0),
	(43, 29, 'div.entry_content', 0, 1),
	(44, 30, 'h1#posttitle', 0, 0),
	(45, 30, 'article.entry', 0, 1),
	(46, 31, 'div.content', 0, 0),
	(47, 31, 'div#articleContent', 0, 1),
	(48, 31, 'h1', 0, 0),
	(49, 32, 'div.post', 0, 0),
	(50, 32, 'h2', 0, 0),
	(51, 32, 'div.post', 0, 1),
	(52, 32, 'div.content', 0, 1),
	(53, 33, 'h1.sa_title', 0, 0),
	(54, 33, 'div.sa_content', 0, 1),
	(55, 34, 'h1.title', 0, 0),
	(56, 34, 'div.post', 0, 1),
	(57, 34, 'div.entry', 0, 1),
	(58, 35, 'div.u148content', 0, 0),
	(59, 35, 'h1', 0, 0),
	(60, 35, 'div.content', 0, 1),
	(61, 37, 'span.mTbTiyle', 0, 0),
	(62, 37, 'div.mb_col_1', 0, 1),
	(63, 38, 'h1#artibodyTitle', 0, 0),
	(64, 38, 'div#artibody', 0, 1),
	(65, 43, 'h1#title', 0, 0),
	(66, 43, 'div#content', 0, 1),
	(67, 44, 'h1#artibodyTitle', 0, 0),
	(68, 44, 'div#artibody', 0, 1),
	(69, 47, 'h1#p_title', 0, 0),
	(70, 47, 'div#p_content', 0, 1),
	(71, 46, 'h1#artibodyTitle', 0, 0),
	(72, 46, 'div#artibody', 0, 1),
	(73, 48, 'div#content', 0, 1),
	(74, 48, 'title', 0, 0),
	(75, 48, 'div.entry-content', 0, 1),
	(76, 49, 'div#body', 0, 1),
	(77, 49, 'title', 0, 0),
	(78, 50, 'div#body', 0, 1),
	(79, 50, 'title', 0, 0);
/*!40000 ALTER TABLE `kaoder_collect_match` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
