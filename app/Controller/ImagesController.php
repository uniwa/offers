<?php

class ImagesController extends AppController {

    public $name = 'Images';

    // View normal image
    public function view($id = null) {
        $this->show($id, IMG_NORMAL);
    }

    // View thumbnail
    public function thumb($id = null) {
        $this->show($id, IMG_THUMB);
    }

    private function show($id, $type = IMG_NORMAL) {
        if ($id == null) throw new BadRequestException();

        $options['conditions'] = array('Image.id' => $id);
        $options['recursive'] = -1;
        $image = $this->Image->find('first', $options);

        if (empty($image))
            throw new NotFoundException('Η συγκεκριμένη εικόνα δεν βρέθηκε');

        $data = 'data';
        $size = 'size';
        $thumb = '_thumb';
        if ($type == IMG_THUMB) {
            $data .= $thumb;
            $size .= $thumb;
        }

        header('Content-Type: '.$image['Image']['type']);
        header('Content-Disposition: filename=thumb_'.$image['Image']['name']);
        header('Content-Length: '.$image['Image'][$size]);
        echo $image['Image'][$data];
        exit();
    }

    function delete($id = null) {
        if ($id == null) throw new BadRequestException();

        $options['conditions'] = array('Image.id' => $id);
        $options['recursive'] = 0;
        $image = $this->Image->find('first', $options);

        if (empty($image)) throw new NotFoundException();

        if ($image['Company']['user_id'] !== $this->Auth->User('id'))
            throw new ForbiddenException();

        if ($this->Image->delete($image['Image']['id'], false))
            $this->Session->setFlash('Η εικόνα διαγράφηκε επιτυχώς.',
                                     'default',
                                     array('class' => Flash::Success));
        else
            $this->Session->setFlash('Παρουσιάστηκε σφάλμα κατά τη διαγραφή της εικόνας.',
                                     'default',
                                     array('class' => Flash::Error));

        $this->redirect($this->referer());
    }

}
