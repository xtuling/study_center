<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/17
 * Time: 22:43
 */

namespace Apicp\Controller\User;

use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\UserService;

class SaveController extends AbstractController
{

    /**
     * 用户加入方式: 管理员添加
     */
    const ADMIN_ADD_JOIN = 1;

    /**
     * 【通讯录】人员保存
     * @author liyifei
     * @time   2016-09-17 22:43:59
     */
    public function Index_post()
    {

        $uid = I('post.uid', '', 'trim');
        $list = I('post.list');
        $dpIds = I('post.dp_ids');
        if (empty($list) || empty($dpIds)) {
            E('_ERR_PARAM_IS_NULL');
        }

        // 以架构接口参数字段为键,拼接用户信息
        $attrServ = new AttrService();
        $attrs = $attrServ->getAttrList(true, array(), true);
        $data = [
            'dpIdList' => $dpIds
        ];

        $list = array_combine(array_column($list, 'field_name'), $list);

        foreach ($attrs as $attr) {
            $fieldName = $attr['field_name'];
            // 防止前端未传参时,赋值为null,UC在保存自定义属性的值时,null会导致保存失败!
            $data[$fieldName] = $list[$fieldName]['attr_value'] !== null ? $list[$fieldName]['attr_value'] : '';

            // 图片、多选项,序列化存储在架构
            $serializeAttr = [
                AttrModel::ATTR_TYPE_CHECKBOX,
                AttrModel::ATTR_TYPE_PICTURE,
            ];
            if (in_array($list[$fieldName]['type'], $serializeAttr) && !empty($data[$fieldName])) {
                $data[$fieldName] = serialize($data[$fieldName]);
            }
        }

        // 调用验证接口,验证参数传值是否符合规范
        $errors = $attrServ->checkValue($data);
        if (!empty($errors)) {
            E($errors[0]);
        }

        // 编辑的时候不修改人员 加入方式
        if (empty($uid)) {
            // 邀请记录信息
            $data['memJoinType'] = self::ADMIN_ADD_JOIN;
            if (!empty($this->_login->user['eaRealname'])) {
                $data['memJoinInviter'] = $this->_login->user['eaRealname'];
            } elseif (!empty($this->_login->user['eaMobile'])) {
                $data['memJoinInviter'] = $this->_login->user['eaMobile'];
            } elseif (!empty($this->_login->user['eaEmail'])) {
                $data['memJoinInviter'] = $this->_login->user['eaEmail'];
            } else {
                $data['memJoinInviter'] = '';
            }
        }

        $userServ = new UserService();
        $result = $userServ->saveUser($uid, $data);

        // 新增、修改用户成功时,UC返回该用户的完整信息
        if (isset($result['memUid'])) {
            $uid = $result['memUid'];
        }

        $this->_result = [
            'uid' => $uid
        ];
    }
}
