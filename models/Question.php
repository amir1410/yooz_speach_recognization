<?php
class Question {
    public $id;
    public $text;
    public $type; // text, choice, voice
    public $score;
    public $options; // array|null
    public $correct_option; // index|null
    public $priority;
    public $has_score;

    public function __construct($row) {
        $this->id = $row['id'];
        $this->text = $row['text'];
        $this->type = $row['type'];
        $this->score = $row['score'];
        $this->options = $row['options'] ? json_decode($row['options'], true) : null;
        $this->correct_option = $row['correct_option'];
        $this->priority = $row['priority'];
        $this->has_score = $row['has_score'];
    }
}