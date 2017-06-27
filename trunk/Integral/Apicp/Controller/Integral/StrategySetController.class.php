<?php
/**
 * Created by IntelliJ IDEA.
 * 企业积分策略编辑
 * User: zhoutao
 * Date: 2016/11/15
 * Time: 上午10:07
 */

namespace Apicp\Controller\Integral;

use Common\Common\Cache;
use VcySDK\Integral;
use VcySDK\Service;

class StrategySetController extends AbstractController
{
    /**
     * 策略循环周期限制 单位范围
     */
    protected $irCycleUnitRange = [
        '天', '周', '月', '年'
    ];

    public function Index()
    {

        $updateData = $this->getUpdateData();

        try {
            $integral = new Integral(Service::instance());
            $integral->updateStrategy($updateData);
        } catch (\Exception $e) {
            E($e->getMessage());
            return false;
        }

        // 更新缓存
        $cache = &Cache::instance();
        $cache->set('Common.StrategySetting', null);

        return true;
    }

    /**
     * 获取提交的数据 和验证数据
     *
     * @return array|bool
     */
    protected function getUpdateData()
    {
        $irId = I('post.irId');
        $enable = I('post.enable');
        $irCycle = I('post.irCycle');
        $irCount = I('post.irCount');
        $irNumber = I('post.irNumber');

        if (empty($irId)) {
            E('_ERR_EMPTY_IRID');
            return false;
        }
        $updateData = [
            'irId' => $irId
        ];

        // 获取要修改的配置
        $cache = &Cache::instance();
        $strategySetting = $cache->get('Common.StrategySetting');
        $ruleList = $strategySetting['eirsRuleSetList'];
        $irIds = array_column($ruleList, 'irId');
        $ruleList = array_combine_by_key($ruleList, 'irId');

        // 数据验证
        if (!in_array($irId, $irIds)) {
            E('_ERR_POST_IRID_ISTEXIST');
            return false;
        }

        if (!in_array($enable, $this->isOpenArr)) {
            $updateData['enable'] = self::CLOSE;
        } else {
            $updateData['enable'] = $enable;
        }

        if ($irCycle != 0) {
            $irCycleEx = explode('|', $irCycle);
            // 策略循环周期判断 是否有 | 符号, 格式验证
            if (count($irCycleEx) != 2) {
                E('_ERR_POST_IRCYCLE_FORMAT');
                return false;
            }
            // 单位不在范围
            if ((int)$irCycleEx[0] < 0 || !in_array($irCycleEx[1], $this->irCycleUnitRange)) {
                E('_ERR_POST_IRCYCLE_UNIT');
                return false;
            }
        }
        $updateData['irCycle'] = $irCycle;

        if ($irCount < 0) {
            E('_ERR_POST_IRCOUNT_FORMAT');
            return false;
        } else {
            $updateData['irCount'] = $irCount;
        }

        if ($irNumber < 0) {
            E('_ERR_POST_IRNUMBER_FORMAT');
            return false;
        } else {
            $updateData['irNumber'] = $irNumber;
        }

        // 积分功能开启 有策略开关操作 关闭操作 已开启的策略数小于等于1 (报错) 当前是开启状态的
        if ($strategySetting['eirsEnable'] == self::OPEN && isset($updateData['enable'])
            && $updateData['enable'] == self::CLOSE && $strategySetting['openedRules'] <= 1 && $ruleList[$irId]['enable'] == self::OPEN) {
            E('_ERR_OPENEDRULES_CANT_NONE');
            return false;
        }

        // $miType 现在固定 mi_type0
        $updateData['miType'] = 'mi_type0';

        return $updateData;
    }
}
