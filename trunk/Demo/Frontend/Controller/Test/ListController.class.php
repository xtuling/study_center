<?php
/**
 * ListController.class.php
 * $author$
 */

namespace Frontend\Controller\Test;

class ListController extends AbstractController
{

    public function Index()
    {

        $this->assign('title', L('DEFAULT_TITLE'));

        $this->_output("List");
    }
}
