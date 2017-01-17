<?php

class AdminNews extends Admin {

    public function __construct() {
        parent::__construct();
    }

    protected function submit() {
        $title = $_POST['news_title'];
        $content = $_POST['news_content'];
        $date = time();

        $query = 'INSERT INTO news_bg (`date`, `title`, `content`)
            VALUES(:date, :title, :content)';

        if ($this->database->prepare($query)->execute(array(
                    ':date' => $date,
                    ':title' => $title,
                    ':content' => $content))) {
            self::$messages[] = 'News have been submitted.';
        } else {
            self::$messages[] = 'Error submitting news.';
        }
    }

    public function deleteNews($id){
        $query = 'DELETE FROM news_'.$this->lang.' WHERE id=:id';
        $this->database->prepare($query)->execute(array(':id' => $id));
    }
    
    public function getLayoutName() {
        return 'admin';
    }
}
