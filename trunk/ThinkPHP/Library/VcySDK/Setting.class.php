<?php
/**
 * Setting.class.php
 * 公共的系统参数操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     gaoyaqiu
 * @version    1.0.0
 */
namespace VcySDK;

use VcySDK\Logger;
use VcySDK\Config;

class Setting
{

    /**
     * 接口调用类
     *
     * @var object|Service
     */
    private $serv = null;

    /**
     * 保存/修改系统参数
     * %s = {apiUrl}/b/{enumber}/business-setting/save
     *
     * @var string
     */
    const SAVE_URL = '%s/business-setting/save';

    /**
     * 删除系统参数
     * %s = {apiUrl}/b/{enumber}/business-setting/delete
     *
     * @var string
     */
    const DEL_URL = '%s/business-setting/delete';

    /**
     * 查询系统参数
     * %s = {apiUrl}/b/{enumber}/business-setting/find
     *
     * @var string
     */
    const FIND_URL = '%s/business-setting/find';



    /**
     * 初始化
     *
     * @param object $serv 接口调用类
     */
    public function __construct($serv)
    {

        $this->serv = $serv;
    }


    /**
     * @param array $params 系统参数
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function save($params)
    {

        return $this->serv->postSDK(self::SAVE_URL, $params, 'generateApiUrlE');
    }

    /**
     * @param array $params 系统参数
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function delete($params)
    {

        return $this->serv->postSDK(self::DEL_URL, $params, 'generateApiUrlE');
    }

    /**
     * @param array $params 系统参数
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function find($params)
    {

        return $this->serv->postSDK(self::FIND_URL, $params, 'generateApiUrlE');
    }




}
