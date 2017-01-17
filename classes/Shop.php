<?php
class Shop extends Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getContent() {        
        
        if(isset($_GET['additem'])){
            $id = (int)$_GET['additem'];
            $cart = new ShopCart();
            $cart->addToCart($id);
        }      
        if(isset($_GET['rmvitem'])){
            $id = (int)$_GET['rmvitem'];
            $cart = new ShopCart();
            $cart->removeFromCart($id);
        }      
        
        if(isset($_GET['addtocart'])) {
            $id = (int)$_GET['addtocart'];
            if($id) {
                if(!isset($_SESSION['cart'])){
                    $_SESSION['cart'] = array();
                }
                $_SESSION['cart'][] = $id;
            }
        }
        
        if(isset($_GET['shop_details'])) {
            $query = "SELECT * FROM shop_".$this->lang. " WHERE id = :id";
            $result = $this->database->prepare($query)->execute(array('id' => (int)$_GET['shop_details']))->fetchAllAssoc()[0];
        } else {
            $query = "SELECT * FROM shop_".$this->lang. " ORDER BY id DESC";
            $result['items'] = $this->database->prepare($query)->execute()->fetchAllAssoc();
            $result['itemsInCart'] = count($_SESSION['cart']);
        }

        return $result;
         
    }

    public function getTemplateName() {
        if(isset($_GET['shop_details'])) {
            return 'shop_details';
        } else if (isset($_GET['checkout'])) {
            return 'checkout';
        } else if (isset($_GET['cart'])) {
            return 'cart';
        }
        return 'shop_'.$this->lang;
    }

    public function getLayoutName() {
        return 'default';
    }

}
