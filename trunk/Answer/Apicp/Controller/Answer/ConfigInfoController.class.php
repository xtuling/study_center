<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/11
 * Time: 18:36
 */
namespace Apicp\Controller\Answer;

use Common\Service\ConfigService;

class ConfigInfoController extends \Apicp\Controller\AbstractController
{
    /**
     * ConfigInfo
     * @author
     * @desc 设置详情接口
     * @return array 权限详情
                array(
                    'rights' => array( // 权限信息
                        'is_all' => 1, // 是否全公司（1=否，2=是）
                        'user_list' => array( // 人员信息
                            array(
                                'uid' => '0E19B0B47F0000012652058BA42EEEDE', // 人员ID
                                'username' => '张三', // 人员姓名
                                'face' => 'http://qy.vchangyi.com', // 人员头像
                            ),
                        ),
                        'tag_list' => array( // 标签信息
                            array(
                                'tag_id' => '0E19B0B47F0000012652058BA42EEEDE', // 标签ID
                                'tag_name' => '吃货', // 标签名称
                            ),
                        ),
                        'dp_list' => array( // 部门信息
                            array(
                                'dp_id' => '0E19B0B47F0000012652058BA42EEEDE', // 部门ID
                                'dp_name' => '技术部', // 部门名称
                            ),
                        ),
                        'job_list' => array( // 职位
                            array(
                                'job_id' => '62C316437F0000017AE8E6ACC7EFAC22', // 职位ID
                                'job_name' => '攻城狮', // 职位名称
                            ),
                        ),
                        'role_list' => array( // 角色
                            array(
                                'role_id' => '62C354B97F0000017AE8E6AC4FD6F429', // 角色ID
                                'role_name' => '国家元首', // 角色名称
                            ),
                        ),
                    )
                );
     */
    public function Index_post()
    {
        // 设置内权限信息
        $configServ = new ConfigService();
        $rights = $configServ->getData();
        $rights = $configServ->getRightData($rights['rights']);

        $this->_result = [
            'rights' => $rights,
        ];
    }
}
