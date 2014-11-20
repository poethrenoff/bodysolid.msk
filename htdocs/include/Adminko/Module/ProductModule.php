<?php
namespace Adminko\Module;

use Adminko\System;
use Adminko\Model\Model;

class ProductModule extends Module
{
    // Текущее произведение
    private static $catalogue = null;
    
    // Текущая линейка
    private static $line = null;
    
    // Текущий товар
    private static $product = null;
    
    // Вывод списка товаров
    protected function actionIndex()
    {
        $catalogue_name = System::getParam('catalogue');
        $catalogue = $this->getCatalogue($catalogue_name);

        $this->view->assign($catalogue);
        $this->output['category'] = true;
        
        if ($catalogue->getCatalogueParent()) {
            $this->content = $this->view->fetch('module/product/list');
        } else {
            $this->content = $this->view->fetch('module/product/catalogue');
        }
    }

    // Вывод списка товаров по линейке
    protected function actionLine()
    {
        $line_name = System::getParam('line');
        $line = $this->getLine($line_name);
        
        $this->view->assign($line);
        $this->output['category'] = true;
        
        $this->content = $this->view->fetch('module/product/list');
    }

    private function actionItem($action)
    {
        $product_article = System::getParam('product');
        $product = $this->getProduct($product_article);
        
        $this->view->assign($product);
        $this->output['product'] = true;
        $this->content = $this->view->fetch('module/product/' . $action);
    }

    protected function actionFeatures()
    {
        $this->actionItem('features');
    }

    protected function actionProperty()
    {
        $this->actionItem('property');
    }

    protected function actionGallery()
    {
        $this->actionItem('gallery');
    }
    
    protected function actionOptions()
    {
        $this->actionItem('options');
    }
    
    protected function actionExercises()
    {
        $this->actionItem('exercises');
    }
    
    protected function actionDownload()
    {
        $this->actionItem('download');
    }

    protected function actionMenu()
    {
        $catalogue_tree = Model::factory('catalogue')->getTree(
            Model::factory('catalogue')->getList(
                array('catalogue_active' => 1), array('catalogue_order' => 'asc')
            )
        );

        $this->view->assign($catalogue_tree);
        $this->content = $this->view->fetch('module/product/menu');
    }
    
    protected function actionPath()
    {
        $path = array();
        if (System::action() == 'index') {
            $catalogue_name = System::getParam('catalogue');
            $path[] = $catalogue = $this->getCatalogue($catalogue_name);
            if ($catalogue->getCatalogueParent()) {
                $path[] = Model::factory('catalogue')->get($catalogue->getCatalogueParent());
            }
        } elseif (System::action() == 'line') {
            $line_name = System::getParam('line');
            $path[] = $this->getLine($line_name);
        } else {
            $product_article = System::getParam('product');
            $path[] = $product = $this->getProduct($product_article);
            $path[] = $catalogue = Model::factory('catalogue')->get($product->getProductCatalogue());
            if ($catalogue->getCatalogueParent()) {
                $path[] = Model::factory('catalogue')->get($catalogue->getCatalogueParent());
            }
        }
        
        $this->view->assign('path', array_reverse($path));
        $this->content = $this->view->fetch('module/product/path');
    }

    /**
     * Получение товара
     */
    public function getProduct($product_article)
    {
        if (is_null(self::$product)) {
            try {
                self::$product = Model::factory('product')->getByName($product_article);
            } catch (\AlarmException $e) {
                System::notFound();
            }
            if (!self::$product->getProductActive()) {
                System::notFound();
            }
        }
        return self::$product;
    }
    
    /**
     * Получение каталога
     */
    public function getCatalogue($catalogue_name)
    {
        if (is_null(self::$catalogue)) {
            try {
                self::$catalogue = model::factory('catalogue')->getByName($catalogue_name);
            } catch (\AlarmException $e) {
                System::notFound();
            }
            if (!self::$catalogue->getCatalogueActive()) {
                System::notFound();
            }
        }
        return self::$catalogue;
    }
    
    /**
     * Получение линейки
     */
    public function getLine($line_name)
    {
        if (is_null(self::$line)) {
            try {
                self::$line = model::factory('line')->getByName($line_name);
            } catch (\AlarmException $e) {
                System::notFound();
            }
        }
        return self::$line;
    }

}
