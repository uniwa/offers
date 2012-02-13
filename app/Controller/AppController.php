<?php

App::uses('ImageException', 'Error');
App::uses('ImageExtensionException', 'Error');
App::uses('UploadFileException', 'Error');

class AppController extends Controller{

    public $components = array(
        'Session',
        'Auth' => array(
            'authenticate' => array(
                'Ldap',
                'Form'
            )
        )
    );

    function beforeFilter() {

        //clear authError default message
        $this->Auth->authError = " ";
    }


    function generateHash($size = 32) {

        return substr(md5(date('c')), 0, $size > 32 ? 32 : $size);
    }


    protected function isImage($file_type) {

        $valid = array('jpeg', 'png', 'gif');
        $extension = explode('/', $file_type);
        $extension = $extension[1];

        return in_array($extension, $valid);
    }


    protected function processImages($images, $image_category = 1) {
        if (isset($images['tmp_name'])) {
            $tmp = $this->_processImage($images, $image_category);
            if (empty($tmp))
                return $tmp;
            else
                return $tmp['Image'];
        } else {
            $photos = array();
            foreach ($images as $image) {
                $tmp = $this->_processImage($image, $image_category);
                if (!empty($tmp))
                    array_push($photos, $this->_processImage($image, $image_category));
            }
            if (empty($photos))
                return $photos;
            else
                return  Set::extract('/Image/.', $photos);
        }
    }

    private function _processImage($image, $image_category) {
        if (isset($image['tmp_name']) && $image['tmp_name'] != null) {
            $photo = array();

            // if image already exists in DB then return empty array
            if (isset($image['id'])) return $photo;

            if (is_uploaded_file($image['tmp_name'])) {
                if ($this->isImage($image['type'])) {
                    $file = fread(fopen($image['tmp_name'], 'r'),
                                    $image['size']);
                    $photo['Image'] = $image;
                    $photo['Image']['data'] = base64_encode($file);
                    $photo['Image']['image_category_id'] = $image_category;
                    return $photo;
                } else {
                    throw new ImageExtensionException();
                }
            } else {
                throw new UploadFileException();
            }
        } else {
            return array();
        }
    }
}
