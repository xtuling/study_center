<?php
/**
 * Created by PhpStorm.
 * User: tangxingguo
 * Date: 2017/4/27
 * Time: 10:59
 */
namespace Apicp\Controller\Award;

use Com\PackageValidate;
use Common\Common\Constant;
use Common\Common\Integral;
use Common\Service\AwardService;
use Common\Service\RightService;

class ListController extends \Apicp\Controller\AbstractController
{
    /**
     * List
     * @author tangxingguo
     * @desc 激励列表
     * @param Int page:1 当前页
     * @param Int limit:20 每页数据总数
     * @return array 激励列表
                array(
                    'page' => 1, // 当前页
                    'limit' => 20, // 当前页条数
                    'total' => 100, // 总条数
                    'list' => array( // 列表数据
                        'award_id' => 1, // 激励ID
                        'award_action' => '第一个激励', // 激励行为
                        'description' => '哈哈', // 描述
                        'medals' => array( // 勋章信息
                            'im_id' => 3, // 勋章ID
                            'icon' => 'http://qy.vchangyi.com/icon.jpg', // 勋章图标URL或者前端路径
                            'icon_type' => 1, // 勋章图标来源（1=用户上传；2=系统预设）
                            'name' => '勋章1', // 勋章名称
                            'desc' => '这是一个勋章', // 勋章描述
                        ),
                        'right_obj' => '店长；经理；主管', // 发放对象
                    ),
                );
     */
    public function Index_post()
    {
        // 验证规则
        $rules = [
            'page' => 'integer',
            'limit' => 'integer',
        ];

        // 验证数据
        $validate = new PackageValidate($rules, [], array_keys($rules));
        $postData = $validate->postData;

        // 分页默认值
        $postData['page'] = isset($postData['page']) ? $postData['page'] : Constant::PAGING_DEFAULT_PAGE;
        $postData['limit'] = isset($postData['limit']) ? $postData['limit'] : Constant::PAGING_DEFAULT_LIMIT;
        list($start, $perpage) = page_limit($postData['page'], $postData['limit']);

        // 排序
        $order_option = ['created' => 'desc'];

        // 激励列表
        $awardServ = new AwardService();
        $awardList = $awardServ->list_by_conds([], [$start, $perpage], $order_option);
        if (!empty($awardList)) {
            // 勋章信息
            $integralServ = new Integral();
            $integralList = $integralServ->listMedal();
            if (!empty($integralList)) {
                $integralList = array_combine_by_key($integralList, 'im_id');
            }
            $rightServ = new RightService();
            foreach ($awardList as $k => $v) {
                $v['medals'] = isset($integralList[$v['medal_id']]) ? $integralList[$v['medal_id']] : [];
                $v['right_obj'] = $rightServ->getData(['award_id' => $v['award_id']]);
                $v['right_obj'] = $this->_formatRightData($v['right_obj']);
                $awardList[$k] = $v;
            }
        }

        $this->_result = [
            'page' => $postData['page'],
            'limit' => $postData['limit'],
            'total' => intval($awardServ->count_by_conds([])),
            'list' => $awardList,
        ];
    }

    /**
     * @desc 将发放对象转为字符串
     * @author tangxingguo
     * @param array $rightData 发放对象
     * @return string
     */
    private function _formatRightData($rightData)
    {
        $right_obj = '';
        $right_obj = $this->_implodeDate($rightData['dp_list'], 'dp_name', $right_obj);
        $right_obj = $this->_implodeDate($rightData['tag_list'], 'tag_name', $right_obj);
        $right_obj = $this->_implodeDate($rightData['user_list'], 'username', $right_obj);
        $right_obj = $this->_implodeDate($rightData['job_list'], 'job_name', $right_obj);
        $right_obj = $this->_implodeDate($rightData['role_list'], 'role_name', $right_obj);
        if ($rightData['is_all'] == Constant::RIGHT_IS_ALL_TRUE) {
            $right_obj = '全公司';
        }

        return $right_obj;
    }

    /**
     * @desc 将权限数组转字符串
     * @author tangxingguo
     * @param array $data 权限数据
     * @param string $key 需要转字串的字段
     * @param string $right_obj 发放对象字串
     * @return string
     */
    private function _implodeDate($data, $key, $right_obj)
    {
        if (!empty($data)) {
            $role_names = array_column($data, $key);
            if (!empty($right_obj)) {
                $right_obj .= ';';
            }
            $right_obj .= implode(';', $role_names);
        }
        return $right_obj;
    }
}
