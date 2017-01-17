<?php
class Bio extends Controller {

    public function __construct() {
        parent::__construct();
    }
    public function getContent(){
        return true;
    }
    
    public function getTemplateName(){
        return 'bio_'.$this->lang;
    }

    public function getLayoutName() {
        return 'default';
    }
}
