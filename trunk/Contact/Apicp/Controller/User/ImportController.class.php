<?php
/**
 * 用户导入操作
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 22:45
 */

namespace Apicp\Controller\User;

use Common\Model\AttrModel;
use Common\Model\UserModel;
use Common\Service\AttrService;
use Common\Service\DepartmentService;
use Common\Service\ImportDataService;
use Common\Service\InviteUserService;
use Common\Service\UserService;
use VcySDK\Member;
use VcySDK\Service;

class ImportController extends AbstractController
{
    protected $_require_login = false;


    /**
     * 【通讯录】人员导入
     * @author liyifei
     */
    public function Index_post()
    {

        try {
            $title_id = (int)I('post.title_id');
            $index = (int)I('post.index');

            // 读取 title 和待导入数据
            $importDataService = new ImportDataService();
            $dataList = $importDataService->list_by_pks(array($title_id, $index));
            $dataList = array_combine_by_key($dataList, 'cid_id');
            // 如果数据不存在
            if (empty($dataList[$title_id]) || empty($dataList[$index])) {
                E('1006:数据错误');
                return true;
            }

            // 获取组织类型
            $titleData = json_decode($dataList[$title_id]['data']);
            $importData = json_decode($dataList[$index]['data']);
            // 新增导入失败原因
            $titleData[] = '失败原因';
            $this->_result['title'] = $titleData;
            $this->_result['data'] = $importData;

            $attrServ = new AttrService();
            $attrs = $attrServ->getAttrList(true, array(), true);
            $attrs = array_combine_by_key($attrs, 'attr_name');

            // 导入数据
            $member = array();
            $ignoreAttrs = array(
                AttrModel::ATTR_TYPE_PICTURE
            );
            foreach ($titleData as $_k => $_title) {
                $attr = $attrs[$_title];
                if (empty($attr) || in_array($attr['type'], $ignoreAttrs)) {
                    continue;
                }

                // 如果是多选
                if (AttrModel::ATTR_TYPE_CHECKBOX == $attr['type']) {
                    $explodes = explode(';', $importData[$_k]);
                    $selected = array();
                    foreach ($attr['option'] as $_attr) {
                        if (in_array($_attr['name'], $explodes)) {
                            $selected[] = $_attr;
                        }
                    }
                    $member[$attr['field_name']] = serialize($selected);
                    continue;
                } elseif (AttrModel::ATTR_TYPE_RADIO == $attr['type'] || AttrModel::ATTR_TYPE_DROPBOX == $attr['type']) {
                    $value = '';
                    foreach ($attr['option'] as $_attr) {
                        if ($_attr['name'] == $importData[$_k]) {
                            $value = $_attr['value'];
                            break;
                        }
                    }
                    $importData[$_k] = $value;
                } elseif (AttrModel::ATTR_TYPE_DATE == $attr['type']) {
                    $importData[$_k] = rstrtotime($importData[$_k]) * 1000;
                }

                $member[$attr['field_name']] = $importData[$_k];
            }

            // 调用验证接口,验证参数传值是否符合规范
            $errors = $attrServ->checkValue($member);
            if (!empty($errors)) {
                E($errors[0]);
            }

            // 获取部门信息
            $dpIds = $this->_getDpIdByName($member['dpName']);
            if (empty($dpIds)) {
                E('1007:组织不存在');
                return false;
            }
            $member['dpIdList'] = $dpIds;
            unset($member['dpName']);

            // 先搜索用户
            $memberSDK = new Member(Service::instance());
            $memberResult = array();
            if (!empty($member['memMobile'])) {
                $condition = array('memMobile' => $member['memMobile']);
                $memberResult = $memberSDK->listAll($condition);
            }
            if (empty($memberResult) && !empty($member['memWeixin'])) {
                $condition = array('memWeixin' => $member['memWeixin']);
                $memberResult = $memberSDK->listAll($condition);
            }
            if (empty($memberResult) && !empty($member['memEmail'])) {
                $condition = array('memEmail' => $member['memEmail']);
                $memberResult = $memberSDK->listAll($condition);
            }
            // 如果用户存在, 则
            if (!empty($memberResult['list']) && 1 == count($memberResult['list'])) {
                $uid = $memberResult['list'][0]['memUid'];
            } else {
                $uid = 0;
            }

            if (0 == $uid) {
                // 邀请记录信息
                $member['memJoinType'] = UserService::ADMIN_ADD_JOIN;
                if (!empty($this->_login->user['eaRealname'])) {
                    $member['memJoinInviter'] = $this->_login->user['eaRealname'];
                } elseif (!empty($this->_login->user['eaMobile'])) {
                    $member['memJoinInviter'] = $this->_login->user['eaMobile'];
                } elseif (!empty($this->_login->user['eaEmail'])) {
                    $member['memJoinInviter'] = $this->_login->user['eaEmail'];
                } else {
                    $member['memJoinInviter'] = '';
                }
            }

            // 更新用户信息
            $userService = new UserService();
            $this->_result = $userService->saveUser($uid, $member);

            $this->clearUserCache();
        } catch (\Exception $e) {
            // 如果读取数据正常, 则抛错
            if (!empty($this->_result['data'])) {
                $importDataService = new ImportDataService();
                $importDataService->update($index, array('is_error' => 1, 'fail_message' => $errors[0]));
                // 错误列表新增失败原因
                $importData[] = $errors[0];
                $this->_result['data'] = $importData;
            }

            throw $e;
        }

        return true;
    }

    /**
     * 通过部门名称获取部门ID
     * @param $dpName
     * @return array
     */
    protected function _getDpIdByName($dpName)
    {

        $dpIds = array();
        $dpNames = explode(';', $dpName);
        $departmentService = new DepartmentService();
        foreach ($dpNames as $_name) {
            $department = array();
            $departmentService->getDepartmentByPath($department, $_name, '', false);
            if (empty($department['dpId'])) {
                continue;
            }

            $dpIds[] = $department['dpId'];
        }

        return $dpIds;
    }

}
