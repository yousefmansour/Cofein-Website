<?php

class AdminGallery extends Controller {
   
    private $maxImageWidth = 1000;
    private $maxImageHeight = 1000;  
    
    //Saves uploaded images
    public function save(){
        set_time_limit(0);
        
        if(isset($_POST['album_id'])){
            $albumID = (int)$_POST['album_id'];
        } else {
            return false;
        }
        
        $images = $this->getImagePaths($this->galleryTempFolder);

        $dstDir = $this->galleryImageFolder.$albumID.'/';
        
        foreach($images as $img){
            $this->createThumbnail($img, $this->galleryThumbnailFolder);
            $this->resizeImage($img, $this->maxImageWidth, $this->maxImageHeight, 75);
            $newPath = $this->moveFile($img, $dstDir);
            
            if(count($this->getImagesFromAlbum($albumID)) > 0 ){
                $this->saveImageToDB($albumID, $newPath, 0);
            } else {
                $this->saveImageToDB($albumID, $newPath, 1);
            }
        }
        
        self::$messages[] = 'Images have been saved.';
    }
      
    private function createThumbnail($img, $dir){
        $imageName = basename($img);
        $thumbnail = $dir.$imageName;
        
        copy($img, $thumbnail);
        if(file_exists($thumbnail)){
            if($this->resizeImage($thumbnail, 400, 400, 75)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function moveFile($file, $dstDir){
        $filename = basename($file);
        
        $newPath = $dstDir.$filename;
        rename($file, $newPath);
        
        return $newPath;
    }
    
    private function saveImageToDB($albumID, $path, $cover){
       $query = 'INSERT INTO gallery (`album`,`path`,`album_cover`)
           VALUES (:id, :path, :cover)';
       
       $this->database->prepare($query)->execute(
               array(':id' => $albumID, 
                   ':path' => $path,
                   ':cover' => $cover)
                );
      
       return $this;
    }
    
    private function resizeImage($file, $maxWidth, $maxHeight, $quality){
        
        list($width, $height) = getimagesize($file);
        if($width < $maxWidth && $height < $maxHeight){
            return $file;
        } else {
            $wr = $maxWidth/$width;
            $hr = $maxHeight/$height;

            $ratio = $wr < $hr ? $wr : $hr;
            
            $newWidth = $width * $ratio;
            $newHeight = $height * $ratio;
            
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $fileType = finfo_file($finfo, $file);
            finfo_close($finfo);
            
            switch($fileType) {
                case 'image/jpeg':
                    $src = imagecreatefromjpeg($file);
                    $dst = $this->createResizedImage($src, $width, $height, $newWidth, $newHeight);
                    imagejpeg($dst, $file, $quality);
                    break;
                case 'image/png':
                    $src = imagecreatefrompng($file);
                    $dst = $this->createResizedImage($src, $width, $height, $newWidth, $newHeight);
                    imagepng($dst, $file, ceil(1));
                    break;
                default: 
                    return false;
            }
        }
        
        return true;
    }
    
    private function createResizedImage($src, $width, $height, $newWidth, $newHeight){
        $dst = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        
        return $dst;
    }
    
    private function getImagePaths($dir){     
        $files = scandir($dir);
        $result = array();
        
        foreach($files as $f){
            if(!is_dir($dir.$f)) {
                $result[] = $dir.$f;
            }
        }

        return $result;
    }
    
    //Moves uploaded image to temp folder
    public function upload(){
        if($_FILES['qqfile']['error'] !== 0){
            if($_FILES['qqfile']['error'] === 1){
                echo json_encode(array('error' => 'Upload error. File is too large.'));
            } else {
                echo json_encode(array('error' => 'Unknown upload error.'));
            }
        } else {
            if($this->mimeTypeToExtension($_FILES['qqfile']['type']) !== false){
            $this->moveImageToTemp();
            echo json_encode(array('success' => true));
            } else {
                echo json_encode(array('error' => 'Upload error. The files must be .jpg or .png.'));
            } 
        }
    }
    
    private function moveImageToTemp(){
        $file = $_FILES['qqfile']['tmp_name'];
        $extension = $this->mimeTypeToExtension($_FILES['qqfile']['type']);
        $qquuid = $_REQUEST['qquuid'];
        $newPath = $this->galleryTempFolder.$qquuid.$extension;
        rename($file, $newPath);
    }
    
    private function mimeTypeToExtension($type){
        switch($type){
            case 'image/jpeg':
                return '.jpg';
            case 'image/png':
                return '.png';
            default:
                //TO DO return error about file type
                return false;
        }
    }
    
    public function removeUploaded(){
        $request = $_REQUEST['gallery_remove'];
        $filename = substr_replace($request, '', 0, strrpos($request, '/') + 1);
        
        foreach(glob($this->galleryTempFolder.$filename.'.*') as $img){
            unlink($img);
        }
        
        return $this;
    }
    
    //Deletes all files in a directory
    public function cleanDir($dir){  
        $files = scandir($dir);
        foreach($files as $f){
            if(!is_dir($dir.$f)) {
                unlink($dir.$f);
            }
        }
        
        return $this;
    }
    
    public function getAlbums(){
        $gallery = new Gallery();
        $albums = $gallery->getAlbums();
        
        $content = array();
        
        for($i = 0; $i < count($albums); $i++) {
            $images = $this->getImagesFromAlbum($albums[$i]['id']);
            $content['albums'][$i]['id'] = $albums[$i]['id'];
            $content['albums'][$i]['title'] = $albums[$i]['title'];
            $content['albums'][$i]['images'] = $images;
        }
       
        return $content;
    }
    
    //Get all images from album
    public function getImagesFromAlbum($albumID){
        $gallery = new Gallery();
        $images = $gallery->getImages($albumID);
        
        return $images;
    } 
    
    public function newAlbum($title){
        if(strlen($title) > 250){
            self::$messages[] = 'Title is too long.';
            return false;
        }
        
        if($this->albumExists($title)){
            self::$messages[] = 'Album already exists.';
            return false;
        }
        
        if($this->createAlbum($title, $this->galleryImageFolder)){
            self::$messages[] = 'Album has been created.';
        }
        
        return $this;
    }
    
    private function createAlbum($title, $dir) {    
        $query = 'INSERT INTO gallery_albums (`title`)
            VALUES(:title)';
        $this->database->prepare($query)->execute(array( 
            ':title' => $title,
            ));
        
        $lastid = $this->database->lastInsertId();
        $path = $dir.$lastid;
        mkdir($path);
        
        return $this;
    }
    
    public function deleteImage($id){
        $query = 'SELECT path, album, album_cover FROM gallery WHERE id = :id';
        $result = $this->database->
                prepare($query)->
                execute(array(':id' => $id))->
                fetchAllAssoc()[0];
        
        $path = $result['path'];
        $isCover = $result['album_cover'];
        $albumID = $result['album'];
        
        if(is_file($path)){
            $filename = basename($path);
            unlink($this->galleryThumbnailFolder.$filename);
            unlink($path);
        }
        
        $query = null;
        $query = 'DELETE FROM gallery WHERE id = :id';
        $this->database->prepare($query)->execute(array(':id' => $id));
        
        if($isCover == 1){
            $firstImage = $this->getFirstImageFromAlbum($albumID);
            if($firstImage) {
                $this->setAlbumCover($firstImage);
            }
        }
        
        return $this;
    }
    
    private function getFirstImageFromAlbum($albumID){
        $query = 'SELECT id FROM gallery WHERE album = :album LIMIT 1';
        $result = $this->database->
                prepare($query)->
                execute(array('album' => $albumID))->fetchAllAssoc();
        
        if(count($result) > 0) {
            $firstImage = $result[0]['id'];
            return $firstImage;
        }
        
        return false;
    }
    
    public function deleteAlbum($id){
        $query = 'SELECT id FROM gallery WHERE album = :id';
        $images = $this->database->
                prepare($query)->
                execute(array(':id' => $id))->
                fetchAllAssoc();
        
        foreach($images as $img){
            $this->deleteImage($img['id']);
        }
        
        $dir = $this->galleryImageFolder.$id;
        rmdir($dir);
        
        $query = null;
        $query = 'DELETE FROM gallery_albums WHERE id = :id';
        
        $this->database->prepare($query)->execute(array(':id' => $id));
        
        return $this;
    }
    
    private function albumExists($title){
        $query = 'SELECT * FROM gallery_albums WHERE title = :title LIMIT 1';
        $result = $this->database->
                prepare($query)->
                execute(array(':title' => $title))->
                fetchAllAssoc();

        if(count($result) > 0){
            return true;
        } else {
            return false;
        }
    }
    
    public function setAlbumCover($id){
        
        $album = $this->getAlbum($id);
        
        if(!$album) {
            return false;
        }
        
        $query = 'UPDATE gallery SET album_cover = 0 WHERE album = :album';
        $this->database->prepare($query)->execute(array(':album' => $album));
        
        $query = null;
        
        $query = 'UPDATE gallery SET album_cover = 1 WHERE id = :id';
        $this->database->prepare($query)->execute(array(':id' => $id));
        
        return $this;
    }
    
    private function getAlbumName($id){
        $query = 'SELECT title FROM gallery_albums WHERE id = :id';
        $result = $this->database->
                prepare($query)->
                execute(array(':id' => $id))->
                fetchAllAssoc()[0]['title'];
        
        return $result;
    }
    
    public function getAlbum($id){
        
        $query = 'SELECT album FROM gallery WHERE id = :id';
        
        $id = $this->database->prepare($query)->execute(array(':id' => $id))->fetchAllAssoc()[0]['album'];
        
        if($id) {
            return $id;
        } else {
            return false;
        }
    }
}
