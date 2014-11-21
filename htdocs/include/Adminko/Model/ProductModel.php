<?php
namespace Adminko\Model;

use Adminko\System;
use Adminko\Db\Db;

class ProductModel extends Model
{
    // Возвращает объект товара по артиклу
    public function getByName($product_article)
    {
        $record = Db::selectRow('select * from product where product_article = :product_article',
            array('product_article' => $product_article));
        if (!$record){
            throw new \AlarmException("Ошибка. Запись {$this->object}({$product_article}) не найдена.");
        }
        return $this->get($record['product_id'], $record);
    }
    
    // Возвращает каталог товара
    public function getCatalogue()
    {
        return Model::factory('catalogue')->get($this->getProductCatalogue());
    }

    // Возвращает URL товара
    public function getProductUrl($action = 'features')
    {
        return System::urlFor(array('controller' => 'product',
            'catalogue' => $this->getCatalogue()->getCatalogueName(),
            'product' => to_file_name($this->getProductArticle(), true),
            'action' => $action));
    }
    
    // Возвращает количество изображений товара
    public function getPictureCount()
    {
        return Model::factory('picture')->getCount(
            array('picture_product' => $this->getId())
        );
    }
    // Возвращает изображения товара
    public function getPictureList()
    {
        return Model::factory('picture')->getList(
            array('picture_product' => $this->getId()), array('picture_order' => 'asc')
        );
    }
        
    // Возвращает изображение по умолчанию
    public function getProductImage()
    {
        $picture_list = Model::factory('picture')->getList(
            array('picture_product' => $this->getId()), array('picture_order' => 'asc'), 1
        );
        if (empty($picture_list)) {
            return get_preference('default_image');
        }
        $default_image = current($picture_list);
        return $default_image->getPictureImage();
    }
    
    // Возвращает количество опций
    public function getOptionsCount()
    {
        return Db::selectCell('
                select
                    count(*)
                from
                    product
                    inner join product_link on product_link.link_product_id = product.product_id
                where
                    product_link.product_id = :product_id and product.product_active = :product_active',
            array('product_id' => $this->getId(), 'product_active' => 1)
        );
    }
    
    // Возвращает список опций
    public function getOptionsList()
    {
        $records = Db::selectAll('
                select
                    product.*
                from
                    product
                    inner join product_link on product_link.link_product_id = product.product_id
                where
                    product_link.product_id = :product_id and product.product_active = :product_active
                order by
                    product.product_order',
            array('product_id' => $this->getId(), 'product_active' => 1)
        );
        return $this->getBatch($records);
    }
    
    // Возвращает количество видео товара
    public function getVideoCount()
    {
        return Model::factory('video')->getCount(
            array('video_product' => $this->getId())
        );
    }
    // Возвращает видео товара
    public function getVideoList()
    {
        return Model::factory('video')->getList(
            array('video_product' => $this->getId()), array('video_order' => 'asc')
        );
    }

    // Возвращает количество упражнений товара
    public function getExercisesCount()
    {
        return Model::factory('exercise')->getCount(
            array('exercise_product' => $this->getId())
        );
    }
    // Возвращает упражнения товара
    public function getExercisesList()
    {
        return Model::factory('exercise')->getList(
            array('exercise_product' => $this->getId()), array('exercise_order' => 'asc')
        );
    }
    
    // Возвращает количество файлов товара
    public function getDownloadCount()
    {
        return Model::factory('download')->getCount(
            array('download_product' => $this->getId())
        );
    }
    // Возвращает файлы товара
    public function getDownloadList()
    {
        return Model::factory('download')->getList(
            array('download_product' => $this->getId()), array('download_order' => 'asc')
        );
    }
    
    // Поисковый запрос
    public function getSearchResult($search_value)
    {
        $search_words = preg_split('/\s+/', $search_value);
            
        $filter_clause = array();
        foreach (array('product_article', 'product_title', 'product_description') as $field_name) {
            $field_filter_clause = array();
            foreach ($search_words as $search_index => $search_word) {
                $field_prefix = $field_name . '_' . $search_index;
                $field_filter_clause[] = 'lower(' . $field_name . ') like :' . $field_prefix;
                $filter_binds[$field_prefix] = '%' . mb_strtolower($search_word , 'utf-8') . '%';
            }
            $filter_clause[] = join(' and ', $field_filter_clause);
        }
        
        $records = Db::selectAll('
            select product.* from product
                inner join catalogue on product.product_catalogue = catalogue.catalogue_id
            where (' . join(' or ', $filter_clause) . ') and
                product_active = :product_active and catalogue_active = :catalogue_active
            order by product_order asc',
            $filter_binds + array('product_active' => 1, 'catalogue_active' => 1)
        );
        
        return $this->getBatch($records);
    }
        
    // Возвращает свойства товара
    public function getPropertyList()
    {
        $product_property_list = Db::selectAll('
                select
                    property.*, ifnull(property_value.value_title, product_property.value) as property_value
                from
                    property
                    left join product_property on product_property.property_id = property.property_id
                    inner join product on product_property.product_id = product.product_id and
                        product.product_catalogue = property.property_catalogue
                    left join property_value on property_value.value_property = property.property_id and
                        property_value.value_id = product_property.value
                where
                    product_property.product_id = :product_id and property.property_active = :property_active
                order by
                    property.property_order',
            array('product_id' => $this->getId(), 'property_active' => 1)
        );
        
        $property_list = array();
        foreach ($product_property_list as $product_property) {
            $property = Model::factory('property')->get($product_property['property_id'], $product_property)
                ->setPropertyValue($product_property['property_value']);
            $property_list[$property->getId()] = $property;
        }
        return $property_list;
    }
}
