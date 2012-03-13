<?php

class ImagesController extends AppController {

    public $name = 'Images';

    function view ($id = null) {

        if ($id == null) throw new BadRequestException();

        $options['conditions'] = array('Image.id' => $id);
        $options['recursive'] = -1;
        $image = $this->Image->find('first', $options);

        if (empty($image))
            throw new NotFoundException('Η συγκεκριμένη εικόνα δεν βρέθηκε');

        header('Content-Type: '.$image['Image']['type']);
        header('Content-Disposition: filename='.$image['Image']['name']);
        header('Content-Length: '.$image['Image']['size']);
        echo $image['Image']['data'];
        exit();
    }


    function delete ($id = null) {

        if ($id == null) throw new BadRequestException();

        $options['conditions'] = array('Image.id' => $id);
        $options['recursive'] = 0;
        $image = $this->Image->find('first', $options);

        if (empty($image)) throw new NotFoundException();

        if ($image['Company']['user_id'] !== $this->Auth->User('id'))
            throw new ForbiddenException();

        if ($this->Image->delete($image['Image']['id'], false))
            $this->Session->setFlash('Η εικόνα διαγράφηκε επιτυχώς.');
        else
            $this->Session->setFlash('Παρουσιάστηκε σφάλμα κατά τη διαγραφή της εικόνας.');

        $this->redirect($this->referer());
    }

}
