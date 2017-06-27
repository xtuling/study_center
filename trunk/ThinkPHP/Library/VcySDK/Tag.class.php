<?php
/**
 * Tag.class.php
 * 标签操作类
 *
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt LGPL
 * @copyright  Copyright (c) 2014 - ? VcySDK (http://www.vchangyi.com/)
 * @author     zhoutao
 * @version    1.0.0
 */

namespace VcySDK;

class Tag
{
    // 根据微信企业号应用的可见范围过滤权限 1: 不需要
    const PERMISSION_TYPE_FALSE = 1;

    /**
     * 接口调用类
     *
     * @var object
     */
    private $serv = null;

    /**
     * 初始化
     *
     * @param Service $serv 接口调用类
     */
    public function __construct(Service $serv)
    {

        $this->serv = $serv;
    }

    /**
     * 创建标签（异步）
     * %s = {apiurl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @param String
     */
    const CREATE_URL = '%s/tag/create';

    /**
     * 更新标签（异步）
     * %s = {apiurl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @param String
     */
    const UPDATE_URL = '%s/tag/update';

    /**
     * 删除标签（异步）
     * %s = {apiurl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @param String
     */
    const DELETE_URL = '%s/tag/delete';

    /**
     * 标签列表（同步）
     * %s = {apiurl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @param String
     */
    const LIST_URL = '%s/tag/list';

    /**
     * 标签人员列表（同步）
     * %s = {apiurl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @param String
     */
    const LIST_USER_URL = '%s/tag/listUsers';

    /**
     * 添加标签成员（异步）
     * %s = {apiurl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @param String
     */
    const ADD_USER_URL = '%s/tag/addUsers';

    /**
     * 删除标签成员（异步）
     * %s = {apiurl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @param String
     */
    const DEL_USER_URL = '%s/tag/delUsers';

    /**
     * 清空指定标签下所有成员（同步）
     * %s = {apiurl}/a/{enumber}/{pluginIdentifier}/{thirdIdentifier}
     *
     * @param String
     */
    const CLEAR_TAG_USERS = '%s/tag/clearTagUsers';

    /**
     * 创建标签（异步）
     *
     * @param array $params 提交数据
     *        + string $tagName 标签名称，长度为1~64个字节，标签名不可与其他标签重名
     *        + string $callbackUrl 异步处理创建标签完成时，回调业务的地址(http POST)
     *        + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function create($params)
    {

        return $this->serv->postSDK(self::CREATE_URL, $params, 'generateApiUrlA');
    }

    /**
     * 更新标签（异步）
     *
     * @param array $params 提交数据
     *        + string $tagId 系统标签ID，非微信标签ID
     *        + string $tagName 标签名称，长度为1~64个字节，标签名不可与其他标签重名
     *        + string $callbackUrl 异步处理创建标签完成时，回调业务的地址(http POST)
     *        + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function update($params)
    {

        return $this->serv->postSDK(self::UPDATE_URL, $params, 'generateApiUrlA');
    }

    /**
     * 删除标签(异步)
     *
     * @param array $params 提交数据
     *        + string $tagId 系统标签ID，非微信标签ID
     *        + string $callbackUrl
     *        + string $callbackUrl 异步处理创建标签完成时，回调业务的地址(http POST)
     *        + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function delete($params)
    {

        return $this->serv->postSDK(self::DELETE_URL, $params, 'generateApiUrlA');
    }

    /**
     * 标签人员列表(同步)
     *
     * @param $params
     *        + tagIds Array（String） 系统标签ID, 非微信标签ID
     *        + tagUserName String 标签成员名称(模糊查询)
     *        + pageNum Integer 当前页，空时默认为1
     *        + pageSize Integer 页大小,空时默认为20,最大1000
     * @return array|bool
     */
    public function listUserAll($params)
    {
        return $this->serv->postSDK(self::LIST_USER_URL, $params, 'generateApiUrlA');
    }

    /**
     * 标签列表(同步)
     *
     * @param $params
     *        + tagIds Array（String） 标签ID列表
     *        + tagThirdIds Array（String）微信标签ID列表, 当tagIds不为空忽略当前参数
     *        + pageNum Integer 当前页，空时默认为1
     *        + pageSize Integer 页大小,空时默认为20,最大1000
     * @return array|bool
     */
    public function listAll($params)
    {

        return $this->serv->postSDK(self::LIST_URL, $params, 'generateApiUrlA');
    }

    /**
     * 添加标签成员（异步)
     *
     * @param array $params 提交数据
     *        + string $tagId 系统标签ID, 非微信标签ID
     *        + array $userIds 人员ID
     *        + array $partyIds 系统部门ID
     *        + string $callbackUrl 异步处理添加标签成员完成时，回调业务的地址(http POST)
     *        + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function addUsers($params)
    {

        return $this->serv->postSDK(self::ADD_USER_URL, $params, 'generateApiUrlA');
    }

    /**
     * 删除标签成员（异步)
     *
     * @param array $params 提交数据
     *        + string $tagId 系统标签ID, 非微信标签ID
     *        + array $userIds 人员ID
     *        + array $partyIds 系统部门ID
     *        + string $callbackUrl 异步处理添加标签成员完成时，回调业务的地址(http POST)
     *        + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     * @return bool|mixed
     * @throws Exception
     */
    public function delUsers($params)
    {

        return $this->serv->postSDK(self::DEL_USER_URL, $params, 'generateApiUrlA');
    }

    /**
     * 清空指定标签下所有成员（同步）
     *
     * @param array $params 提交数据
     *                      + tagId String 系统标签ID, 非微信标签ID
     *
     * @return bool|mixed
     * @throws Exception
     */
    public function delTagUsers($params)
    {

        return $this->serv->postSDK(self::CLEAR_TAG_USERS, $params, 'generateApiUrlA');
    }
}
