<?php
/**
 * 签到配置表
 * @author: houyingcai
 * @email:  594609175@qq.com
 * @date :  2017-04-24 10:27:52
 * @version $Id$
 */

namespace Common\Service;

use Common\Model\ConfigModel;

class ConfigService extends AbstractService
{
    // 构造方法
    public function __construct()
    {
        $this->_d = new ConfigModel();

        parent::__construct();
    }

    /**
     * 设置默认数据
     * @return bool
     */
    public function set_default_data()
    {
        $sign_config = $this->_d->get_by_conds(array());

        if (empty($sign_config)) {

            // 默认数据文件所在路径
            $data_path = APP_PATH . 'Common' . D_S . 'Sql' . D_S . 'data.php';

            // 如果默认数据所在文件不存在就不再执行下面的
            if (!file_exists($data_path)) {

                return false;
            }

            // 读取默认数据
            $data = require $data_path;

            // 如果默认数据不是数组或者为空，就不继续执行剩下的了
            if (!is_array($data) || empty($data)) {

                return false;
            }

            // 循环默认数据，插入默认数据
            foreach ($data as $k => $v) {
                switch ($k) {
                    case 'sign_config_default':
                        $this->_d->insert_all($v);
                        break;
                }
            }
        }
        return true;
    }

    /**
     * 保存签到配置(后台)
     *
     * @param array $result  返回签到信息
     * @param array $reqData 请求数据
     *
     * @return bool
     */
    public function save_config(&$result, $reqData)
    {
        // 数据验证
        if (!$this->validate_params($reqData)) {

            return false;
        }

        // 获取签到配置信息
        $config = $this->_d->get_by_conds(array());

        // 配置信息是否存在
        if (empty($config)) {

            // 插入配置信息
            $this->_d->insert($reqData);

        } else {

            $reqData['integral_rules'] = serialize($reqData['integral_rules']);
            // 是否修改积分规则
            if ($reqData['integral_rules'] != $config['integral_rules']) {

                $reqData['rules_updated'] = MILLI_TIME;
            }

            // 更新配置信息
            $this->_d->update_by_conds(array(), $reqData);
        }

        return true;
    }

    /**
     * [验证签到配置数据]
     *
     * @param  [array] $data [签到配置数组]
     *
     * @return [array]
     */
    private function validate_params($data = array())
    {
        $data['sign_rules'] = addslashes($data['sign_rules']);

        // 如果不是数组或为空
        if (!is_array($data) || empty($data)) {

            $this->_set_error('_ERR_SIGN_CONFIG');

            return false;
        }

        // 签到周期不能为空
        if (empty($data['cycle']) || !intval($data['cycle'])) {

            $this->_set_error('_EMPTY_SIGN_CYCLE');

            return false;
        }

        // 积分规则不能有空数据
        if (empty($data['integral_rules'])) {

            $this->_set_error('_EMPTY_INTEGRAL_RULES');

            return false;

        } else {

            foreach ($data['integral_rules'] as $key => $val) {
                $integral = intval($val);
                if (!$integral) {

                    $this->_set_error('_EMPTY_INTEGRAL_RULES');

                    return false;
                }
            }

        }

        // 积分规则数据个数与周期不一致
        if (count($data['integral_rules']) != $data['cycle']) {

            $this->_set_error('_ERR_INTEGRAL_RULES');

            return false;
        }

        // 签到规则不能为空
        if (empty($data['sign_rules'])) {

            $this->_set_error('_EMPTY_SIGN_RULES');

            return false;
        }

        return true;
    }

}
