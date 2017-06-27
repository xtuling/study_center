<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 19:10
 */
namespace Apicp\Controller\News;

use Com\PackageValidate;
use Common\Common\User;
use Common\Service\LikeService;

class LikeListController extends \Apicp\Controller\AbstractController
{
   /**
    * LikeList
    * @desc 点赞列表
    * @param int    article_id:true 新闻ID
    * @return array 点赞列表
    *               array(
                   'list' => array( // 点赞列表
                   'uid' => 'B4B3BAFE7F00000173E870DA83A9751E', // 人员ID
                   'username' => '张三', // 人员姓名
                   'face' => 'http://shp.qpic.cn/bizmp/gdZUibR6BHrkuqSjvCzX33qvZpCIOaYZiaFRnciae9WgxiaWXqxkqIOyeg/', // 头像
                   'created' => 1434567890000, // 点赞时间
                   ),
                   );
    */

    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 点赞列表
        $likeServ = new LikeService();
        $likeList = $likeServ->list_by_conds(['article_id' => $postData['article_id']]);

        if ($likeList) {
            // 人员信息
            $uids = array_column($likeList, 'uid');
            $userServ = &User::instance();
            $userList = $userServ->listAll(['memUids' => $uids]);
            $userList = array_combine_by_key($userList, 'memUid');

            // 合并头像
            if ($userList) {
                foreach ($likeList as $k => $v) {
                    if (isset($userList[$v['uid']])) {
                        $likeList[$k]['face'] = isset($userList[$v['uid']]) ? $userList[$v['uid']]['memFace'] : '';
                    }
                }
            }

        }

        $this->_result = ['list' => $likeList];
    }
}
