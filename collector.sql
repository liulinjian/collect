/*
Navicat MySQL Data Transfer

Source Server         : root
Source Server Version : 50525
Source Host           : localhost:3306
Source Database       : collector

Target Server Type    : MYSQL
Target Server Version : 50525
File Encoding         : 65001

Date: 2014-06-30 17:55:44
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `kaoder_collect`
-- ----------------------------
DROP TABLE IF EXISTS `kaoder_collect`;
CREATE TABLE `kaoder_collect` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) NOT NULL COMMENT '域名',
  `alias` varchar(255) DEFAULT NULL COMMENT '别名',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=81 DEFAULT CHARSET=utf8 COMMENT='靠垫文章采集器数据表';

-- ----------------------------
-- Records of kaoder_collect
-- ----------------------------
INSERT INTO `kaoder_collect` VALUES ('2', 'blog.sina.com.cn', '新浪博客');
INSERT INTO `kaoder_collect` VALUES ('4', 'history.sina.com.cn', 'history.sina.com.cn');
INSERT INTO `kaoder_collect` VALUES ('5', 'fo.sina.com.cn', 'fo.sina.com.cn');
INSERT INTO `kaoder_collect` VALUES ('6', 'zhongyi.sina.com', 'zhongyi.sina.com');
INSERT INTO `kaoder_collect` VALUES ('18', 'history.news.qq.com', 'QQ历史');
INSERT INTO `kaoder_collect` VALUES ('19', 'view.news.qq.com', 'view.news.qq.com');
INSERT INTO `kaoder_collect` VALUES ('22', 'foxue.qq.com', 'foxue.qq.com');
INSERT INTO `kaoder_collect` VALUES ('23', 'history.people.com.cn', 'history.people.com.cn');
INSERT INTO `kaoder_collect` VALUES ('24', 'news.ifeng.com', 'news.ifeng.com');
INSERT INTO `kaoder_collect` VALUES ('25', 'zhongyi.ifeng.com', 'zhongyi.ifeng.com');
INSERT INTO `kaoder_collect` VALUES ('26', 'blog.douguo.com', 'blog.douguo.com');
INSERT INTO `kaoder_collect` VALUES ('27', 'dajia.qq.com', 'dajia.qq.com');
INSERT INTO `kaoder_collect` VALUES ('30', 'jiaren.org', 'jiaren.org');
INSERT INTO `kaoder_collect` VALUES ('31', 'www.guokr.com', 'www.guokr.com');
INSERT INTO `kaoder_collect` VALUES ('33', 'select.yeeyan.org', 'select.yeeyan.org');
INSERT INTO `kaoder_collect` VALUES ('34', 'ear.duomi.com', 'ear.duomi.com');
INSERT INTO `kaoder_collect` VALUES ('35', 'www.u148.net', 'www.u148.net');
INSERT INTO `kaoder_collect` VALUES ('38', 'news.sina.com.cn', 'news.sina.com.cn');
INSERT INTO `kaoder_collect` VALUES ('43', 'news.xinhuanet.com', 'news.xinhuanet.com');
INSERT INTO `kaoder_collect` VALUES ('44', 'sports.sina.com.cn', 'sports.sina.com.cn');
INSERT INTO `kaoder_collect` VALUES ('46', 'ent.sina.com.cn', 'ent.sina.com.cn');
INSERT INTO `kaoder_collect` VALUES ('47', 'politics.people.com.cn', 'politics.people.com.cn');
INSERT INTO `kaoder_collect` VALUES ('48', 'www.appinn.com', 'www.appinn.com');
INSERT INTO `kaoder_collect` VALUES ('55', 'cloud.51cto.com', 'cloud.51cto.com');
INSERT INTO `kaoder_collect` VALUES ('59', 'developer.51cto.com', 'developer.51cto.com');
INSERT INTO `kaoder_collect` VALUES ('60', 'zhuzike.com', 'zhuzike.com');
INSERT INTO `kaoder_collect` VALUES ('61', 'www.vmovier.com', 'www.vmovier.com');
INSERT INTO `kaoder_collect` VALUES ('62', 'zhu.tianhua.me', 'zhu.tianhua.me');
INSERT INTO `kaoder_collect` VALUES ('63', 'www.zreading.cn', 'www.zreading.cn');
INSERT INTO `kaoder_collect` VALUES ('64', 'jishi.cntv.cn', 'jishi.cntv.cn');
INSERT INTO `kaoder_collect` VALUES ('65', 'news.appinn.com', 'news.appinn.com');
INSERT INTO `kaoder_collect` VALUES ('66', 'qing.blog.sina.com.cn', 'qing.blog.sina.com.cn');
INSERT INTO `kaoder_collect` VALUES ('67', 'www.nowamagic.net', 'www.nowamagic.net');
INSERT INTO `kaoder_collect` VALUES ('68', 'news.qq.com', 'news.qq.com');
INSERT INTO `kaoder_collect` VALUES ('69', 'www.jfdaily.com', 'www.jfdaily.com');
INSERT INTO `kaoder_collect` VALUES ('70', 'tw.cankaoxiaoxi.com', 'tw.cankaoxiaoxi.com');
INSERT INTO `kaoder_collect` VALUES ('71', 'tech.sina.com.cn', 'tech.sina.com.cn');
INSERT INTO `kaoder_collect` VALUES ('72', 'tech.qq.com', 'tech.qq.com');
INSERT INTO `kaoder_collect` VALUES ('73', 'video.sina.com.cn', 'video.sina.com.cn');
INSERT INTO `kaoder_collect` VALUES ('74', 'www.socialbeta.com', 'www.socialbeta.com');
INSERT INTO `kaoder_collect` VALUES ('76', 'blog.csdn.net', 'blog.csdn.net');
INSERT INTO `kaoder_collect` VALUES ('78', 'sports.qq.com', 'sports.qq.com');
INSERT INTO `kaoder_collect` VALUES ('79', 'new.iheima.com', 'new.iheima.com');
INSERT INTO `kaoder_collect` VALUES ('80', 'www.chinaz.com', 'www.chinaz.com');

-- ----------------------------
-- Table structure for `kaoder_collect_match`
-- ----------------------------
DROP TABLE IF EXISTS `kaoder_collect_match`;
CREATE TABLE `kaoder_collect_match` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cid` int(11) NOT NULL COMMENT '关联kaoder_collect表',
  `match` varchar(255) NOT NULL,
  `pos` int(2) NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0:title  1:content',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=147 DEFAULT CHARSET=utf8 COMMENT='抓取字段匹配规则数据表';

-- ----------------------------
-- Records of kaoder_collect_match
-- ----------------------------
INSERT INTO `kaoder_collect_match` VALUES ('1', '2', 'div.articalTitle', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('2', '2', 'h2', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('3', '2', 'div.articalContent', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('4', '4', '#artibodyTitle', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('5', '4', '#artibody', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('6', '5', '#artibodyTitle', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('7', '5', '#artibody', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('8', '6', '#wordCon', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('9', '6', 'h1', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('10', '6', '#artCon', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('18', '18', 'div.hd', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('19', '18', 'h1', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('20', '18', 'div#Cnt-Main-Article-QQ', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('22', '19', 'h1', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('23', '19', 'div#articleContent', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('24', '22', '#C-Main-Article-QQ', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('25', '22', 'h1', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('26', '22', '#Cnt-Main-Article-QQ', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('27', '23', '#p_title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('28', '23', '#p_content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('29', '24', '#artical_topic', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('30', '24', 'div#artical_real', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('31', '25', 'div#i-article', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('32', '25', 'h2', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('33', '25', 'div#i-article1', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('34', '26', 'div#post-title-single', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('35', '26', 'h1', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('36', '26', 'div.post-content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('37', '27', 'div.title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('38', '27', 'h1', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('39', '27', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('40', '28', 'span.tcnt', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('41', '28', 'div.nbw-blog', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('42', '29', 'h1.entry-title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('43', '29', 'div.entry_content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('44', '30', 'h1#posttitle', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('45', '30', 'article.entry', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('46', '31', 'div.content', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('47', '31', 'div#articleContent', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('48', '31', 'h1', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('49', '32', 'div.post', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('50', '32', 'h2', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('51', '32', 'div.post', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('52', '32', 'div.content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('53', '33', 'h1.sa_title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('54', '33', 'div.sa_content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('55', '34', 'h1.title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('56', '34', 'div.post', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('57', '34', 'div.entry', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('58', '35', 'div.u148content', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('59', '35', 'h1', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('60', '35', 'div.content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('61', '37', 'span.mTbTiyle', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('62', '37', 'div.mb_col_1', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('63', '38', 'h1#artibodyTitle', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('64', '38', 'div#artibody', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('65', '43', 'h1#title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('66', '43', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('67', '44', 'h1#artibodyTitle', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('68', '44', 'div#artibody', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('69', '47', 'h1#p_title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('70', '47', 'div#p_content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('71', '46', 'h1#artibodyTitle', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('72', '46', 'div#artibody', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('73', '48', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('74', '48', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('75', '48', 'div.entry-content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('76', '49', 'div#body', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('77', '49', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('78', '50', 'div#body', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('79', '50', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('81', '51', 'div.part01 clearfix', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('82', '51', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('83', '52', 'div.part01 clearfix', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('84', '52', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('85', '53', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('86', '53', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('87', '54', 'div.nbw-blog-end', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('88', '54', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('89', '55', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('90', '55', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('91', '56', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('92', '56', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('93', '57', 'div#', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('94', '57', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('95', '58', 'div#', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('96', '58', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('97', '59', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('98', '59', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('99', '60', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('100', '60', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('101', '61', 'div#entry', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('102', '61', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('103', '62', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('104', '62', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('105', '63', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('106', '63', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('107', '64', 'div.text_lt', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('108', '64', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('109', '65', 'div.entry', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('110', '65', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('111', '66', 'div.content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('112', '66', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('113', '66', 'div.feedInfo', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('114', '60', 'div.content_post', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('115', '67', 'div.post_content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('116', '67', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('117', '69', 'div.article', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('118', '69', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('119', '69', 'div.para', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('120', '70', 'div.bg-content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('121', '70', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('122', '71', 'div#artibody', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('123', '71', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('124', '73', 'div#artibody', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('125', '73', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('126', '68', 'div#Cnt-Main-Article-QQ', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('127', '68', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('128', '74', 'div.entry', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('129', '74', 'h1.entry-title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('130', '75', 'body', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('131', '75', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('133', '72', 'div.mainContent', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('134', '72', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('135', '76', 'div#article_content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('136', '76', 'div.article_title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('137', '76', 'h3', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('138', '77', 'div#content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('139', '77', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('140', '78', 'div#Cnt-Main-Article-QQ', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('141', '78', 'title', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('142', '79', 'div.txs_Content', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('143', '79', 'div.txs_xq', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('144', '79', 'h2', '0', '0');
INSERT INTO `kaoder_collect_match` VALUES ('145', '80', 'div.pbody', '0', '1');
INSERT INTO `kaoder_collect_match` VALUES ('146', '80', 'title', '0', '0');

-- ----------------------------
-- Table structure for `kaoder_weixin`
-- ----------------------------
DROP TABLE IF EXISTS `kaoder_weixin`;
CREATE TABLE `kaoder_weixin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `wxh` varchar(50) DEFAULT NULL,
  `openId` varchar(250) DEFAULT NULL,
  `title` varchar(250) DEFAULT NULL,
  `summary` varchar(250) DEFAULT NULL,
  `content` text,
  `link` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of kaoder_weixin
-- ----------------------------
