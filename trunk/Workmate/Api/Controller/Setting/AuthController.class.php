<?php
/**
 * AuthController.class.php
 * 获取同事圈是否开启发布审核以及评论审核
 * User: heyuelong
 * Date: 2017年4月25日16:28:05
 */
namespace Api\Controller\Setting;

class AuthController extends AbstractController
{

    /**
     * 主方法
     * @return boolean
     */
    public function Index_get()
    {
        // 获取审核同事圈功能，以及评论审核功能,当后台已修改设置时$this->_setting值不改变则查看后台设置是否更新缓存
        $this->_result = array(
            'release' => intval($this->_setting['release']),
            'comment' => intval($this->_setting['comment']),
        );
    }
}
