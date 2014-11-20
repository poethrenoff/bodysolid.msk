<?php
namespace Adminko\Module;

use Adminko\Captcha;
use Adminko\Mail;
use Adminko\View;
use Adminko\Session;
use Adminko\System;
use Adminko\Valid\Valid;

class DealerModule extends Module
{
    public $type_list = array(
        '1' => 'Оптовая', '2' => 'Розничная',
    );

    protected function actionIndex()
    {
        if (Session::flash('subscribe_complete')) {
            $this->content = $this->view->fetch('module/dealer/success');
        } else {
            $error = !empty($_POST) ? $this->subscribe() : array();

            $this->view->assign('error', $error);
            $this->view->assign('type_list', $this->type_list);
            $this->content = $this->view->fetch('module/dealer/form');
        }
    }
    
    protected function subscribe()
    {   
        $error = array();

        if (!isset($_POST['email']) || is_empty($_POST['email'])) {
            $error['email'] = 'Не заполнено обязательное поле';
        }
        if (!isset($error['email']) && !Valid::factory('email')->check($_POST['email'])) {
            $error['email'] = 'Поле заполнено некорректно';
        }
        if (!isset($_POST['person']) || is_empty($_POST['person'])) {
            $error['person'] = 'Не заполнено обязательное поле';
        }

        if (isset($_POST['name']) && !is_empty($_POST['name'])) {
            if (!isset($_POST['type']) || is_empty($_POST['type'])) {
                $error['type'] = 'Не заполнено обязательное поле';
            }
            if (!isset($error['type']) && !in_array($_POST['type'], array_keys($this->type_list))) {
                $error['type'] = 'Поле заполнено некорректно';
            }
            if (!isset($_POST['phone']) || is_empty($_POST['phone'])) {
                $error['phone'] = 'Не заполнено обязательное поле';
            }
            if (!isset($_POST['fax']) || is_empty($_POST['fax'])) {
                $error['fax'] = 'Не заполнено обязательное поле';
            }
        }
        if (!isset($_POST['captcha']) || is_empty($_POST['captcha'])) {
            $error['captcha'] = 'Не заполнено обязательное поле';
        }
        if (!isset($error['captcha']) && !Captcha::check($_POST['captcha'])) {
            $error['captcha'] = 'Неправильно введены символы с картинки';
        }
        
        if (count($error)) {
            return $error;
        }

        $from_email = get_preference('from_email');
        $from_name = get_preference('from_name');

        $subscribe_email = get_preference('subscribe_email');
        $subscribe_subject = get_preference('subscribe_subject');

        $subscribe_view = new View();
        $subscribe_view->assign('type_list', $this->type_list);
        $subscribe_message = $subscribe_view->fetch('module/dealer/message');
        
        Mail::send($subscribe_email, $from_email, $from_name, $subscribe_subject, $subscribe_message);

        Session::flash('subscribe_complete', true);

        System::redirectBack();
    }
}
