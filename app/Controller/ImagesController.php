<?php

class ImagesController extends AppController {

    public $name = 'Images';

    function view($id = null) {
        $options['conditions'] = array('Image.id' => $id);
        $options['recursive'] = -1;
        $image = $this->Image->find('first', $options);

        if (empty($image)) {
            throw new NotFoundException('Η συγκεκριμένη εικόνα δεν βρέθηκε');
        } else {
            header('Content-type: '.$image['Image']['type']);
            echo base64_decode($image['Image']['data']);
            exit();
        }
    }

}
