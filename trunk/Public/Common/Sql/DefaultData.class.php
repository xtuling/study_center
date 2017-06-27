<?php
/**
 * 应用安装时的初始数据文件
 * data.php
 * $Author$
 */
namespace Common\Sql;

class DefaultData
{
    public static function installData()
    {
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
                ()

            ),

            'Sign' => Array
            (
                'open' => 1,
                'name' => '签到',
                'iconApi' => 'Sign/Apicp/Operate/IconApi',
                'bannerApi' => Array
                ()

            ),

            'Workmate' => Array
            (
                'open' => 1,
                'name' => '同事圈',
                'iconApi' => 'Workmate/Apicp/Operate/IconApi',
                'bannerApi' => Array
                ()

            ),

            'Activity' => Array
            (
                'open' => 1,
                'name' => '活动中心',
                'iconApi' => 'Activity/Apicp/Operate/IconApi',
                'bannerApi' => Array
                ()

            ),

            'Doc' => Array
            (
                'open' => 1,
                'name' => '资料库',
                'iconApi' => 'Doc/Apicp/Operate/IconApi',
                'bannerApi' => Array
                ()

            ),

        );
    }
}
