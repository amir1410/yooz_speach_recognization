<?php
class User {
    public $id;
    public $chat_id;
    public $full_name;
    public $goal;
    public $city;
    public $phone;
    public $level;
    public $score;
    public $answers_count;

    public function __construct($row) {
        $this->id = $row['id'];
        $this->chat_id = $row['chat_id'];
        $this->full_name = $row['full_name'];
        $this->goal = $row['goal'];
        $this->city = $row['city'];
        $this->phone = $row['phone'];
        $this->level = $row['level'];
        $this->score = $row['score'];
        $this->answers_count = $row['answers_count'];
    }
}