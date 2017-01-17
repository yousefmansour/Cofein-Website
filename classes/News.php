<?php 
class News extends Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getContent(){
     
        if(isset($_GET['offset']) && isset($_GET['limit'])){
            $offset = (int)$_GET['offset'];
            $limit = (int)$_GET['limit'];
        }
        
        if(!isset($offset)) {
            $offset = 0;
        }
        
        if(!isset($limit)) {
            $limit = 4;
        }
        
        $content = $this->getArticles($offset, $limit);

        for($i = 0; $i < count($content); $i++){                         
            $content[$i]['date'] = $this->createDate($this->lang, $content[$i]['date']);
        }
        
        if(isset($_GET['offset'])){
            echo json_encode($content);
            exit;
        } else {
            return $content;
        }
    }
    
    public function getArticles($offset, $limit){
        
        $query = 'SELECT * FROM news_'.$this->lang.' ORDER BY id DESC LIMIT '.$limit.' OFFSET '.$offset;
        $articles = $this->database->prepare($query)->execute()->fetchAllAssoc();
        
        return $articles;
    }
    
    public function createDate($lang, $time){
        if($lang === 'bg'){
            $monthsBG = ["Януари", "Февруари", "Март", "Април", "Май", "Юни", "Юли", "Август", "Септември", "Октомври", "Ноември", "Декември"];
            $month = (int)strftime("%m", $time) - 1;
            return strftime("%d ".$monthsBG[$month]." %Y", (int)$time);
        } else {
            return date('d F Y', $time);
        }
    } 
    
    public function getTemplateName(){
        return 'news';
    }

    public function getLayoutName() {
        return 'default';
    }
}

