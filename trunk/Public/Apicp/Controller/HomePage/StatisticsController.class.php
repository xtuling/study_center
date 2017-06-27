<?php
/**
 * 管理后台-首页
 * CreateBy：zs_anything
 * Date：2016-01-03
 *
 */

namespace Apicp\Controller\HomePage;

use Common\Common\Department;
use VcySDK\Member;
use VcySDK\Service;
use VcySDK\Adminer;

class StatisticsController extends AbstractController
{

    /**
     * 在职状态:实习
     */
    const ACTIVE_TYPE_INTERNSHIP = 2;

    /**
     * 在职状态:试用
     */
    const ACTIVE_TYPE_PROBATION = 3;

    /**
     * 在职状态:正式
     */
    const ACTIVE_TYPE_FORMAL = 1;

    /**
     * 在职状态:离职
     */
    const ACTIVE_TYPE_QUIT = 4;

    /**
     * 在职状态: 退休
     */
    const ACTIVE_TYPE_RETIRE = 5;


    /**
     * VcySDK 用户操作类
     *
     * @type Member
     */
    protected $_mem;


    /**
     * VcySDK 部门操作类
     *
     * @type Department
     */
    protected $_department;


    protected $_serv;

    public function before_action($action = ''){
        if (! parent::before_action($action)) {
            return false;
        }


        $this->_serv = &Service::instance();
        $this->_department = new Department();
        $this->_mem = new Member($this->_serv);

        return true;
    }

    public function Index_post()
    {

        $adminerSdk = new Adminer($this->_serv);
        // 管理员、角色统计
        $adminerAndRoleTotal = $adminerSdk->adminerAndRoleTotal();

        $memSdk = new Member($this->_serv);
        // 人员统计
        $memRelevantTotal = $memSdk->memberRelevantTotal();

        // 人员在职情况, 按照在职类型统计（1：正式 2：实习 3：试用 4：离职 5：退休）
        $memActiveTotal = $memSdk->memberActiveTotal();
        if(!empty($memActiveTotal)){
            foreach ($memActiveTotal as $key => &$v) {
                switch ($v['memActive']) {
                    case self::ACTIVE_TYPE_FORMAL:
                        $v['memJobName'] = "正式";
                        break;
                    case self::ACTIVE_TYPE_INTERNSHIP:
                        $v['memJobName'] = "实习";
                        break;
                    case self::ACTIVE_TYPE_PROBATION:
                        $v['memJobName'] = "试用";
                        break;
                    case self::ACTIVE_TYPE_QUIT:
                        $v['memJobName'] = "离职";
                        break;
                    case self::ACTIVE_TYPE_RETIRE:
                        $v['memJobName'] = "退休";
                        break;
                    default:
                        $v['memJobName'] = "未知";
                        break;
                }
            }
        }

        // 按人员数量降序排列
        $allDep = $this->_department->listAll();
        multi_array_sort($allDep, array_column($allDep, 'user_total'), SORT_NUMERIC, SORT_DESC);
        foreach ($allDep as &$item) {
            $item = [
                'dpId' => $item['dpId'],
                'dpName' => $item['dpName'],
                'isChildDepartment' => $item['isChildDepartment'],
                'order' => $item['dpDisplayorder'],
                'user_total' => $item['departmentMemberCount'],
                'dept_level' => $item['dpLevel'],
            ];
        }

        $this->_result = array(
            'member' => $memRelevantTotal,
            'adminer' => $adminerAndRoleTotal,
            'memActiveGroup' => $memActiveTotal,
            'departments' => array_values($allDep)
        );

        return true;
    }
}
