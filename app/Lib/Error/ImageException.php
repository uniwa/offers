<?php

class ImageException extends CakeException {

    public function __construct($message = 'There was an error during image processing.') {
        parent::__construct($message);
    }
}
