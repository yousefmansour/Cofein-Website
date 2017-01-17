<?php

class AdminShop extends Admin {
    public function __construct() {
        parent::__construct();
    }
    
    //TO DO image names must be in english
    //TO DO save POST fields on verifyData error
    protected function newArticle() {     
        
        if($this->verifyData()){      
            $name = filter_input(INPUT_POST, 'shop-name', FILTER_SANITIZE_STRING);
            $description = filter_input(INPUT_POST, 'shop-desc', FILTER_SANITIZE_STRING);
            $price = (double) filter_input(INPUT_POST, 'shop-price', FILTER_SANITIZE_STRING);
            $imagePath = $_FILES['shop-image']['tmp_name'];
            $imageName = uniqid();
            $imageDestination = $this->shopImageFolder.$imageName;
            if(!move_uploaded_file($imagePath, $imageDestination)){
                throw new Exception('Could not move image '.$imageName.' to '. $imageDestination);
            }

            $query = "INSERT INTO shop_" . $this->lang . " (`name`, `description`, `price`, `image`)
                VALUES (:name, :description, :price, :image)";
            $this->database
                    ->prepare($query)
                    ->execute(array(
                        'name' => $name, 
                        'description' => $description, 
                        'price' => $price, 
                        'image' => $imageDestination));

            self::$messages[] = "Item has been added to the shop.";
            return true;
        }
    }
    
    private function verifyData(){
         do {
            if (!isset($_POST['shop-name']) || strlen($_POST['shop-name']) < 1) {
                self::$messages[] = 'Article name missing.';
                return false;
            }
            if (!isset($_POST['shop-desc']) || strlen($_POST['shop-desc']) < 1) {
                self::$messages[] = 'Short description missing.';
                return false;
            }
            if (!isset($_POST['shop-price']) || strlen($_POST['shop-price']) < 1) {
                self::$messages[] = 'Price missing.';
                return false;
            }
            if (!isset($_FILES['shop-image']['tmp_name']) || strlen($_FILES['shop-image']['tmp_name']) < 1) {
                self::$messages[] = 'Please upload an image.';
                return false;
            }
        } while (false);
        
        return true;
    }

    public function deleteItem($id) {
        $query = 'SELECT image FROM shop_'.$this->lang.' WHERE id = :id';
        $image = $this->database->prepare($query)->execute(array(':id' => $id))->fetchAllAssoc()[0]['image'];
        var_dump($image);
        unlink($image);
        $query = 'DELETE FROM shop_'.$this->lang.' WHERE id = :id';
        $this->database->prepare($query)->execute(array(':id' => $id));   
        
    }
    
    public function getLayoutName() {
        return false;
    }
}
