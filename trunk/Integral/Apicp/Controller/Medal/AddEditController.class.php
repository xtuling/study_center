<?php
/**
 * Created by IntelliJ IDEA.
 * 添加/编辑勋章
 * User: zhoutao
 * Reader: zhoutao 2017-05-31 10:02:11
 * Date: 2017-05-24 12:10:06
 */

namespace Apicp\Controller\Medal;

use Com\PackageValidate;
use Common\Model\MedalModel;
use Common\Service\MedalService;

class AddEditController extends AbstractController
{
    public function index()
    {
        $validate = new PackageValidate(
            [
                'icon' => 'require',
                'icon_type' => 'in:' . MedalModel::ICON_TYPE_USER_UPLOAD . ',' . MedalModel::ICON_TYPE_SYS,
                'name' => 'require|max:' . MedalModel::NAME_MAX_LENGTH,
                'desc' => 'max:' . MedalModel::DESC_MAX_LENGTH,
            ],
            [
                'icon.require' => L('_ERR_PARAM_CAN_NOT_BE_EMPTY', ['name' => '勋章图标']),
                'icon_type.in' => L('_ERR_PARAM_MUST_IN_RANGE', [
                    'name' => '勋章图标来源类型',
                    'range' => MedalModel::ICON_TYPE_USER_UPLOAD . ',' . MedalModel::ICON_TYPE_SYS
                ]),
                'name.require' => L('_ERR_PARAM_CAN_NOT_BE_EMPTY', ['name' => '名称']),
                'name.max' => L('_ERR_PARAM_MAX_LENGTH', [
                    'name' => '名称',
                    'maxLength' => MedalModel::NAME_MAX_LENGTH]),
                'desc.max' => L('_ERR_PARAM_MAX_LENGTH', [
                    'name' => '描述',
                    'maxLength' => MedalModel::DESC_MAX_LENGTH]),
            ],
            [
                'im_id',
                'icon',
                'icon_type',
                'name',
                'desc'
            ]
        );
        $postData = $validate->postData;
        // 验证器不接受空字段数据, 这里重新获取下
        $postData['desc'] = I('post.desc');

        try {
            $medalServ = new MedalService();
            $medalInsertData = array_diff_key_reserved($postData, ['im_id'], true);
            // 更新
            if (!empty($postData['im_id'])) {
                $medalServ->update($postData['im_id'], $medalInsertData);
                $this->_result['im_id'] = $postData['im_id'];
            // 新增
            } else {
                $this->_result['im_id'] = $medalServ->insert($medalInsertData);
            }

            $medalServ->commit();
        } catch (\Exception $e) {
            $medalServ->rollback();
        }

        return true;
    }
}
