<?php
class Partners extends Controller {

    public function __construct() {
        parent::__construct();
    }
    public function getContent(){
        return true;
    }
    
    public function getTemplateName(){
        return 'partners_'.$this->lang;
    }

    public function getLayoutName() {
        return 'default';
    }

}