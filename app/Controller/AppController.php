<?php

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


    /**
     * @short Properly modify image arrays so as to save them in the database
     * Takes an image or array of images as Form::input() (option 'type' => 'file')
     * helper function returns them and modifies it so as to save it in
     * opendeals.images table. If the given images already exist in the table
     * then nothing is done and an empty array is returned.
     *
     * @param $images The array of images, or image
     * @param $image_category The category of the image according to table opendeals.image_categories
     */
    protected function processImages($images, $image_category = 1) {

        if (isset($images['tmp_name'])) {
            // if one image
            $tmp = $this->_processImage($images, $image_category);
            if (empty($tmp))
                return $tmp;
            else
                return $tmp['Image'];
        } else {
            // if many images
            $photos = array();
            foreach ($images as $image) {
                $tmp = $this->_processImage($image, $image_category);
                if (!empty($tmp))
                    array_push($photos, $this->_processImage($image, $image_category));
            }
            if (empty($photos))
                return $photos;
            else
                return Set::extract('/Image/.', $photos);
        }
    }

    /**
     * @short helper function that sets the required image information
     * Creates the blob (base64 encoded) and all the other required
     * information about the image.
     *
     * @param $image The image to process
     * @param $image_category The category of image
     *
     * @throws ImageExtensionException
     * @throws UploadFileException
     */
    private function _processImage($image, $image_category) {
        if (isset($image['tmp_name']) && $image['tmp_name'] != null) {
            $photo = array();
            // if image already exists in DB then return empty array
            if (isset($image['id'])) return $photo;

            if (is_uploaded_file($image['tmp_name'])) {
                // check validity of image
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
