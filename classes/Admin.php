<?php

class Admin extends Controller {

    private $template;

    public function __construct() {
        parent::__construct();
    }

    public function getContent() {

        $response = $this->processRequest();
        if($response){
            return $response;
        } else {
            return false;
        }
    }

    private function processRequest() {
        if (isset($_POST['submit_login'])) {
            $this->login();
        }

        if (isset($_GET['logout'])) {
            session_destroy();
            header('Location: /');
        }

        if (isset($_SESSION['admin'])) {
            if (isset($_GET['adminpage'])) {
                switch ($_GET['adminpage']) {
                    case 'news':
                        if (isset($_POST['news_content'])) {
                            $news = new AdminNews();
                            $news->submit();
                        }
                    case 'gallery':
                        $gallery = new AdminGallery();      
                        if (isset($_POST['create_album'])) {
                            $gallery->newAlbum($_POST['album_title']);
                        }
                        if (isset($_GET['delete_album'])) {
                            $gallery->deleteAlbum((int) $_GET['delete_album']);
                        }
                        
                        return $gallery->getAlbums();

                    case 'gallery_edit':
                        if (isset($_GET['album_id'])) {
                            $albumID = (int) $_GET['album_id'];
                        } else if (isset($_POST['album_id'])) {
                            $albumID = (int) $_POST['album_id'];
                        } else {
                            header('Location: ?adminpage=gallery');
                        }
                        
                        $gallery = new AdminGallery();
                        
                        if (isset($_GET['delete_image'])) {
                            $gallery->deleteImage((int) $_GET['delete_image']);
                        }
                        if (isset($_POST['save_images'])) {   
                            $gallery->save();
                        }
                        
                        if(isset($_GET['cover_image'])){
                            $imageID = (int)$_GET['cover_image'];
                            if($imageID){
                                $gallery->setAlbumCover($_GET['cover_image']);
                            }
                        }
                        
                        $gallery->cleanDir($this->galleryTempFolder);
                        
                        //var_dump('<pre>'.print_r($gallery->getImagesFromAlbum($albumID), true).'</pre>');
                        return $gallery->getImagesFromAlbum($albumID);
                    case 'shop':
                        if (isset($_POST['shop_submit'])) {
                            $shop = new AdminShop();
                            $shop->newArticle();
                        } 
                }
            } else {
                if(isset($_GET['news_delete'])){
                    $news = new AdminNews();
                    $news->deleteNews((int)$_GET['news_delete']);
                    header('Location: news');
                }
                
                if (isset($_GET['gallery_upload'])) {
                    $gallery = new AdminGallery();
                    $gallery->upload();
                }               
                
                if (isset($_GET['gallery_remove'])) {
                    $gallery = new AdminGallery();
                    $gallery->removeUploaded();
                }  
                
                if(isset($_GET['shop_delete'])) {
                    $shop = new AdminShop();
                    $shop->deleteItem($_GET['shop_delete']);
                   
                }
            }
        }
    }


    private function login() {
        $username = $_POST['username'];
        $password = $_POST['password'];

        if ($username !== ADMIN_USERNAME || $password !== ADMIN_PASSWORD) {
            self::$messages[] = 'Wrong username/password.';
        } else {
            $_SESSION['admin'] = true;
            header('Location: ?admin=true&adminpage=news');
        }
        return $this;
    }

    public function getTemplateName() {
        if ($this->template) {
            return $this->template;
        }
        if (isset($_SESSION['admin'])) {
            if (isset($_GET['adminpage'])) {
                return 'admin_' . $_GET['adminpage'];
            } else {
                return 'admin_news';
            }
        } else {
            return 'admin_login';
        }
    }

    public function getLayoutName() {
        return 'admin';
    }
}
