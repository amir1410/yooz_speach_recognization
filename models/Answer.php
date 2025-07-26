<?php
class Answer {
    public $id;
    public $user_id;
    public $question_id;
    public $answer;
    public $voice_path;
    public $is_correct;
    public $score;

    public function __construct($row) {
        $this->id = $row['id'];
        $this->user_id = $row['user_id'];
        $this->question_id = $row['question_id'];
        $this->answer = $row['answer'];
        $this->voice_path = $row['voice_path'];
        $this->is_correct = $row['is_correct'];
        $this->score = $row['score'];
    }
}