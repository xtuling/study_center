<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 14:05
 */
namespace Api\Controller\News;

use Com\PackageValidate;
use Common\Common\User;
use Common\Common\Constant;
use Common\Common\RpcFavoriteHelper;
use Common\Service\ArticleService;
use Common\Service\LikeService;
use Common\Service\AttachService;
use Common\Service\ReadService;
use Common\Service\RightService;

class InfoController extends \Api\Controller\AbstractController
{
    /**
     * 是否必须登录
     */
    protected $_require_login = false;
    
    /**
     * Info
     * @author liyifei
     * @desc 新闻详情
     * @param int article_id:true 新闻公告ID
     * @param int is_share 是否为分享入口（1=不是；2=是）
     * @return array 新闻详情
     *          array(
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
                    'is_download' => 1, // 附件是否支持下载（1=不支持，2=支持）
                    'is_secret' => 1, // 是否保密（1=不保密，2=保密）
                    'is_share' => 1, // 允许分享（1=不允许，2=允许）
                    'is_notice' => 1, // 消息通知（1=不开启，2=开启）
                    'is_comment' => 1, // 评论功能（1=不开启，2=开启）
                    'is_like' => 1, // 点赞功能（1=不开启，2=开启）
                    'is_recommend' => 1, // 首页推荐（1=不开启，2=开启）
                    'content' => '语言是民族的重要特征之一', // 新闻内容
                    'my_is_like' => 1, // 我是否点赞（1=未点赞，2=已点赞）
                    'my_is_favorite' => 1, // 我是否收藏（1=未收藏，2=已收藏）
                    'like_list' => array ( // 点赞列表(头像url)
                        'total' => 1000, // 点赞总数
                        'index' => 0, // 我在点赞人员头像列表的位置下标(我未点赞时,返回空字符串)
                        'face_list' => array( // 点赞人员头像列表
                            'http://www.vchangyi.com/001.jpg',
                            'http://www.vchangyi.com/002.jpg',
                        )
                    )
                );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer|gt:0',
            'is_share' => 'integer|in:1,2',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $article_id = $postData['article_id'];
        $is_share = isset($postData['is_share']) ? $postData['is_share'] : 0;

        // 查询新闻
        $articleServ = new ArticleService();
        $article = $articleServ->get_by_conds([
            'article_id' => $article_id,
            'news_status' => Constant::NEWS_STATUS_SEND,
        ]);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }

        // 登录人员是否在阅读权限内
        $user = $this->_login->user;
        $rightServ = new RightService();
        $checkRes = $rightServ->checkUserRight($user, $article_id);

        // 保密新闻或非分享入口
        if ($is_share != Constant::RIGHT_INPUT_IS_SHARE_TRUE || $article['is_secret'] == Constant::NEWS_IS_SECRET_TRUE) {
            // 不在权限范围内
            if (!$checkRes) {
                E('_ERR_ARTICLE_CAN_NOT_READ');
            }
        }

        // 非外部人员
        if (!empty($user)) {
            // 登录人员在可阅读范围内
            if ($checkRes) {
                $readServ = new ReadService();
                $read = $readServ->get_by_conds([
                    'article_id' => $article_id,
                    'uid' => $user['memUid'],
                ]);
                if (!$read) {
                    // 记录阅读信息
                    $dpNames = [];
                    if (isset($user['dpName']) && $user['dpName']) {
                        $dpNames = array_column($user['dpName'], 'dpName');
                    }
                    $readServ->insert([
                        'article_id' => $article_id,
                        'uid' => $user['memUid'],
                        'username' => isset($user['memUsername']) ? $user['memUsername'] : '',
                        'dp_name' => $dpNames ? serialize($dpNames) : '',
                        'job' =>  isset($user['memJob']) ? $user['memJob'] : '',
                        'mobile' =>  isset($user['memMobile']) ? $user['memMobile'] : '',
                    ]);

                    // 新闻主表read_total+1
                    $articleServ->update($article_id, ['`read_total` = `read_total` + ?' => 1]);
                }
            }
        }

        // 格式化新闻数据
        $articleFormat = $articleServ->formatData($article);

        // 格式化附件数据
        $attachServ = new AttachService();
        $formatAttach = $attachServ->formatData($article['article_id']);

        // 组合数据
        $data = array_merge($articleFormat, $formatAttach);

        // 我是否点赞(默认否)
        $data['my_is_like'] = Constant::NEWS_IS_LIKE_FALSE;

        // 外部人员
        if (empty($user)) {
            $user['memUid'] = '';
            $data['is_download'] = Constant::NEWS_IS_DOWNLOAD_FALSE;
            $data['is_comment'] = Constant::NEWS_IS_COMMENT_FALSE;
            $data['is_like'] = Constant::NEWS_IS_LIKE_FALSE;
            $data['is_outside'] = Constant::RIGHT_IS_OUTSIDE_YES;
        } else {
            $data['is_outside'] = Constant::RIGHT_IS_OUTSIDE_NO;
            
            // RPC查询收藏结果
            $param = [
                'uid' => $user['memUid'],
                'dataId' => $article['article_id'],
            ];
            $rpcFavorite = &RpcFavoriteHelper::instance();
            $status = $rpcFavorite->getStatus($param);
            $data['my_is_favorite'] = Constant::NEWS_IS_LIKE_FALSE;
            if (isset($status['collection']) && $status['collection'] == RpcFavoriteHelper::COLLECTION_YES) {
                $data['my_is_favorite'] = Constant::NEWS_IS_LIKE_TRUE;
            }
        }

        // 点赞列表
        $data['like_list'] = [
            'total' => 0,
            'index' => '',
            'face_list' => [],
        ];
        $likeServ = new LikeService();
        $likeList = $likeServ->list_by_conds(['article_id' => $article_id], [], ['created' => 'desc']);
        if ($likeList) {
            // 我是否点赞
            $uids = array_column($likeList, 'uid');
            if (in_array($user['memUid'], $uids)) {
                $data['my_is_like'] = Constant::NEWS_IS_LIKE_TRUE;
            }

            // 点赞人员头像列表
            $userServ = &User::instance();
            $users = $userServ->listByUid($uids);
            foreach ($uids as $index => $uid) {
                // 我的点赞在点赞人员头像列表的位置下标
                if ($uid == $user['memUid']) {
                    $data['like_list']['index'] = $index;
                }
                foreach ($users as $u) {
                    if ($uid == $u['memUid']) {
                        $data['like_list']['face_list'][] = $u['memFace'];
                    }
                }
            }

            $data['like_list']['total'] = count($likeList);
        }

        $this->_result = $data;
    }
}
