<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/4
 * Time: 16:53
 */
namespace Apicp\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\ArticleHelper;
use Common\Service\ArticleService;
use Common\Service\ClassService;

class RemindController extends \Apicp\Controller\AbstractController
{
    /**
     * Remind
     * @author liyifei
     * @desc 未学习提醒
     * @param Int article_id:true 课程ID
     * @param Array uids:true 待提醒人员UID数组
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_id' => 'require|integer',
            'uids' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 已发布的课程详情
        $articleServ = new ArticleService();
        $article = $articleServ->get($postData['article_id']);
        if (empty($article)) {
            E('_ERR_ARTICLE_NOT_FOUND');
        }
        // 未发布课程不可提醒
        if ($article['article_status'] != Constant::ARTICLE_STATUS_SEND) {
            E('_ERR_ARTICLE_UNSEND_NOTICE');
        }

        // 顶级分类信息
        $classServ = new ClassService();
        $class = $classServ->getTopClass($article['class_id']);
        $article['class_name'] = $class['class_name'];

        // 课程可学、已学、未学人员UID
        $articleHelper = &ArticleHelper::instance();
        list($uids_all, $uids_study, $uids_unstudy) = $articleHelper->getStudyData($article['article_id']);

        // 传参人员是否有学习权限
        $diff = array_diff($postData['uids'], $uids_unstudy);
        if (!empty($diff)) {
            E('_ERR_ARTICLE_USER_RIGHT');
        }

        // 发送未读提醒
        $articleHelper->sendUnreadMsg($postData['uids'], $article);
    }
}
