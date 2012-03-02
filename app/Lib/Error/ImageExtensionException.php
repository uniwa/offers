<?php

class ImageExtensionException extends CakeException {

    public function __construct($message = 'Μη αποδεκτός τύπος αρχείου εικόνας.') {
        parent::__construct($message);
    }
}
