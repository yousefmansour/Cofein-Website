<?php
class Controller {
    
    protected $database;
    protected $lang = 'bg';
    protected static $messages = array();
    protected $shopImageFolder = 'views/img/shop/';
    protected $newsImageFolder = 'views/img/news/';
    protected $newsTempFolder = 'views/img/news_temp/';
    protected $galleryImageFolder = 'views/img/gallery/'; 
    protected $galleryThumbnailFolder = 'views/img/gallery_thumbnails/';
    protected $galleryTempFolder = 'views/img/gallery_temp/';  
    
    public function __construct() {
        $this->setDB();
    }

    public function setDB() {
        $DB = Database::getInstance();
        if (!$DB) {
            throw new Exception('Failed to get database instance.');
        }
        $this->database = $DB;
        return $this;
    }
    
    public static function getMessages(){
        return self::$messages;
    }
}   
