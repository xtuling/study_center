<?php
/**
 * 确认选择人员，记录选择记录，为常用人员做数据
 *
 * User: 原习斌
 * Date: 2016-08-26
 */
namespace Apicp\Controller\ChooseMem;

use Common\Service\CommonChooseService;
use Common\Model\CommonChooselogModel;

class ConfirmChooseController extends AbstractController
{

    /**
     * 要插入数据库的数据数组
     *
     * @var array
     */
    protected $_add_data = array();

    public function Index()
    {

        // 选择的用户ID数组
        $memArray = I('post.memArray');
        // 选择的标签ID数组
        $tagArray = I('post.tagArray');
        // 选择的部门ID数组
        $dpArray = I('post.dpArray');

        // 取出ID组成数组
        $memIDs = array_column($memArray, 'memID');
        $tagIDs = array_column($tagArray, 'tagID');
        $dpIDs = array_column($dpArray, 'dpID');

        // 组建用户、部门、标签的选择数据
        $this->_format_data($memIDs, CommonChooselogModel::CHOOSE_MEM);
        $this->_format_data($tagIDs, CommonChooselogModel::CHOOSE_TAG);
        $this->_format_data($dpIDs, CommonChooselogModel::CHOOSE_DEP);

        // 插入选人记录
        $this->_add_log();

        return true;
    }

    /**
     * 组建用户、部门、标签的数组
     *
     * @param array $ids  ID数组
     * @param int   $type 类型
     *
     * @return bool
     */
    protected function _format_data($ids, $type)
    {

        // 用户ID为空或不是数组就直接返回
        if (! is_array($ids) || empty($ids)) {
            return true;
        }

        // 循环用户ID，组建插入数据数组
        foreach ($ids as $v) {
            $this->_add_data[] = array(
                'choose_type' => intval($type),
                'chooseId' => $v,
                'eaId' => $this->_login->user['eaId']
            );
        }

        return true;
    }

    /**
     * 插入记录信息
     *
     * @return bool
     */
    protected function _add_log()
    {

        // 如果要插入的数据是空的，就不再往下走了
        if (empty($this->_add_data)) {
            return true;
        }

        $serv = new CommonChooseService();
        $serv->insert_all($this->_add_data);
        return true;
    }
}

