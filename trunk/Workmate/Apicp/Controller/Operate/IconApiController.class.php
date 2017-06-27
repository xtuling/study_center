<?php
/**
 * Created by PhpStorm.
 * User: liyifei2012it
 * Date: 17/4/11
 * Time: 16:31
 */
namespace Apicp\Controller\Operate;

class IconApiController extends \Apicp\Controller\AbstractController
{
    /**
     * 栏目接口
     * @desc 栏目接口
     * @return array(
     *  array(
     *      'id' => '分类 ID', // 分类 ID
     *      'name' => '分类名称', // 分类名称
     *      'url' => '分类链接，如果为空，则表明该链接不可直接访问', // 分类链接，如果为空，则表明该链接不可直接访问
     *      'upId' => '上级分类 ID，为 0 则表示顶级' // 上级分类 ID，为 0 则表示顶级
     *  ),
     *  array()
     * )
     */
    public function Index_post()
    {
        $res[] = [
            'id' => '1',
            'name' => '全部',
            'url' => 'Workmate/Frontend/Index/Index',
            'upId' => '0',
        ];

        $this->_result = $res;
    }
}
