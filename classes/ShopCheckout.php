<?php

class ShopCheckout extends Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getContent() {
       if(isset($_POST['submit'])){
           return $this->checkout();
       }
    }
    private function checkout(){
        
            $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
            $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_NUMBER_INT);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
            $info = filter_input(INPUT_POST, 'info', FILTER_SANITIZE_STRING);
            
            if(strlen((int)$phone < 5)) {
                self::$messages[] = 'Phone number is not valid.';
            }
            
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                self::$messages[] = 'E-mail is not valid.';
                return true;
            }
            
            $query = 'INSERT INTO orders (`name`,`phone`,`email`,`address`,`info`)
                VALUES (:name, :phone, :email, :address, :info)';
            $this->database->prepare($query)->execute(array(
                ':name' => $name,
                ':phone' => $phone,
                ':email' => $email,
                ':address' => $address,
                ':info' => $info
            ));
            self::$messages[] = 'Your order has been submitted.';
            return true;
        }
    
    public function getTemplateName() {
        return 'checkout_'.$this->lang;
    }
    
    public function getLayoutName(){
        return 'shop';
    }
}
