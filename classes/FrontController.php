<?php
class FrontController {
    
    private $content;
    private $messages;
    private $request = 'News';
    private $controller;
    private $template;
    private $ajaxRequest = false;
    public static $lang = 'bg';
    private $layout = 'main_layout.html.twig';
    private $mobileLayout = 'mobile_layout.html.twig';
    private $ieError = 'ie_error.html.twig';
    private $layoutFolder = 'views/layouts/';
    private $templateFolder = 'views/templates/';
    
    public function __construct() {
        set_exception_handler(array($this, 'exceptionHandler'));
        session_start();  
        $this->processRequest()->createPage()->render();
    }

    private function processRequest(){   
        
        if (isset($_GET['admin']) || isset($_GET['adminpage'])) {
            $this->request = 'Admin';
        } else if (isset($_GET['page'])) {
            $this->ajaxRequest = true;
            $this->request = $_GET['page'];
        } else {
            $request = $this->getRequestFromUri();
            if($request) {
                $this->request = $request;
            }       
        }

        if(isset($_GET['delete'])){
            $admin = new Admin();
            $admin->delete();
        }
        return $this;
    }
    
    private function createPage(){
         if (class_exists($this->request, true) && method_exists($this->request, 'getContent')){      
            $this->controller = new $this->request();
            $this->setContent()->setLayout()->setTemplateFile();    
        } else {
            throw new Exception($this->request);
        }
        return $this;
    }
    
    private function setTemplateFile(){
        $templateName = $this->controller->getTemplateName(); 
        if(!$templateName) {
            $template = 'empty.html.twig';
        } else {
            $template = $templateName.'.html.twig';
        }
        $this->template = $template;
        return $this;
    }
    
    private function setLayout() {
        if (preg_match('/(?i)msie [5-8]/', $_SERVER['HTTP_USER_AGENT'])) {
            // if IE<=8
            echo file_get_contents($this->layoutFolder . $this->ieError);
            exit;
        } else {
            if ($this->ajaxRequest === true) {
                $this->layout = 'ajax_layout.html.twig';
            } else {
                $layout = $this->controller->getLayoutName();
                if ($layout !== 'default') {
                    $this->layout = $layout . '_layout.html.twig';
                } elseif($this->isMobile()){
                    $this->layout = $this->mobileLayout;
                }
            }
            return $this;
        }
    }

    private function setContent() {
        $content = $this->controller->getContent();  
        if($content){
            $this->content = $content;
        }
        $this->messages = Controller::getMessages(); 
        return $this;
    }

    private function render(){
        $loader = new Twig_Loader_Filesystem(array(PATH_TO_SRC.$this->layoutFolder, PATH_TO_SRC.$this->templateFolder));
        $twig = new Twig_Environment($loader, array('debug' => DEBUG));

        $twig->addExtension(new Twig_Extension_Debug());
        
        echo $twig->render($this->template, 
                array('layout' => $this->layout,
                    'path' => PATH_TO_SRC,
                    'lang' => self::$lang, 
                    'template' => $this->template, 
                    'content' => $this->content, 
                    'messages' => $this->messages,
                    'session' => $_SESSION,
                    'get' => $_GET,
                    'post' => $_POST));
    }
    
    private function getRequestFromUri(){
        return explode('?',str_replace('/','',$_SERVER['REQUEST_URI']),2)[0];
    }
    
    public function exceptionHandler(Exception $exception) {
        if(DEBUG){
            file_put_contents('C:\Users\UltraPC3000\Desktop\exceptions.txt', $exception);
            echo '<pre>' . print_r($exception, true) . '</pre>';
        } else {
            echo 'An error has occured.';
        }
    }
      
    private function isMobile(){
        
         return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
    } 
}
