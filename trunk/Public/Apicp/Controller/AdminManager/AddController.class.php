<?php
/**
 * 新增管理员
 * 鲜彤 2016-07-29 15:42:10
 */
namespace Apicp\Controller\AdminManager;

use Com\PackageValidate;
use VcySDK\Adminer;

class AddController extends AbstractController
{

    /**
     * 用户可能的状态值
     *
     * @type array 管理员状态, 1: 启用; 2: 禁用
     */
    private $userStatuses = array(Adminer::MANAGER_ABLE_LOGIN, Adminer::MANAGER_DISABLE_LOGIN);

    /** 提交的数据 */
    private $postData = [];

    public function Index()
    {
        $this->getParams();

        // 提交UC
        $res = $this->_sdkAdminer->register($this->postData);
        if (empty($res['eaId'])) {
            E("_ERR_ADD_ADMIN");
            return false;
        }

        // 发送邀请 邮件和短信
        $this->inviteMsgSend($res['eaId']);

        // 菜单权限清空缓存文件
        $options['temp'] = get_sitedir();
        S('authAction_' . $res['eaId'], null, $options);

        $this->_result = $res;

        return true;
    }

    /**
     * 获取和验证数据
     */
    protected function getParams()
    {
        // 初始化验证器
        $validate = new PackageValidate([
            'eaMobile' => ['require', 'regex' => PackageValidate::PHONE_REGEX],
            'eaRealname' => 'require|length:2,20',
            'eaEmail' => 'require|email',
            'earId' => 'require',
            'eaUserstatus' => 'in:' . implode(',', $this->userStatuses),
        ], [
            'eaMobile' => '_ERR_PHONE_FORMAT',
            'eaRealname' => '_ERR_REAL_NAME_FORMAT',
            'eaUserstatus' => '_ERR_USER_STATUS_INVALID',
            'eaEmail.require' => L('_ERR_PLS_SUBMIT_ID', ['name' => '邮箱']),
            'eaEmail.email' => L('_ERR_DATA_FORMAT', ['name' => '邮箱']),
            'earId' => L('_ERR_PLS_SUBMIT_ID', ['name' => 'ID']),
        ]);

        // 获取提交数据
        $validate->getParams([
            'eaMobile',
            'eaRealname',
            'eaUserstatus',
            'eaEmail',
            'earId',
            'memUid',
            'adminerBusinessAuthor'
        ]);

        // 当memUid不为空时: authorType必填, 当authorType为2时: dpIds必填
        $validateData = $validate->postData;
        if (!empty($validateData['memUid'])) {
            $validateData['authorType'] = $validateData['adminerBusinessAuthor']['authorType'];
            $validateData['dpIds'] = $validateData['adminerBusinessAuthor']['dpIds'];

            $validate->rule['authorType'] = 'requireWithNone:memUid|in:1,2';
            $validate->rule['dpIds'] = 'requeireIf:authorType,2';

            $validate->message['authorType'] = L('_ERR_FIELD_REQUIRE_WITH_FIELF', ['name' => '通讯录人员', 'condition' => '不为空', 'requireName' => '业务权限类型']);
            $validate->message['dpIds'] = L('_ERR_FIELD_REQUIRE_WITH_FIELF', ['name' => '业务权限类型', 'condition' => '为指定部门', 'requireName' => '指定部门ID']);
        }

        // 验证 并获取提交数据
        if (!$validate->check($validateData)) {
            E($validate->getError());
        }
        $this->postData = $validate->postData;
    }
}

