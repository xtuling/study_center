<?php
/**
 * Integral.class.php
 * 积分操作
 * $Author$
 */

namespace Common\Common;

use VcySDK\Service;
use VcySDK\Exception;
use Think\Log;
use VcySDK\Integral as IntegralSDK;

class Integral
{

    /** SDK对象 */
    protected $sdk = null;

    /**
     * 单例实例化
     *
     * @return Integral
     */
    public static function &instance()
    {

        static $instance;
        if (empty($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public function __construct()
    {

        $this->sdk = new IntegralSDK(Service::instance());

        return true;
    }

    /**
     * 获取企业积分策略配置
     * @return mixed
     */
    public function getStrategySetting()
    {

        return $this->sdk->getStrategySetting();
    }

    /**
     * 获取企业积分等级配置
     * @return mixed
     */
    public function getIntegralLevelSetting()
    {

        return $this->sdk->getIntegralLevelSetting();
    }

    /**
     * 获取企业积分公共配置
     * @return mixed
     */
    public function getEpIntegralCommonSetting()
    {

        return $this->sdk->getEpIntegralCommonSetting();
    }

    /**
     * 微信端积分增减接口（系统异步操作）
     * @param $params
     * + memUid String 用户ID
     * + irKey String 积分策略key
     * + miType String 积分类型 (默认mi_type0)
     * @return array|bool
     * + irNumber int 增加的积分
     * + irName string 积分项名称
     */
    public function asynUpdateIntegral($params)
    {

        if (empty($params['memUid']) || empty($params['irKey'])) {
            return false;
        }

        try {
            $this->sdk->asynUpdateIntegral($params);
        } catch (Exception $e) {
            $message = $e->getMessage();
            $sdkCode = $e->getSdkCode();
            Log::record("添加积分失败，message：{$message}，sdkcode：{$sdkCode}");
            return false;
        }

        // 获取加的积分值
        $cache = Cache::instance();
        $setting = $cache->get('Common.StrategySetting');
        $rule = $setting['eirsRuleSetList'];

        // UC 异步处理增加减积分,故这里直接返回数值
        return [
            'irNumber' => $rule[$params['irKey']]['irNumber'],
            'irName' => $rule[$params['irKey']]['irName']
        ];
    }

    /**
     * 获取勋章列表
     * @param $imId Int/Array 勋章ID
     * @param $name String 勋章名称
     * @return mixed
     */
    public function listMedal($imId = 0, $name = '')
    {

        $rpcURL = call_user_func_array('sprintf', [
            '%s://%s/%s/Integral/Rpc/Medal/List',
            $_SERVER['REQUEST_SCHEME'],
            $_SERVER['HTTP_HOST'],
            QY_DOMAIN
        ]);

        return $this->requestRpc($rpcURL, [
            $imId, $name
        ]);
    }

    /**
     * 赋予人员勋章
     * @param $imId        int 勋章ID
     * @param $memUid      string 人员ID
     * @param $memUsername string 人员姓名
     * @return mixed
     */
    public function endowMedal($imId, $memUid, $memUsername)
    {

        $rpcUrl = call_user_func_array('sprintf', [
            '%s://%s/%s/Integral/Rpc/Medal/Endow',
            $_SERVER['REQUEST_SCHEME'],
            $_SERVER['HTTP_HOST'],
            QY_DOMAIN,
        ]);

        return $this->requestRpc($rpcUrl, [
            $imId, $memUid, $memUsername
        ]);
    }

    /**
     * 提交RPC
     * @param      $rpcUrl
     * @param null $postData
     * @return mixed
     */
    private function requestRpc($rpcUrl, $postData = null)
    {

        if (is_null($postData)) {
            return call_user_func(array(\Com\Rpc::phprpc($rpcUrl), 'Index'));
        } else {
            return \Com\Rpc::phprpc($rpcUrl)->invoke('Index', $postData);
        }
    }

    /**
     * 通过UID获取积分信息
     * @param $uids
     * @return array
     */
    public function listByUid($uids)
    {

        $list = $this->sdk->integralMemberList(array('memUids' => $uids));
        return array_combine_by_key($list['list'], 'memUid');
    }

    /**
     * 获取用户的积分排名
     * @param $memUid
     * @param $dpIds
     * @param $jobId
     * @return mixed
     */
    public function getUserIntegralRank($memUid, $dpIds, $jobId) {
        return $this->sdk->getRanking([
            'memUid' => $memUid,
            'dpIds' => $dpIds,
            'jobId' => $jobId
        ]);
    }
}
