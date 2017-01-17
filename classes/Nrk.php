<?php
class Nrk extends Controller {

    public function __construct() {
        parent::__construct();
    }
    public function getContent(){
        return true;
    }
    
    public function getTemplateName(){
        return 'nrk_'.$this->lang;
    }

    public function getLayoutName() {
        return 'default';
    }
}