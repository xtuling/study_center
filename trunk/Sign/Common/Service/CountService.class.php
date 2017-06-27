<?php
/**
 * 签到统计表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-04-24 10:36:58
 * @version $Id$
 */

namespace Common\Service;

use Common\Common\Integral;
use Common\Model\ConfigModel;
use Common\Model\CountModel;
use Common\Model\RecordModel;

class CountService extends AbstractService
{
    /** 没有签到 */
    const NO_SIGN = 0;

    /** 已经签到 */
    const HAS_SIGN = 1;

    /** 签到状态 */
    const SIGN_STATUS = 1;

    /** 每次增加次数 */
    const SIGN_ADD_TIME = 1;

    /** 默认连续签到次数 */
    const DEFAULT_SIGN_NUM = 1;

    /** 已经签积分获得数 */
    const HAS_SIGN_INGEGRAL = 0;

    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new CountModel();

    }

    /**
     * 用户签到(手机端)
     *
     * @param array $result  返回签到信息
     * @param array $user    用户信息
     *
     * @return bool
     */
    public function user_sign(&$result, $user)
    {
        $serv_config = new ConfigModel();
        $serv_record = new RecordModel();

        // 获取签到配置信息
        $config = $serv_config->get_by_conds(array());

        // 积分规则
        $integral_rules = unserialize($config['integral_rules']);
        // 获取当前用户的签到统计信息
        $uid        = $user['memUid'];
        $username   = $user['memUsername'];
        $user_count = $this->_d->get_by_conds(array('uid' => $uid));

        // 初始化签到状态
        $is_sign = self::NO_SIGN;

        // 初始签到获得积分数
        $sign_integral = $integral_rules[0];

        // 初始化连续签到次数
        $continuous = self::DEFAULT_SIGN_NUM;

        try {

            $this->_d->start_trans();

            $count_info = array(
                'username'  => $username,
                'last_time' => MILLI_TIME,
            );

            // 初始化签到状态
            $sign_status = '';

            //【1】当前用户是否有签到信息
            if (empty($user_count)) {

                // 插入签到统计表
                $count_info['uid']        = $uid;
                $count_info['continuous'] = $continuous;
                $count_info['sign_nums']  = $continuous;
                $count_info['integrals']  = $sign_integral;

                $res = $this->_d->insert($count_info);

            } else {
                // 签到状态
                $sign_status = rgmdate(NOW_TIME, 'Ymd') - rgmdate(to_second_time($user_count['last_time']), 'Ymd');
            }

            //【2】当前用户今日已签到
            if (rgmdate(NOW_TIME, 'Ymd') == rgmdate(to_second_time($user_count['last_time']), 'Ymd')) {

                $is_sign       = self::HAS_SIGN;
                $sign_integral = self::HAS_SIGN_INGEGRAL;
                $continuous    = $user_count['continuous'];
            }

            //【3】当前用户是否签到中断过
            if ($sign_status > self::SIGN_STATUS) {

                // 更新签到统计表
                $count_info['continuous'] = $continuous;
                $count_info['sign_nums']  = $user_count['sign_nums'] + self::SIGN_ADD_TIME;
                $count_info['integrals']  = $sign_integral + $user_count['integrals'];

                $res = $this->_d->update_by_conds(array('uid' => $uid), $count_info);
            }

            //【4】当前用户是否是连续签到
            if ($sign_status == self::SIGN_STATUS) {

                // 管理员是否中途修改过配置信息
                $updated   = to_second_time($config['rules_updated']);
                $is_update = rgmdate($updated, 'Ymd') > rgmdate($user_count['last_time'], 'Ymd') ? true : false;

                // 连续签到次数是否达到周期上限
                $is_max_cycle = $user_count['continuous'] >= $config['cycle'] ? true : false;

                // 未修改配置信息且未达周期上限
                if (!$is_update && !$is_max_cycle) {

                    // 今日签到获得积分数
                    $sign_integral = $integral_rules[$user_count['continuous']];
                    // 更新连续签到次数
                    $continuous = $user_count['continuous'] + self::SIGN_ADD_TIME;

                }
                
                // 更新签到统计表
                $count_info['continuous'] = $continuous;
                $count_info['sign_nums']  = $user_count['sign_nums'] + self::SIGN_ADD_TIME;
                $count_info['integrals']  = $sign_integral + $user_count['integrals'];

                $res = $this->_d->update_by_conds(array('uid' => $uid), $count_info);
            }

            // 插入积分记录表
            if ($res) {

                // 插入记录表
                $record_info = array(
                    'uid'           => $uid,
                    'username'      => $username,
                    'sign_integral' => $sign_integral,
                );
                $serv_record->insert($record_info);
            }

            // 事务提交
            $this->_d->commit();

            // 返回签到信息
            $result = array(
                'cycle'         => (int)$config['cycle'],
                'continuous'    => (int)$continuous,
                'sign_integral' => $sign_integral,
                'is_sign'       => $is_sign,
            );

            // 修改用户积分数
            if ($sign_integral > 0) {
            
                $integralUtil = &Integral::instance();
                $integralUtil->asynUpdateIntegral(array(
                    'memUid' => $uid,
                    'miType' => 'mi_type0',
                    'irKey' => 'dt_sign',
                    'remark' => '签到',
                    'integral' => intval($sign_integral)
                ));
            }

            return true;

        } catch (\Think\Exception $e) {
            $this->_set_error($e->getMessage(), $e->getCode());

            // 事务回滚
            $this->_d->rollback();

            return false;

        } catch (\Exception $e) {
            $this->_set_error($e->getMessage(), $e->getCode());

            // 事务回滚
            $this->_d->rollback();

            return false;
        }
    }

}
