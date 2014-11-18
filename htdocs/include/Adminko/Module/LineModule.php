<?php
namespace Adminko\Module;

use Adminko\Model\Model;

class LineModule extends Module
{
    protected function actionIndex()
    {
        $line_list = Model::factory('line')->getList(array(), array('line_order' => 'asc'));

        $this->view->assign('line_list', $line_list);
        $this->content = $this->view->fetch('module/line/list');
    }
}
