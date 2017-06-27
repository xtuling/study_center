<?php
/**
 * Enterprise.class.php
 * 企业同步通讯录状态查询操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhuxun37
 * @version    1.0.0
 */
namespace VcySDK;

class EnterpriseSync
{

    /**
     * 接口调用类
     *
     * @var object|Service
     */
    private $serv = null;

    /**
     * 企业同步通讯录状态查询接口
     * %s = {apiUrl}/s/enterprise/
     *
     * @var string
     */
    const ADDRESS_RESULT = '%s/sync_address_result';

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
     * 企业同步通讯录状态查询
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function addressResult()
    {

        return $this->serv->postSDK(self::ADDRESS_RESULT, [], 'generateApiUrlE');
    }
}
