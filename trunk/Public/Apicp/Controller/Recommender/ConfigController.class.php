<?php
/**
 * ConfigController.class.php
 * 【运营管理】应用相关配置读取
 * @author Deepseath
 * @version $Id$
 */
namespace Apicp\Controller\Recommender;

use Common\Common\Setting;

class ConfigController extends AbstractController
{

    /**
     * 获取应用配置信息
     *
     * @desc 【管理后台】获取应用配置信息
     *
     * @param string app:false 读取指定应用模块配置信息，为空则返回全部应用配置信息
     *
     * @return <pre>array(
     *   'News' =>
     *   array (
     *     'open' => 1, // 应用模块是否启用。1=启用；2=未启用
     *     'name' => '新闻', // 应用模块标准名称
     *     'iconApi' => '', // 首页 icon 获取接口 url
     *     'bannerApi' =>
     *     array (
     *       'selectorTitle' => '选择新闻', // 首页条幅接口相关：选择器显示的名称
     *       'categoryUrl' => '', // 首页条幅接口相关：分类获取接口 url
     *       'searchUrl' => '', // 首页条幅接口相关：文章搜索接口 url
     *       'listUrl' => '', // 首页条幅接口相关：文章列表接口 url
     *       'searchTitle' => '搜索新闻标题', // 首页条幅接口相关：文章搜索默认显示 placeholder 文字
     *     ),
     *   ),
     *   'Course' =>
     *   array (
     *     'open' => 1, // 应用模块是否启用。1=启用；2=未启用
     *     'name' => '课程',// 应用模块标准名称
     *     'iconApi' => '', // 首页 icon 获取接口 url
     *     'bannerApi' =>
     *     array (
     *       'selectorTitle' => '选择课程', // 首页条幅接口相关：选择器显示的名称
     *       'categoryUrl' => '', // 首页条幅接口相关：分类获取接口 url
     *       'searchUrl' => '', // 首页条幅接口相关：文章搜索接口 url
     *       'listUrl' => '', // 首页条幅接口相关：文章列表接口 url
     *       'searchTitle' => '搜索课程标题', // 首页条幅接口相关：文章搜索默认显示 placeholder 文字
     *     ),
     *   ),
     *   'Exam' =>
     *   array (
     *     'open' => 1, // 应用模块是否启用。1=启用；2=未启用
     *     'name' => '考试',// 应用模块标准名称
     *     'iconApi' => '', // 首页 icon 获取接口 url
     *     'bannerApi' =>
     *     array (
     *       'selectorTitle' => '选择试题', // 首页条幅接口相关：选择器显示的名称
     *       'categoryUrl' => '', // 首页条幅接口相关：分类获取接口 url
     *       'searchUrl' => '', // 首页条幅接口相关：文章搜索接口 url
     *       'listUrl' => '', // 首页条幅接口相关：文章列表接口 url
     *       'searchTitle' => '搜索试卷名称', // 首页条幅接口相关：文章搜索默认显示 placeholder 文字
     *     ),
     *   ),
     * )</pre>
     */
    public function Index()
    {

        // 请求指定的应用信息
        $app = I('app', null);
        // 定义合法的应用标识名（目录名）
        $appList = [
            'News',
            'Course',
            'Exam'
        ];

        // 获取缓存
        $set = &Setting::instance();
        $config = $set->get('Common.appConfig');

        if (!in_array($app, $appList)) {
            $app = null;
        }

        if ($app === null) {
            // 获取全部应用信息
            $this->_result = $config;
        } else {
            // 获取指定应用信息
            $this->_result = $config[$app];
        }

        return $this->_result;
    }
}
