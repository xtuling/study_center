<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 16/9/18
 * Time: 11:52
 */
namespace Api\Controller\User;

use Common\Service\CardService;
use Common\Service\AttrService;
use Common\Service\InviteUserService;

class CardSaveController extends AbstractController
{

    /**
     * 【通讯录】名片设置
     * @author liyifei
     * @time 2016-09-18 11:54:49
     */
    public function Index_post()
    {
        $fields = I('post.fields', []);
        if (!empty($fields) && !is_array($fields)) {
            E('_ERR_PARAM_FORMAT');
            return false;
        }

        // 当前登录用户
        $user = $this->_login->user;
        if (empty($user)) {
            E('_ERR_NOT_LOGIN');
            return false;
        }

        // 验证参数中的属性名是否存在
        $attrServ = new AttrService();
        $data = $attrServ->list_all();
        $allFields = array_column($data, 'field_name');
        foreach ($fields as $fieldName) {
            if (!in_array($fieldName, $allFields)) {
                E('_ERR_FIELD_NAME_UNDEFINED');
                return false;
            }
        }

        // 是否已存在隐藏的数据
        $cardServ = new CardService();
        $conds = [
            'uid' => $user['memUid']
        ];
        $res = $cardServ->get_by_conds($conds);

        // 修改或新增的数据
        $data = [
            'fields' => !empty($fields) && is_array($fields) ? serialize($fields) : '',
        ];
        if (empty($res)) {
            $data['uid'] = $user['memUid'];
            $cardServ->insert($data);
        } else {
            $cardServ->update_by_conds($conds, $data);
        }
    }
}
