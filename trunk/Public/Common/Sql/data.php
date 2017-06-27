<?php
/**
 * 应用安装时的初始数据文件
 * data.php
 * $Author$
 */
//return "
//INSERT INTO `oa_common_setting` (`setting_id`, `key`, `value`, `type`, `comment`, `domain`, `status`, `created`, `updated`, `deleted`) VALUES
//(1,	'appConfig',	'a:6:{s:4:"News";a:4:{s:4:"open";s:1:"1";s:4:"name";s:6:"新闻";s:7:"iconApi";s:26:"News/Apicp/Operate/IconApi";s:9:"bannerApi";a:5:{s:13:"selectorTitle";s:12:"选择新闻";s:11:"categoryUrl";s:28:"News/Apicp/Operate/ClassList";s:7:"listUrl";s:27:"News/Apicp/Operate/NewsList";s:9:"searchUrl";s:29:"News/Apicp/Operate/NewsSearch";s:11:"searchTitle";s:18:"搜索新闻标题";}}s:6:"Course";a:4:{s:4:"open";s:1:"1";s:4:"name";s:6:"课程";s:7:"iconApi";s:28:"Course/Apicp/Operate/IconApi";s:9:"bannerApi";a:5:{s:13:"selectorTitle";s:12:"选择课程";s:11:"categoryUrl";s:30:"Course/Apicp/Operate/ClassList";s:7:"listUrl";s:31:"Course/Apicp/Operate/CourseList";s:9:"searchUrl";s:33:"Course/Apicp/Operate/CourseSearch";s:11:"searchTitle";s:18:"搜索课程标题";}}s:4:"Exam";a:4:{s:4:"open";i:1;s:4:"name";s:6:"考试";s:7:"iconApi";s:26:"Exam/Apicp/Operate/IconApi";s:9:"bannerApi";a:0:{}}s:4:"Sign";a:4:{s:4:"open";i:1;s:4:"name";s:6:"签到";s:7:"iconApi";s:26:"Sign/Apicp/Operate/IconApi";s:9:"bannerApi";a:0:{}}s:8:"Workmate";a:4:{s:4:"open";i:1;s:4:"name";s:9:"同事圈";s:7:"iconApi";s:30:"Workmate/Apicp/Operate/IconApi";s:9:"bannerApi";a:0:{}}s:8:"Activity";a:4:{s:4:"open";i:1;s:4:"name";s:12:"活动中心";s:7:"iconApi";s:30:"Activity/Apicp/Operate/IconApi";s:9:"bannerApi";a:0:{}}}',	1,	'应用配置相关信息',	'comm',	1,	0,	0,	0);
//";

return Array(
    'News' => Array
    (
        'open' => '1',
        'name' => '新闻资讯',
        'iconApi' => 'News/Apicp/Operate/IconApi',
        'bannerApi' => Array
        (
            'selectorTitle' => '选择新闻',
            'categoryUrl' => 'News/Apicp/Operate/ClassList',
            'listUrl' => 'News/Apicp/Operate/NewsList',
            'searchUrl' => 'News/Apicp/Operate/NewsSearch',
            'searchTitle' => '搜索新闻标题',
        ),

    ),

    'Course' => Array
    (
        'open' => '1',
        'name' => '课程中心',
        'iconApi' => 'Course/Apicp/Operate/IconApi',
        'bannerApi' => Array
        (
            'selectorTitle' => '选择课程',
            'categoryUrl' => 'Course/Apicp/Operate/ClassList',
            'listUrl' => 'Course/Apicp/Operate/CourseList',
            'searchUrl' => 'Course/Apicp/Operate/CourseSearch',
            'searchTitle' => '搜索课程标题',
        )

    ),

    'Exam' => Array
    (
        'open' => 1,
        'name' => '考试中心',
        'iconApi' => 'Exam/Apicp/Operate/IconApi',
        'bannerApi' => Array
        (
        )

    ),

    'Sign' => Array
    (
        'open' => 1,
        'name' => '签到',
        'iconApi' => 'Sign/Apicp/Operate/IconApi',
        'bannerApi' => Array
        (
        )

    ),

    'Workmate' => Array
    (
        'open' => 1,
        'name' => '同事圈',
        'iconApi' => 'Workmate/Apicp/Operate/IconApi',
        'bannerApi' => Array
        (
        )

    ),

    'Activity' => Array
    (
        'open' => 1,
        'name' => '活动中心',
        'iconApi' => 'Activity/Apicp/Operate/IconApi',
        'bannerApi' => Array
        (
        )

    ),

    'Doc' => Array
    (
        'open' => 1,
        'name' => '资料库',
        'iconApi' => 'Doc/Apicp/Operate/IconApi',
        'bannerApi' => Array
        (

        )

    ),

);