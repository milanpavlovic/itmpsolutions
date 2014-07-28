<?php

class Init {

    public $db;

    public $baseUrl;

    public $table;

    public $files = array();

    public $tables = array();

    public $maxSize;

    public $allowedTypes = array();

    public $logged;

    public function __construct(PDO $db, $table, $config){
        $this->logged       = $_SESSION['logged'];
        $this->baseUrl      = $config['main']['baseUrl'];
        if(!$this->logged){
            $this->redirect('login', null, true);
        }
        $this->table        = $table;
        $this->db           = $db;
        $this->files        = $config['tables'][$table];
        $this->tables       = array_keys($config['tables']);
        $this->maxSize      = $config['upload']['maxSize'] * 1024 * 1024;
        $this->allowedTypes = $config['upload']['allowedType'];
    }

    public function load($array = null, $view = null, $partial = false){
        if($array) extract($array);
        if(! $view){
            $view = debug_backtrace()[1]['function'];
        }
        if($partial){
            ob_start();
            include $view . '.php';
            $content = ob_get_contents();
            ob_end_clean();
            return $content;
        } else {
            include('index.php');
        }
    }

    public function redirect($action, $id = null, $root = false){
        header('Location:' . $this->router($action, $id, $root));
        exit();
    }

    public function setFlash($msg){
        $_SESSION['flash'] = $msg;
    }

    public function router($name, $id = '', $root = false){
        return $root ? $this->baseUrl . '/?' . $name : $this->baseUrl . '/?' . $this->table . '/' . $name . '/' . $id;
    }

    public function notFound($action){
        $this->pageName = '404';
        $this->load(array('message' => $action . ' not found'), 'error');
        exit();
    }

}