<?php
/**
 * IndexController.class.php
 * $author$
 */
namespace Frontend\Controller\Frontend;

class IndexController extends \Common\Controller\Frontend\AbstractController
{

    public function Index()
    {
        $this->assign('title', L('DEFAULT_TITLE'));
        $this->_output("Common@Frontend/Default");
    }
}
