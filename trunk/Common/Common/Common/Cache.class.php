<?php
/**
 * Cache.class.php
 * 缓存
 * $Author$
 */

namespace Common\Common;

use Common\Model\SettingModel;
use Common\Service\SettingService;
use VcySDK\Department;
use VcySDK\Enterprise;
use VcySDK\Job;
use VcySDK\Member;
use VcySDK\Service;
use VcySDK\Tag;
use VcySDK\Role;

class Cache extends \Com\Cache
{

    /**
     * 实例化
     *
     * @return \Common\Common\Cache
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * 获取部门信息
     *
     * @return array|bool
     */
    public function Department()
    {

        // 获取数据
        $department_sdk = new Department(Service::instance());
        $result = $department_sdk->listAll(array(), 1, 15000);
        $departments = array_combine_by_key($result['list'], 'dpId');

        // 处理扩展
        \Common\Common\Department::parseExt($departments, $result['extList']);
        
        return $departments;
    }

    /**
     * 部门类型
     * @return array
     */
    public function DepartmentType()
    {

        $sdk = new Department(Service::instance());
        $types = $sdk->listType(array(), 1, 1000);

        return array_combine_by_key($types, 'dptId');
    }

    /**
     * 获取部门类型配置
     * @return array
     */
    public function DepartmentFieldConfig()
    {

        $sdk = new Department(Service::instance());
        $configs = $sdk->listFieldConfig(array(), 1, 5000);

        $result = array();
        foreach ($configs as $_cfg) {
            if (!isset($result[$_cfg['dptId']])) {
                $result[$_cfg['dptId']] = array();
            }

            if ('select' == $_cfg['dfcType']) {
                $_cfg['dfcValues'] = unserialize($_cfg['dfcValues']);
            }
            $result[$_cfg['dptId']][$_cfg['dfcId']] = $_cfg;
        }

        return $result;
    }

    /**
     * 职位信息
     *
     * @return array|bool
     */
    public function Job()
    {

        $job_sdk = new Job(Service::instance());
        $result = $job_sdk->listAll(array(), 1, 5000);

        if (is_array($result) && isset($result['list'])) {
            $jobs = array_combine_by_key($result['list'], 'jobId');
            return $jobs;
        }

        return [];
    }

    /**
     * 角色信息
     *
     * @return array|bool
     */
    public function Role()
    {
        $role_sdk = new Role(Service::instance());
        $result = $role_sdk->listAll(array(), 1, 5000);

        if (is_array($result) && isset($result['list'])) {
            $roles = array_combine_by_key($result['list'], 'roleId');
            return $roles;
        }

        return [];
    }

    /**
     * 标签信息
     *
     * @return array|bool|mixed
     */
    public function Tag()
    {

        $tagSdk = new Tag(Service::instance());
        $tags = $tagSdk->listAll(['pageSize' => 1000]);
        $tags = array_combine_by_key($tags, 'tagId');

        return $tags;
    }

    /**
     * 企业积分策略获取
     *
     * @return mixed
     */
    public function StrategySetting()
    {

        $integral = new Integral();
        $setting = $integral->getStrategySetting();

        if (is_array($setting) && isset($setting['eirsRuleSetList'])) {
            $setting['eirsRuleSetList'] = array_combine_by_key($setting['eirsRuleSetList'], 'irKey');
        }

        return $setting;
    }

    /**
     * 企业积分等级配置获取
     *
     * @return mixed
     */
    public function LevelSetting()
    {

        $integral = new Integral();
        $setting = $integral->getIntegralLevelSetting();

        return $setting;
    }

    /**
     * 企业积分公共配置获取
     *
     * @return mixed
     */
    public function EnterpriseIntgrlCommonSetting()
    {

        $integral = new Integral();
        $setting = $integral->getEpIntegralCommonSetting();

        return $setting;
    }

    /**
     * 获取企业配置信息
     *
     * @return bool|mixed
     */
    public function EnterpriseConfig()
    {

        $enterpriseSdk = new Enterprise(Service::instance());
        $enterpriseConfig = $enterpriseSdk->listSetting();

        return $enterpriseConfig;
    }

    /**
     * 用户可见范围 （权限）
     */
    public function Jurisdiction()
    {

        $member = new Member(Service::instance());
        $jurisdiction = $member->appAllow();

        return $jurisdiction;
    }

    /**
     * 获取应用配置缓存
     * @return array
     */
    public function AppSetting()
    {

        $settingService = new SettingService();
        $settings = $settingService->list_by_conds(array());
        $result = array();
        foreach ($settings as $_setting) {
            if (SettingModel::TYPE_ARRAY == $_setting['type']) {
                $_setting['value'] = unserialize($_setting['value']);
            }

            $result[$_setting['key']] = $_setting;
        }

        return $result;
    }

    /**
     * 默认方法
     *
     * @author zhoutao
     * @time   2016-07-15 15:37:51
     *
     * @param $method
     * @param $args
     * @return bool|array
     */
    public function __call($method, $args)
    {

        /**
         * 以下缓存不存在时 先建立空缓存
         * User: Common/Common/User.class.php 用
         */
        $passMethod = ['User','Attach'];
        if (!in_array($method, $passMethod)) {
            E('_ERR_CACHE_UNDEFINED');
            return false;
        }

        return [];
    }
}
