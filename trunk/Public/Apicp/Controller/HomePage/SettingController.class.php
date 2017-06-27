<?php
/**
 * Created by PhpStorm.
 * User: gaoyaqiu
 * Date: 17/1/4
 * Time: 14:08
 */

namespace Apicp\Controller\HomePage;


use VcySDK\Setting;
use VcySDK\Service;

class SettingController extends AbstractController{

    // 测试使用
    protected $_require_login = false;

    protected $_serv;

    /**
     * VcySDK 系统参数操作类
     *
     * @type $_setting
     */
    protected $_setting;


    // 系统参数的开关key
    const SWITCH_KEY = 'switch';


    public function before_action($action = ''){
        if (! parent::before_action($action)) {
            return false;
        }

        $this->_serv = &Service::instance();
        $this->_setting = new Setting($this->_serv);

        return true;
    }


    public function Index_post(){
        // 获取图片验证码CODE
        $bsKey = I("post.bsKey");
        if(empty($bsKey)){
            $bsKey = self::SWITCH_KEY;
        }

        $bsValue = I("post.bsValue");
        $params = array();
        $params['bsKey'] = $bsKey;
        if(empty($bsValue)){
            // 查询key对应的value
            $data = $this->_setting->find($params);
        }else{
            $params['bsValue'] = $bsValue;
            $params['bsComment'] = $bsValue;
            // 保存
            $data = $this->_setting->save($params);
            $data['bsValue'] = $bsValue;
        }

        $this->_result = array(
            "bsValue" => isset($data['bsValue']) ? $data['bsValue'] : ''
        );

        return true;
    }
}