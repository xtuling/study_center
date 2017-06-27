<?php
/**
 * MedalService.class.php
 * 勋章 service
 */
namespace Common\Service;

use Common\Common\Attach;
use Common\Model\MedalModel;

class MedalService extends AbstractService
{

    // 构造方法
    public function __construct()
    {
        parent::__construct();
        $this->_d = new MedalModel();
    }

    /**
     * 替换勋章数组内 用户上传 的图标URL
     * @param $medalArr
     */
    public function replaceAtUrlWhereUserUpload($medalArr)
    {
        // 获取用户上传的图片ID
        $atIds = [];
        foreach ($medalArr as $item) {
            if ($item['icon_type'] == MedalModel::ICON_TYPE_USER_UPLOAD) {
                $atIds[] = $item['icon'];
            }
        }
        if (!empty($atIds)) {
            $atIds = array_values(array_unique($atIds));

            $atServ = new Attach();
            $atArr = $atServ->listAttachUrl($atIds);

            // 替换图片路径
            foreach ($medalArr as &$item) {
                if ($item['icon_type'] == MedalModel::ICON_TYPE_USER_UPLOAD) {
                    $item['icon'] = $atArr[$item['icon']]['atAttachment'];
                }
            }
        }

        return $medalArr;
    }
}
