<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/8/1
 * Time: 下午5:09
 */

namespace Common\Common;

use VcySDK\Service;
use VcySDK\Tag as SdkTag;

class Tag
{
    /**
     * 分页查询最大数据条数
     */
    const LIST_MAX_PAGE_SIZE = 1000;

    /**
     * VcySDK 标签操作类
     *
     * @type SdkTag
     */
    protected $_tagSDK = null;

    /**
     * 实例化
     *
     * @return \Common\Common\Tag
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
     * 构造方法
     */
    public function __construct()
    {

        return $this->_tagSDK = new SdkTag(Service::instance());
    }

    /**
     * 创建标签
     *
     * @param array $params 提交数据
     *                      + string $tagName 标签名称，长度为1~64个字节，标签名不可与其他标签重名
     *                      + string $callbackUrl 异步处理创建标签完成时，回调业务的地址(http POST)
     *                      + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     *
     * @return mixed
     */
    public function create($params)
    {

        return $this->_tagSDK->create($params);
    }

    /**
     * 更新标签
     *
     * @param array $params 提交数据
     *                      + string $tagId 系统标签ID，非微信标签ID
     *                      + string $tagName 标签名称，长度为1~64个字节，标签名不可与其他标签重名
     *                      + string $callbackUrl 异步处理创建标签完成时，回调业务的地址(http POST)
     *                      + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     *
     * @return mixed
     */
    public function update($params)
    {

        return $this->_tagSDK->update($params);
    }

    /**
     * 删除标签
     *
     * @param array $params 提交数据
     *                      + string $tagId 系统标签ID，非微信标签ID
     *                      + string $callbackUrl
     *                      + string $callbackUrl 异步处理创建标签完成时，回调业务的地址(http POST)
     *                      + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     *
     * @return mixed
     */
    public function delete($params)
    {

        return $this->_tagSDK->delete($params);
    }

    /**
     * 获取所有标签信息
     *
     * @param array $tagIds 标签ID
     * @param array $params
     *
     * @return array
     */
    public function listAll($tagIds, $params = array())
    {

        $condition = [
            // 当前页
            'pageNum' => 1,
            // 分页大小
            'pageSize' => self::LIST_MAX_PAGE_SIZE
        ];

        if (is_array($tagIds) && !empty($tagIds)) {
            $condition['tagIds'] = $tagIds;
        }

        if (is_array($params) && !empty($params)) {
            $condition = array_merge($condition, $params);
        }

        $page_max = 0;
        $list = [];

        // 获取所有用户
        do {
            $result = $this->_tagSDK->listAll($condition);

            if (isset($result['list']) && !empty($result['list'])) {
                $list = array_merge($list, $result['list']);
            }

            // 计算总页数
            if ($page_max === 0) {
                $page_max = ceil($result['total'] / $condition['pageSize']);
            }

            $condition['pageNum']++;
        } while ($condition['pageNum'] <= $page_max);

        return $list;
    }

    /**
     * 标签人员列表
     *
     * @param array $condi    系统标签ID, 非微信标签ID
     * @param int   $pageNum  当前页，空时默认为 1
     * @param int   $pageSize 页大小,空时默认为 20, 最大 1000
     *
     * @return mixed
     */
    public function listUserAll($condi, $pageNum = 1, $pageSize = 20)
    {

        if (is_array($condi)) {
            $condi['pageNum'] = $pageNum;
            $condi['pageSize'] = $pageSize;
        }

        return $this->_tagSDK->listUserAll($condi);
    }

    public function listUserByTagId($tagIds, $pageNum = 1, $pageSize = 20)
    {

        return $this->listUserAll(array('tagIds' => (array)$tagIds), $pageNum, $pageSize);
    }

    /**
     * 添加标签成员
     *
     * @param array $params 提交数据
     *                      + string $tagId 系统标签ID, 非微信标签ID
     *                      + array $userIds 人员ID
     *                      + array $partyIds 系统部门ID
     *                      + string $callbackUrl 异步处理添加标签成员完成时，回调业务的地址(http POST)
     *                      + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     *
     * @return mixed
     */
    public function addUsers($params)
    {

        return $this->_tagSDK->addUsers($params);
    }

    /**
     * 删除标签成员
     *
     * @param array $params 提交数据
     *                      + string $tagId 系统标签ID, 非微信标签ID
     *                      + array $userIds 人员ID
     *                      + array $partyIds 系统部门ID
     *                      + string $callbackUrl 异步处理添加标签成员完成时，回调业务的地址(http POST)
     *                      + string $callbackParams JSON格式字符串，业务系统自定义参数，回调业务时原样返回
     *
     * @return mixed
     */
    public function delUsers($params)
    {

        return $this->_tagSDK->delUsers($params);
    }

    /**
     * 清空指定标签下所有成员（同步）
     *
     * @param array $params 提交数据
     *                      + string $tagId 系统标签ID, 非微信标签ID
     *
     * @return mixed
     */
    public function delTagUsers($params)
    {

        return $this->_tagSDK->delTagUsers($params);
    }

    /**
     * 根据条件获取所有成员信息
     *
     * @param array $condition 查询条件
     *                         + tagIds array|string 标签ID
     *                         + tagUserName string 标签成员名称(模糊查询)
     *
     * @return array
     */
    public function listAllMember($condition = [])
    {

        $page = 1;
        $page_max = 0;
        $list = [];

        // 获取所有成员
        do {
            $condition['pageNum'] = $page;
            $condition['pageSize'] = self::LIST_MAX_PAGE_SIZE;
            $result = $this->_tagSDK->listUserAll($condition);

            if (isset($result['list']) && !empty($result['list'])) {
                $list = array_merge($list, $result['list']);
            }

            // 计算总页数
            if ($page_max === 0) {
                $page_max = ceil($result['total'] / self::LIST_MAX_PAGE_SIZE);
            }

            $page++;
        } while ($page <= $page_max);

        return $list;
    }
}
