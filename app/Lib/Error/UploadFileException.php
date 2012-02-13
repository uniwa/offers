<?php

class UploadFileException extends CakeException {

    public function __construct($message = 'Παρουσιάστηκε σφάλμα κατά το ανέβασμα του αρχείου.') {
        parent::__construct($message);
    }
}
