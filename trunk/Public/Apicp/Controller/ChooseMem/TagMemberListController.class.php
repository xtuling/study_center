<?php
/**
 * 选人组件-标签人员相关接口
 * Created by PhpStorm.
 * User: 何岳龙
 * Date: 2016年8月31日15:59:47
 */
namespace Apicp\Controller\ChooseMem;

use Common\Common\Cache;
use Common\Common\Department;
use Common\Common\Tag;
use Common\Common\User;

class TagMemberListController extends AbstractController
{

    public function Index()
    {

        // 标签ID
        $tagId = I('post.tagId', '', 'trim');
        // 判断标签ID不能为空
        if (empty($tagId)) {
            $this->_set_error('_ERR_EMPTY_TAG_ID');
            return false;
        }

        // 调用SDK获取用户列表
        $memList = Tag::instance()->listUserByTagId($tagId, self::PAGE, self::MAX_LIMIT);
        // 获取用户UIDS
        $memUids = array_filter(array_column($memList['list'], 'memUid'));
        sort($memUids);

        // 获取部门IDS
        $dpIds = array_filter(array_column($memList['list'], 'dpId'));
        sort($dpIds);

        // 初始化部门列表
        $departments = array();
        // 验证部门IDS
        if (is_array($dpIds) && ! empty($dpIds)) {
            // 初始化部门类
            $department = new Department();
            // 获取缓存部门列表
            $dplist = $department->listById($dpIds);

            // 遍历部门
            foreach ($dplist as $_v) {
                $departments[] = array(
                    'dpId' => $_v['dpId'],
                    'dpName' => $_v['dpName'],
                );
            }
        }

        // 初始人员列表
        $members = array();
        // 验证人员UIDS
        if (is_array($memUids) && ! empty($memUids)) {
            // 初始化用户类
            $user = new User();
            // 获取列表
            $list = $user->listByUid($memUids);
            // 格式化人员列表
            foreach ($list as $_v) {
                $members[] = array(
                    'memUid' => $_v['memUid'],
                    'memUsername' => $_v['memUsername'],
                    'memFace' => $_v['memFace']
                );
            }
        }

        // 返回值
        $this->_result = array(
            'list' => array(
                'mems' => $members,
                'dps' => $departments
            )
        );

        return true;
    }

}
