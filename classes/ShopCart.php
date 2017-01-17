<?php

class ShopCart extends Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getContent() {       
        if(isset($_GET['additem'])){
            $id = (int)$_GET['additem'];
            $this->addToCart($id);
        }      
        if(isset($_GET['rmvitem'])){
            $id = (int)$_GET['rmvitem'];
            $this->removeFromCart($id);
        }      
        $result = $this->calcItems();
        return $result;      
    }
    //Returns array of items in currently in cart
    private function calcItems(){
        $items = array();
        for ($i = 0; $i < count($_SESSION['cart']); $i++) {
            //Calculate total price and quantity of similar items
            foreach ($items as $k => $v) {
                if ($v['id'] == $_SESSION['cart'][$i]) {
                    $items[$k]['price'] += $items[$k]['price']/$items[$k]['quantity'];
                    $items[$k]['quantity'] += 1;                  
                    continue 2;
                }
            }
            //TO DO code sucks, fix
            $query = 'SELECT * FROM shop_' . $this->lang . ' WHERE id = :id';
            $item = $this->database->prepare($query)->execute(array(':id' => $_SESSION['cart'][$i]))->fetchAllAssoc();
            $items[$i] = $item[0];
            $items[$i]['quantity'] = 1;
        }
        return $items;
    }
    
    
    public function addToCart($id){
        $_SESSION['cart'][] = $id;
        return $this;
    }
    
    public function removeFromCart($id){
        $key = array_search($id, $_SESSION['cart']);
        if($key !== false){
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
        }
        return $this; 
        
    }
    
    public function getTemplateName() {
        return 'cart_'.$this->lang;
    }

    public function getLayoutName() {
        return 'shop';
    }

}
