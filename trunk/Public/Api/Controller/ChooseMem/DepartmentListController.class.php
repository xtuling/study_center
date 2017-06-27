<?php
/**
 * 微信端选部门接口
 * DepartmentListController.class.php
 * $author$ 鲜彤
 * $date$   2017年06月08日
 */

namespace Api\Controller\ChooseMem;

use Common\Common\Department;

class DepartmentListController extends AbstractController
{

    // 默认页码
    const DEFAULT_PAGE = 1;

    // 默认页大小
    const DEFAULT_LIMIT = 100;


    /**
     * 部门公共类
     *
     * @type Department
     */
    protected $_department;


    public function before_action($action = '')
    {

        if (!parent::before_action($action)) {
            return false;
        }

        $this->_department = new Department();

        return true;
    }

    public function Index()
    {

        $dpID = I('post.dpId');
        $result = [];

        if (empty($dpID)) {
            $user = $this->_login->user;
            // 由于登录信息中返回的‘isChildDepartment’字段错误，故重新查询保证数据正确
            $myDp = $this->_department->listById(array_column($user['dpName'], 'dpId'));
            foreach ($myDp as $k => $v) {
                $result['list'][] = [
                    'dpId' => $v['dpId'],
                    'dpName' => $v['dpName'],
                    'isChildDepartment' => $v['isChildDepartment'],
                ];

            }

        } else {
            // 查询部分指定部门
            list($departments, $data) = $this->partdp($dpID, self::DEFAULT_PAGE, self::DEFAULT_LIMIT);
            $result = array(
                'list' => $departments,
            );
        }

        $this->_result = $result;

        return true;
    }

    /**
     * 获取部门列表
     *
     * @param string $dpParentId 上级部门ID
     * @param int $page 页码
     * @param int $limit 每页条数
     *
     * @return array
     */
    private function partdp($dpParentId, $page, $limit)
    {

        // 初始化
        $data = array();
        // 获取部门详情
        $dpIds = array_values($this->_department->list_childrens_by_cdid($dpParentId));
        $departments = $this->_department->listAll();
        $start = ($page - 1) * $limit;
        $end = $start + $limit;
        for (; $start < $end; $start++) {
            if (empty($dpIds[$start])) {
                break;
            }

            $currentDp = $departments[$dpIds[$start]];

            $data[$currentDp['dpId']] = array(
                'dpId' => $currentDp['dpId'],
                'dpName' => $currentDp['dpName'],
                'isChildDepartment' => $currentDp['isChildDepartment'],
            );
        }

        return array(array_values($data), array('total' => count($dpIds), 'pageNum' => $page, 'pageSize' => $limit));
    }


}
