<?php
/**
 * OpenController.class.php
 * 应用开启
 * $author$
 */

namespace Apicp\Controller\Plugin;

use Com\Plugin;

class OpenController extends AbstractController
{

    public function Index()
    {

        $plugin = new Plugin();
        $data = [];
        // 导入应用的默认数据
        $plugin->importDefaultData($data);

        $this->_result = array(
            'data' => $data
        );

        return true;
    }

}
