<?php
namespace Adminko\Admin\Table;

class LineTable extends Table
{
    protected function actionAddSave($redirect = true)
    {
        if (!init_string('line_name')) {
            $_REQUEST['line_name'] = to_file_name(init_string('line_title'), true);
            unset($this->fields['line_name']['no_add']);
        }

        $primary_field = parent::actionAddSave(false);

        if ($redirect) {
            $this->redirect();
        }

        return $primary_field;
    }
}
