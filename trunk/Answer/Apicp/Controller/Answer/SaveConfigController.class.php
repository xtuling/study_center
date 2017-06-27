<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Answer;

use Com\PackageValidate;
use Common\Common\Config;
use Common\Service\ConfigService;

class SaveConfigController extends \Apicp\Controller\AbstractController
{
    /**
     * SaveConfig
     * @author
     * @desc 保存设置接口
     * @param Int right.is_all 是否全公司（1=否；2=是）
     * @param Array right.uids 人员ID
     * @param Array right.dp_ids 部门ID
     * @param Array right.tag_ids 标签ID
     * @param Array right.job_ids 职位ID
     * @param Array right.role_ids 角色ID
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'right' => 'require|array',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $right = $validate->postData['right'];

        // 格式化权限数据
        $configServ = new ConfigService();
        $right = $configServ->formatPostRight($right);
        if (empty($right)) {
            E('_ERR_CONFIG_RIGHT_IS_EMPTY');
        }

        // 保存数据
        $count = $configServ->count();
        if ($count > 0) {
            // 更新
            $configServ->update_by_conds([], ['rights' => serialize($right)]);
        } else {
            // 添加
            $configServ->insert(['rights' => serialize($right)]);
        }

        // 清除缓存数据
        Config::instance()->clearCacheData();
    }
}
