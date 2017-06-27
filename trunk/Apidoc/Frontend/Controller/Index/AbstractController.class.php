<?php
/**
 * Created by PhpStorm.
 *
 * User: zhoutao
 * Date: 16/7/14
 * Time: 下午2:56
 */

namespace Frontend\Controller\Index;

abstract class AbstractController extends \Common\Controller\Frontend\AbstractController
{

    /**
     * Apidoc 不需要任何数据库支持, 也不需要登录, 所以不执行基类操作
     * @param string $action
     * @return bool
     */
    public function before_action($action = '')
    {

        return true;
    }

    /**
     * output
     * @desc 输出模板
     *
     * @param string $tpl 引入的模板
     *
     * @return bool
     */
    protected function _output($tpl)
    {

        // 当前页标识
        $currentMenu = array();
        $currentMenu[CONTROLLER_NAME] = 'active ';

        $this->assign('identifier', APP_IDENTIFIER);
        $this->assign('enumber', QY_DOMAIN);
        $this->assign('host', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);

        $this->assign('currentMenu', $currentMenu);
        $this->assign('classUrl', oaUrl('/Frontend/Index/ListClass'));
        $this->assign('methodUrl', oaUrl('/Frontend/Index/ListMethod'));
        $this->assign('indexUrl', oaUrl('/Frontend/Index/Index'));
        $this->assign('docUrl', oaUrl('/Frontend/Index/Doc'));

        // 切换目录列表
        $this->assign('dirs', $this->_listDir());

        parent::_output($tpl);
        return true;
    }

    /**
     * 获取目录列表
     * @return array
     */
    protected function _listDir()
    {

        $file = I('request.file');
        if (empty($file)) {
            $file = I('request.dir');
        }

        $file = ltrim($file, '/');
        $dirs = array();
        $count = substr_count($file, '/');
        for (; $count > 0; $count--) {
            $pos = strrpos($file, '/');
            $file = substr($file, 0, $pos);
            $dirs[] = $file;
        }

        return $dirs;
    }

}
