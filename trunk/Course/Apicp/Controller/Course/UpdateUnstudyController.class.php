<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/5/4
 * Time: 15:31
 */
namespace Apicp\Controller\Course;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\ArticleHelper;
use Common\Service\ArticleService;

class UpdateUnstudyController extends \Apicp\Controller\AbstractController
{
    /**
     * UpdateUnstudy
     * @author liyifei
     * @desc 更新课程未学习人员总数
     * @param Array article_ids:true 课程ID数组
     * @return mixed
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'article_ids' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;
        $article_ids = $postData['article_ids'];

        $artServ = new ArticleService();
        $list = $artServ->list_by_conds([
            'article_id' => $article_ids,
            'refresh_time < ?' => MILLI_TIME - Constant::ARTICLE_REFRESH_TIME,
        ]);

        if (empty($list)) {
            return true;
        }

        $articleHelper = &ArticleHelper::instance();

        foreach ($list as $article) {
            list(, , $uids_unstudy) = $articleHelper->getStudyData($article['article_id']);
            $unstudy_total = count($uids_unstudy);

            if ($article['unstudy_total'] != $unstudy_total) {
                $artServ->update($article['article_id'], [
                    'unstudy_total' => $unstudy_total,
                    'refresh_time' => MILLI_TIME,
                ]);
            }
        }
    }
}
