<?php
namespace Adminko\Model;

use Adminko\System;
use Adminko\Db\Db;

class LineModel extends Model
{
    // Возвращает объект линейки по системному имени
    public function getByName($line_name)
    {
        $record = Db::selectRow('select * from line where line_name = :line_name',
            array('line_name' => $line_name));
        if (!$record){
            throw new \AlarmException("Ошибка. Запись {$this->object}({$line_name}) не найдена.");
        }
        return $this->get($record['line_id'], $record);
    }
    
    // Возвращает список товаров линейки
    public function getProductList($limit = null)
    {
        return Model::factory('product')->getList(
            array('product_active' => 1, 'product_line' => $this->getId()),
            array('product_order' => 'asc'), $limit
        );
    }
    
    // Возвращает URL линейки
    public function getLineUrl()
    {
        return System::urlFor(array('controller' => 'product', 'action' => 'line', 'line' => $this->getLineName()));
    }
}
