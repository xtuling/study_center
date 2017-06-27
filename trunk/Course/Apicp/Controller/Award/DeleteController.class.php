<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/27
 * Time: 10:59
 */
namespace Apicp\Controller\Award;

use Com\PackageValidate;
use Common\Service\AwardService;
use Common\Service\RightService;

class DeleteController extends \Apicp\Controller\AbstractController
{
    /**
     * Delete
     * @author tangxingguo
     * @desc 删除激励
     * @param array award_ids:true 激励ID数组
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'award_ids' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $awardIds = $postData = $validate->postData['award_ids'];

        // 删除激励
        $awardServ = new AwardService();
        $awardServ->delete_by_conds(['award_id' => $awardIds]);

        // 删除权限
        $rightServ = new RightService();
        $rightServ->delete_by_conds(['award_id' => $awardIds]);
    }
}
