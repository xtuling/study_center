<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 19:10
 */
namespace Apicp\Controller\Course;

use Common\Common\User;
use Common\Common\Comment;
use Common\Service\ArticleService;
use Common\Service\LikeService;

class LikeListController extends \Apicp\Controller\AbstractController
{
   /**
    * LikeList
    * @author liyifei
    * @desc 点赞列表
    * @param Int article_id:true 课程ID
    * @return array 点赞列表
                   array(
                       'article_title' => '标题', // 课程标题
                       'update_time' => '1434567890000', // 更新时间
                       'like_total' => 20, // 点赞总数
                       'comment_total' => 10, // 评论总数
                       'list' => array( // 点赞列表
                           array(
                               'uid' => 'B4B3BAFE7F00000173E870DA83A9751E', // 人员ID
                               'username' => '张三', // 人员姓名
                               'face' => 'http://shp.qpic.cn/bizmp/gdZUibR6BHrkuqSjvCzX33qvZpCIOaYZiaFRnciae9WgxiaWXqxkqIOyeg/', // 头像
                               'created' => 1434567890000, // 点赞时间
                           ),
                       ),
                   )
    */
    public function Index_post()
    {
        $article_id = I('post.article_id', 0, 'intval');
        $articleServ = new ArticleService();
        $article = $articleServ->get($article_id);
        if (empty($article)) {
            E('_ERR_ARTICLE_DATA_NOT_FOUND');
        }

        // 点赞列表
        $likeServ = new LikeService();
        $likeList = $likeServ->list_by_conds(['article_id' => $article_id]);
        if ($likeList) {
            // 人员信息
            $uids = array_column($likeList, 'uid');
            $userServ = &User::instance();
            $userList = $userServ->listByUid($uids);

            // 合并头像
            if ($userList) {
                foreach ($likeList as $k => $v) {
                    if (isset($userList[$v['uid']])) {
                        $likeList[$k]['face'] = $userList[$v['uid']]['memFace'];
                    }
                }
            }
        }

        // 评论总数
        $commentServ = &Comment::instance();
        $commentList = $commentServ->listAll(['cmtObjid' => $article_id]);

        $this->_result = [
            'article_title' => $article['article_title'],
            'update_time' => $article['update_time'],
            'like_total' => count($likeList),
            'comment_total' => $commentList['total'],
            'list' => $likeList
        ];
    }
}
