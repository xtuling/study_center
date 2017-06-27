<?php

namespace Api\Controller\User;

use Common\Common\User;
use Common\Model\AttrModel;
use Common\Service\AttrService;
use Common\Service\UserService;

class EditController extends AbstractController
{
    public function Index_post()
    {
        // 是否有此人
        $uid = I('post.uid', '', 'trim');
        $userSDK = new User();
        $userData = $userSDK->getByUid($uid);
        if (empty($userData)) {
            E('_ERR_MEMBER_IS_NOT_EXIST');
        }

        // 获取人员属性更新数据
        $infoList = array_combine_by_key(I('post.list'), 'field_name');
        $userUpdateData = $this->getUpdateData($infoList);

        // 保存
        $userServ = new UserService();
        $userServ->saveUser($uid, $userUpdateData);

        return true;
    }

    /**
     * 获取人员属性更新数据
     * @param $infoList
     * @return array
     */
    protected function getUpdateData($infoList)
    {
        // 获取 人员属性设置
        $attrServ = new AttrService();
        $attrArr = $attrServ->getAttrList(true, array(), true);

        // 更新数据
        $userUpdateData = [];
        // 需要特殊数据结构处理的字段
        $serializeAttr = [
            AttrModel::ATTR_TYPE_CHECKBOX,
            AttrModel::ATTR_TYPE_PICTURE,
        ];
        foreach ($attrArr as $attr) {
            $fieldName = $attr['field_name'];
            $attrValue = $infoList[$fieldName]['attr_value'];
            // 值不存在 或者 字段手机端不允许编辑 则 跳过
            if ($attrValue == null ||
                    $attr['is_allow_user_modify'] == AttrModel::ATTR_NOT_ALLOWED_USER_MODIFY) {
                continue;
            }


            if (!in_array($attr['type'], $serializeAttr)) {
                $userUpdateData[$fieldName] = $attrValue;
            } else {
                // 图片、多选项 特殊数据结构处理
                $userUpdateData[$fieldName] = serialize(// 这里的二维空数组 是因为前端在初始化有option字段的时候需要
                    !empty($attrValue) ? $attrValue : [[], []]);
            }
        }

        // 调用验证接口,验证参数传值是否符合规范
        $errors = $attrServ->checkValue($userUpdateData, 'is_required_cp', array_keys($userUpdateData));
        if (!empty($errors)) {
            E($errors[0]);
        }

        return $userUpdateData;
    }
}
