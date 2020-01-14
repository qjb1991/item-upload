create table iu_upload_project(
  `id` int unsigned primary key auto_increment comment 'ID',
  `app_name` varchar(50) not null default '' comment '项目名称',
  `state` tinyint not null default 1 comment '状态 0：关闭 1：开启',
  `key_id` varchar(50) not null default '' comment 'access_key_id',
  `key_secret` varchar(100) not null default '' comment 'access_key_secret',
  `allow_type` varchar(255) not null default '' comment '允许的文件类型',
  `max_size` tinyint unsigned not null default 8 comment '文件允许最大值，单位/m',
  `created_at` int unsigned not null default 0 comment '创建时间',
  `updated_at` int unsigned not null default 0 comment '更新时间',
  unique key `key_id`(`key_id`)
)engine=InnoDb charset=utf8 comment='文件上传日志';

CREATE TABLE `iu_file` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `type` varchar(20) NOT NULL DEFAULT '' COMMENT '文件类型',
  `row_name` varchar(255) NOT NULL DEFAULT '' COMMENT '文件名',
  `access_url` varchar(1000) NOT NULL DEFAULT '' COMMENT '访问路径',
  `save_url` varchar(1000) NOT NULL DEFAULT '' COMMENT '保存路径',
  `app_name` varchar(20) NOT NULL DEFAULT '' COMMENT '项目来源',
  `md5` varchar(50) NOT NULL DEFAULT '' COMMENT 'md5',
  `size` varchar(255) not null default '' comment '文件大小',
  `create_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_at` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `md5` (`md5`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8 COMMENT='上传的文件';