<?php
namespace Adminko\Module;

use Adminko\Cart;
use Adminko\Date;
use Adminko\Mail;
use Adminko\View;
use Adminko\System;
use Adminko\Session;
use Adminko\Model\Model;
use Adminko\Module\Module;
use Adminko\Valid\Valid;

class PurchaseModule extends Module
{
    /**
     * Корзина
     */
    protected $cart = null;
     
    /**
     * Оформление заказа
     */
    protected function actionIndex()
    {
        if (Session::flash('purchase_complete')) {
            $this->content = $this->view->fetch('module/purchase/complete');
        } else {
            $this->cart = Cart::factory();
            
            $error = !empty($_POST) && $this->cart->getQuantity() ? $this->addPurchase() : array();
            
            $this->view->assign('error', $error);
            $this->view->assign('cart', $this->cart);
            $this->content = $this->view->fetch('module/purchase/form');
        }
    }
        
    /**
     * Создание заказа
     */
    protected function addPurchase()
    {
        $error = array();
        
        if (!isset($_POST['name']) || is_empty($_POST['name'])) {
            $error['name'] = 'Не заполнено обязательное поле';
        }
        if (!isset($_POST['phone']) || is_empty($_POST['phone'])) {
            $error['phone'] = 'Не заполнено обязательное поле';
        }
        if (!isset($_POST['email']) || is_empty($_POST['email'])) {
            $error['email'] = 'Не заполнено обязательное поле';
        }
        if (!isset($error['email']) && !Valid::factory('email')->check($_POST['email'])) {
            $error['email'] = 'Поле заполнено некорректно';
        }

        if (count($error)) {
            return $error;
        }
        
        // Сохранение заказа
        $purchase = Model::factory('purchase')
            ->setPurchaseClientName($_POST['name'])
            ->setPurchaseClientPhone($_POST['phone'])
            ->setPurchaseClientEmail($_POST['email'])
            ->setPurchaseClientAddress($_POST['address'])
            ->setPurchaseClientComment($_POST['comment'])
            ->setPurchaseDate(Date::now())
            ->setPurchaseSum($this->cart->getSum())
            ->save();
        
        // Сохранение позиций заказа
        foreach($this->cart->get() as $item) {
            $product = Model::factory('product')->get($item->id);

            Model::factory('purchase_item')
                ->setItemPurchase($purchase->getId())
                ->setItemProduct($product->getId())
                ->setItemPrice($item->price)
                ->setItemQuantity($item->quantity)
                ->save();
        }

        // Отправка сообщения
        $from_email = get_preference('from_email');
        $from_name = get_preference('from_name');
        
        $client_email = $_POST['email'];
        $client_subject = get_preference('client_subject');
        
        $manager_email = get_preference('manager_email');
        $manager_subject = get_preference('manager_subject');
        
        $purchase_view = new View();
        $purchase_view->assign('cart', $this->cart);
        $purchase_view->assign('purchase', $purchase);
        
        $client_message = $purchase_view->fetch('module/purchase/client_message');
        $manager_message = $purchase_view->fetch('module/purchase/manager_message');
        
        Mail::send($client_email, $from_email, $from_name, $client_subject, $client_message);
        Mail::send($manager_email, $from_email, $from_name, $manager_subject, $manager_message);
        
        Session::flash('purchase_complete', true);
        
        $this->cart->clear();
        
        System::redirectBack();
    }
    
    // Отключаем кеширование
    protected function getCacheKey()
    {
        return false;
    }
}