<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 17:06
 */
namespace Apicp\Controller\News;

use Common\Service\ArticleService;
use Common\Service\RightService;
use Common\Service\AttachService;

class InfoController extends \Apicp\Controller\AbstractController
{
   /**
    * Info
    * @author liyifei
    * @desc 新闻详情
    * @param int article_id:true 新闻公告ID
    * @return array 新闻详情
    *               array(
                       'news_status' => 1, // 新闻状态（1=草稿，2=已发布，3=预发布）
                       'article_id' => 123, // 新闻ID
                       'title' => '重大新闻', // 新闻标题
                       'cover_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 封面图片ID
                       'cover_url' => 'http://qy.vchangyi.org', // 封面图片地址
                       'is_show_cover' => 1, // 是否正文显示封面图片（1=不显示，2=显示）
                       'summary' => '零食增加卫龙系列', // 摘要
                       'parent_id' => 1, // 父分类ID
                       'parent_name' => '一级分类', // 父分类名称
                       'class_id' => 2, // 分类ID
                       'class_name' => '内部公告', // 分类名称
                       'author' => '张三', // 作者
                       'at_video' => array( // 视频附件
                           array(
                               'at_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 附件ID
                               'at_name' => '小视频.mp4', // 附件名
                               'at_time' => '123456543000', // 音、视频类型的播放时长(单位：毫秒)
                               'at_size' => 10240.12, // 附件尺寸（单位KB）
                               'at_url' => 'http://qy.vchangyi.com', // 附件地址
                               'at_convert_url' => 'http://qy.vchangyi.com', // 视频转码后url
                               'at_suffix' => '.xml', // 附件后缀
                           ),
                       ),
                       'at_audio' => array( // 音频附件
                           array(
                               'at_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 附件ID
                               'at_name' => '音频.mp3', // 附件名
                               'at_time' => '123456543000', // 音、视频类型的播放时长(单位：毫秒)
                               'at_size' => 1024.12, // 附件尺寸（单位KB）
                               'at_url' => 'http://qy.vchangyi.com', // 附件地址
                               'at_convert_url' => '', // 转码后url,音频附件为空
                               'at_suffix' => '.txt', // 附件后缀
                           ),
                       ),
                       'at_file' => array( // 文件附件
                           array(
                               'at_id' => 'b3ddbc502e307665f346cbd6e52cc10d', // 附件ID
                               'at_name' => '文档.doc', // 附件名
                               'at_size' => 123.12, // 附件大小（单位:字节）
                               'at_url' => 'http://qy.vchangyi.com', // 附件地址
                               'at_convert_url' => 'http://qy.vchangyi.com', // 文件转码后url
                               'at_suffix' => '.xml', // 附件后缀
                           ),
                       ),
                       'right' => array( // 新闻阅读权限
                           'is_all' => 1, // 是否全公司(1=否，2=是)
                           'tag_list' => array(
                               array(
                                   'tag_id' => '3CDBB2867F0000012C7F8D28432943BB',
                                   'tag_name' => 'liyifei001'
                               ),
                           ),
                           'dp_list' => array(
                               array(
                                   'dp_id' => 'B65085507F0000017D3965FCB20CA747',
                                   'dp_name' => '一飞冲天'
                               ),
                           ),
                           'user_list' => array(
                               array(
                                   'uid' => 'B4B3BA5B7F00000173E870DA6ADFEA2A',
                                   'username' => '缘来素雅',
                                   'face' => 'http://shp.qpic.cn/bizmp/gdZUibR6BHrmiar6pZ6pLfRyZSVaXJicn2CsvKRdI9gccXRfP2NrDvJ8A/'
                               ),
                           )
                           'job_list' => array(// 职位
                               array(
                                   'job_id' => '62C316437F0000017AE8E6ACC7EFAC22',// 职位ID
                                   'job_name' => '攻城狮',// 职位名称
                               ),
                           ),
                           'role_list' => array(// 角色
                               array(
                                   'role_id' => '62C354B97F0000017AE8E6AC4FD6F429',// 角色ID
                                   'role_name' => '国家元首',// 角色名称
                               ),
                           ),
                       ),
                       'is_download' => 1, // 附件是否支持下载（1=不支持，2=支持）
                       'is_secret' => 1, // 是否保密（1=不保密，2=保密）
                       'is_share' => 1, // 允许分享（1=不允许，2=允许）
                       'is_notice' => 1, // 消息通知（1=不开启，2=开启）
                       'is_comment' => 1, // 评论功能（1=不开启，2=开启）
                       'is_like' => 1, // 点赞功能（1=不开启，2=开启）
                       'is_recommend' => 1, // 首页推荐（1=不开启，2=开启）
                       'content' => '语言是民族的重要特征之一', // 新闻内容
                    );
    */
    public function Index_post()
    {
        $article_id = I('post.article_id', 0, 'intval');
        $articleServ = new ArticleService();
        $article = $articleServ->get($article_id);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // 格式化新闻数据
        $formatArticle = $articleServ->formatData($article);

        // 格式化新闻权限数据
        $rightServ = new RightService();
        $formatRight['right'] = $rightServ->getData(['article_id' => $article_id]);

        // 格式化附件数据
        $attachServ = new AttachService();
        $formatAttach = $attachServ->formatData($article['article_id']);

        // 组合数据
        $data = array_merge($formatArticle, $formatRight, $formatAttach);

        $this->_result = $data;
    }
}
