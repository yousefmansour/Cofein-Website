<?php

class Gallery extends Controller {

    public function __construct() {
        parent::__construct();
    }
    
    public function getContent(){
        
        $albums = $this->getAlbums();
        $content = array();
        
        if(count($albums) > 0) {
            for($i = 0; $i < count($albums); $i++) {
                $images = $this->getImages($albums[$i]['id']);
                if(count($images) < 1){
                    continue;
                }
                $content['albums'][$i]['id'] = $albums[$i]['id'];
                $content['albums'][$i]['title'] = $albums[$i]['title'];
                $content['albums'][$i]['images'] = $images;
            }

            $tempArr = array();

            foreach($content['albums'] as $alb) {
                $tempArr[] = $alb;
            }

            $content['albums'] = $tempArr;
        }
        
        return $content;
    }
    
    public function getAlbums(){
        
        $query = 'SELECT id, title FROM gallery_albums ORDER BY id DESC';
        $albums = $this->database->prepare($query)->execute()->fetchAllAssoc();
              
        return $albums;
    }
    
    public function getImages($albumID){
        
        $query = 'SELECT id, path, album_cover FROM gallery WHERE album = :albumID';

        $images = $this->database->prepare($query)->
                execute(array(':albumID' => $albumID))->fetchAllAssoc();
        
        for($i = 0; $i < count($images); $i++){
            $filename = basename($images[$i]['path']);
            $thumbnail = $this->galleryThumbnailFolder.$filename;
            if(file_exists($thumbnail)){
                $images[$i]['thumbnail'] = $thumbnail;
            } else {
                $images[$i]['thumbnail'] = $images[$i]['path'];
            }         
            
            if($images[$i]['album_cover'] == 1 && $i > 0){
                $out = array_splice($images, $i, 1);
                array_splice($images, 0, 0, $out);
            }
        }
        
        return $images;
    } 
    
    public function getTemplateName(){
        return 'gallery';
    }

    public function getLayoutName() {
        return 'default';
    }
}
