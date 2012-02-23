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


    protected function generateHash($size = 32) {

        return substr(md5(date('c')), 0, $size > 32 ? 32 : $size);
    }


    protected function isImage($file_type, $valid = array('jpeg', 'png', 'gif')) {

        if (strpos($file_type, '/') !== false) {
            $extension = explode('/', $file_type);
            return in_array($extension[1], $valid);
        } else
            return in_array($file_type, $valid);
    }


    /**
     * @short Properly modify image arrays so as to save them in the database
     * Takes an image or array of images (as returned by Form::input() helper)
     * and modifies it so as to save it in opendeals.images table.
     * Note: already existing images are _not_ duplicated
     *
     * @param $images The array of images, or image
     * @param $image_category The category of the image according to table opendeals.image_categories
     * @param $foreign_keys Key => Value array containing required foreign key values
     *
     * @throws ImageExtensionException
     * @throws UploadFileException
     */
    protected function processImages($images,
                                     $image_category = 1,
                                     $foreign_keys = array())
    {
        if (isset($images['tmp_name']))
            // if one image
            return $this->_processImage($images, $image_category, $foreign_keys);

        else {
            // if many images
            $photos = array();
            foreach ($images as $image) {
                $tmp = $this->_processImage($image, $image_category, $foreign_keys);
                if (!empty($tmp)) $photos[] = $tmp;
            }
            return $photos;
        }
    }

    /**
     * @short helper function that sets the required image information
     * Creates the blob (base64 encoded) and sets all the required
     * information about the image in the returned array.
     *
     * @param $image The image to process
     * @param $image_category The category of image
     * @param $foreign_keys Key => Value array containing required foreign key values
     *
     * @throws ImageExtensionException
     * @throws UploadFileException
     */
    private function _processImage($image, $image_category, $foreign_keys) {
        if ((isset($image['tmp_name']) && $image['tmp_name'] == null ) ||
             isset($image['id']))
            {
                return array();
            }

        if (!is_uploaded_file($image['tmp_name']))
            throw new UploadFileException();

        if (!$this->isImage($image['type']))
            throw new ImageExtensionException();

        $file = fread(fopen($image['tmp_name'], 'r'), $image['size']);
        $image['data'] = base64_encode($file);
        $image['image_category_id'] = $image_category;
        // set the foreign keys if needed
        foreach (array_keys($foreign_keys) as $key)
            $image[$key] = $foreign_keys[$key];

        return $image;
    }

    private function _createThumbnail($source_img, $thumb_width = 260) {

        $extension = explode('/', $source_img['type']);
        $extension = $extension[1];

        switch ($extension) {
            case 'jpeg':
                $source = imagecreatefromjpeg($source_img['tmp_name']);
                break;
            case 'png':
                $source = imagecreatefrompng($source_img['tmp_name']);
                break;
            case 'gif':
                $source = imagecreatefromgif($source_img['tmp_name']);
                break;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $thumb_height = floor($height * ($thumb_width / $width));
        $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0,
                           $thumb_width, $thumb_height,
                           $width, $height);

        ob_start();
        switch ($extension) {
            case 'jpeg':
                imagejpeg($thumb, null, 100);
                break;
            case 'png':
                imagepng($thumb, null, 0, PNG_NO_FILTER);
                break;
            case 'gif':
                imagegif($thumb);
                break;
        }
        $thumb_data = ob_get_contents();
        $thumb_size = ob_get_length();
        ob_end_clean();

        $result = $source_img;
        $result['name'] = 'thumb_'.$result['name'];
        $result['size'] = $thumb_size;
        $result['data'] = base64_encode($thumb_data);
        // thumbnail category
        $result['image_category_id'] = 2;

        return $result;
    }
}
