<?php
/**
 * 编辑管理员信息
 * 鲜彤 2016年08月01日10:25:42
 */
namespace Apicp\Controller\AdminManager;

use Com\Validate;
use Common\Common\Sms;
use VcySDK\Adminer;

class EditController extends AbstractController
{
    /**
     * 用户可能的状态值
     *
     * @type array 管理员状态, 1: 启用; 2: 禁用
     */
    private $userStatuses = array(1, 2);

    /** 提交的数据 */
    protected $postData = [];

    public function Index()
    {
        $this->getParams();

        $eaData = $this->_sdkAdminer->fetch(['eaId' => $this->postData['eaId']]);

        // 调用UC，编辑管理员提交
        $this->_sdkAdminer->modify($this->postData);

        // 邮箱有改变 并且 未激活
        if ($eaData['eaIsactivated'] != Adminer::IS_ACTIVATED) {
            $this->inviteMsgSend($this->postData['eaId']);
        }

        return true;
    }

    /**
     * 获取提交数据
     * @return bool
     */
    protected function getParams()
    {
        $field = [
            'eaId',
            'eaMobile',
            'eaRealname',
            'eaPassword',
            'eaUserstatus',
            'eaEmail',
            'earId',
            'memUid',
            'adminerBusinessAuthor'
        ];
        foreach ($field as $_name) {
            $this->postData[$_name] = I('post.' . $_name);
        }

        if (empty($this->postData['eaPassword'])) {
            unset($this->postData['eaPassword']);
        }
        $this->validateParams();

        return true;
    }

    /**
     * 验证数据
     * @return bool
     */
    protected function validateParams()
    {
        $role = [
            'eaId' => 'require',
            'eaMobile' => ['require', 'regex' => '/(^0?1[2,3,5,6,7,8,9]\d{9}$)|(^(\d{3,4})-(\d{7,8})$)|(^(\d{7,8})$)|(^(\d{3,4})-(\d{7,8})-(\d{1,4})$)|(^(\d{7,8})-(\d{1,4})$)/'],
            'eaRealname' => 'length:2,20',
            'eaEmail' => 'email',
            'eaUserstatus' => 'in:' . implode(',', $this->userStatuses),
        ];
        $errormsg = [
            'eaId' => L('_ERR_PLS_SUBMIT_ID', ['name' => 'ID']),
            'eaMobile' => '_ERR_PHONE_FORMAT',
            'eaRealname' => '_ERR_REAL_NAME_FORMAT',
            'eaUserstatus' => '_ERR_USER_STATUS_INVALID',
            'eaEmail.require' => L('_ERR_PLS_SUBMIT_ID', ['name' => '邮箱']),
            'eaEmail.email' => L('_ERR_DATA_FORMAT', ['name' => '邮箱']),
        ];

        $validateData = $this->postData;
        if (!empty($validateData['memUid'])) {
            if (isset($validateData['adminerBusinessAuthor']['authorType'])) {
                $validateData['authorType'] = $validateData['adminerBusinessAuthor']['authorType'];
            }
            if (isset($validateData['adminerBusinessAuthor']['dpIds'])) {
                $validateData['dpIds'] = $validateData['adminerBusinessAuthor']['dpIds'];
            }

            $role['authorType'] = 'requireWithNone:memUid|in:1,2';
            $role['dpIds'] = 'requeireIf:authorType,2';

            $errormsg['authorType'] = L('_ERR_FIELD_REQUIRE_WITH_FIELF', ['name' => '通讯录人员', 'condition' => '不为空', 'requireName' => '业务权限类型']);
            $errormsg['dpIds'] = L('_ERR_FIELD_REQUIRE_WITH_FIELF', ['name' => '业务权限类型', 'condition' => '为指定部门', 'requireName' => '指定部门ID']);
        }

        $validator = new Validate($role, $errormsg);
        if (!$validator->check($validateData)) {
            E($validator->getError());
        }

        return true;
    }

}

