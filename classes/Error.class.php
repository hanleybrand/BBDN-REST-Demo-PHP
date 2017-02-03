<?php
    namespace BbRest;
    class Error
    {
        public $label;
        public $msg;
        public $data;

        public function __construct($msg = "An error for which no special message has been crafted has occurred",
                                    $label = "Error",
                                    $data = null)
        {
            $this->label = $label;
            $this->msg = $msg;
            $this->data = $data;
        }
    }