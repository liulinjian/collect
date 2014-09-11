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


-- 导出  表 kaoder3.kaoder_collect 结构
CREATE TABLE IF NOT EXISTS `kaoder_collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL COMMENT '域名',
  `alias` varchar(255) DEFAULT NULL COMMENT '别名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8 COMMENT='靠垫文章采集器数据表';

-- 正在导出表  kaoder3.kaoder_collect 的数据：~27 rows (大约)
/*!40000 ALTER TABLE `kaoder_collect` DISABLE KEYS */;
INSERT INTO `kaoder_collect` (`id`, `domain`, `alias`) VALUES
	(2, 'blog.sina.com.cn', '新浪博客'),
	(4, 'history.sina.com.cn', 'history.sina.com.cn'),
	(5, 'fo.sina.com.cn', 'fo.sina.com.cn'),
	(6, 'zhongyi.sina.com', 'zhongyi.sina.com'),
	(18, 'history.news.qq.com', 'QQ历史'),
	(19, 'view.news.qq.com', 'view.news.qq.com'),
	(22, 'foxue.qq.com', 'foxue.qq.com'),
	(23, 'history.people.com.cn', 'history.people.com.cn'),
	(24, 'news.ifeng.com', 'news.ifeng.com'),
	(25, 'zhongyi.ifeng.com', 'zhongyi.ifeng.com'),
	(26, 'blog.douguo.com', 'blog.douguo.com'),
	(27, 'dajia.qq.com', 'dajia.qq.com'),
	(28, 'blog.163.com', 'blog.163.com'),
	(29, 'zhu.tianhua.me', 'zhu.tianhua.me'),
	(30, 'jiaren.org', 'jiaren.org'),
	(31, 'www.guokr.com', 'www.guokr.com'),
	(32, 'www.zreading.cn', 'www.zreading.cn'),
	(33, 'select.yeeyan.org', 'select.yeeyan.org'),
	(34, 'ear.duomi.com', 'ear.duomi.com'),
	(35, 'www.u148.net', 'www.u148.net'),
	(37, 'jishi.cntv.cn', 'jishi.cntv.cn'),
	(38, 'news.sina.com.cn', 'news.sina.com.cn'),
	(43, 'news.xinhuanet.com', 'news.xinhuanet.com'),
	(44, 'sports.sina.com.cn', 'sports.sina.com.cn'),
	(46, 'ent.sina.com.cn', 'ent.sina.com.cn'),
	(47, 'politics.people.com.cn', 'politics.people.com.cn'),
	(48, 'www.appinn.com', 'www.appinn.com');
/*!40000 ALTER TABLE `kaoder_collect` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
