<?php
/**
 * SettingModel.class.php
 * 公共设置 Service
 * @author Deepseath
 * @version $Id$
 */
namespace Common\Service;

use Common\Model\CommonSettingModel;

class CommonSettingService extends AbstractService
{

    /**
     * 构造方法
     */
    public function __construct()
    {
        parent::__construct();
        $this->_d = new CommonSettingModel();
    }

    /**
     * @desc 安装默认设置数据
     * @author tangxingguo
     */
    public function setDefaultData()
    {
        $count = $this->count();
        if ($count > 0) {
            return;
        }
        // 默认配置信息
        $defaultData = \Common\Sql\DefaultData::installData();
        $defaultData = serialize($defaultData);

        $data = [
            'key' => 'appConfig',
            'value' => $defaultData,
            'type' => 1,
            'comment' => '应用配置相关信息',
        ];

        $this->insert($data);
    }
}
