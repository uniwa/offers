<?php

class Image extends AppModel {

    public $name = 'Image';
    // Auto increment image_count when adding images for offers and companies
    //
    // Images should be auto incremented for companies, *only* when the
    // offer_id field is not set in the image. This denotes that the image is
    // associated with the company's profile (logo/company's photos) and not
    // with the offer.
    public $belongsTo = array(
                              'Company' => array(
                                  'counterCache' => true,
                                  'counterScope' => array(
                                      'Image.offer_id' => null)
                              ),
                              'Offer' => array(
                                    'counterCache' => true
                              )
                        );

    public static $validExtensions = array('jpeg', 'png', 'gif');

    /**
     * @short checks whether a file is an image based on its extension
     *
     * @param $file_type the extension or mimetype of the image
     * @param $valid An array with the valid image extensions
     *
     * @return true or false
     */
    public function is_valid($file_type, $valid = null) {

        if (is_null($valid))
            $valid = self::$validExtensions;
        else if (!is_array($valid))
            throw new InvalidArgumentException('$valid must be array.');

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
     * @param $generate_thumbs Create or not thumbnails for the given images
     * @param $thumb_size An array with the desired width and height of thumbnail
     * @param $foreign_keys Key => Value array containing required foreign key values
     *
     * @throws ImageExtensionException
     * @throws UploadFileException
     * @throws InvalidArgumentException
     *
     * @return Array of images
     */
    public function process($images,
                                   $foreign_keys = array(),
                                   $generate_thumbs = true,
                                   $thumb_size = null
                                   )
    {
        if ($thumb_size === null)
            $thumb_size = array('width' => THUMB_WIDTH);

        if (!is_array($thumb_size))
            throw new InvalidArgumentException('$thumb_size must be array or null.');

        if (!is_array($foreign_keys))
            throw new InvalidArgumentException('$foreign_keys must be array.');

        $photo = array();
        if (isset($images['tmp_name'])) $images = array($images);

        foreach ($images as $image) {
            $tmp = $this->_process($image, $foreign_keys);
            if (!empty($tmp)) {
                $photo = $tmp;
                if ($generate_thumbs === true)
                    $tmp = $this->_createThumbnail($tmp, $thumb_size);
                    $photo = array_merge($photo, $tmp);
            }
        }

        return $photo;
    }

    /**
     * @short helper function that sets the required image information
     * Creates the blob (base64 encoded) and sets all the required
     * information about the image in the returned array.
     *
     * @param $image The image to process
     * @param $foreign_keys Key => Value array containing required foreign key values
     *
     * @throws ImageExtensionException
     * @throws UploadFileException
     *
     * @return Array containing image information and data
     */
    private function _process($image, $foreign_keys) {
        if ((isset($image['tmp_name']) && $image['tmp_name'] == null ) ||
             isset($image['id']))
            return array();

        if (!is_uploaded_file($image['tmp_name']))
            throw new UploadFileException();

        if (!$this->is_valid($image['type']))
            throw new ImageExtensionException();

        $file = fread(fopen($image['tmp_name'], 'r'), $image['size']);
        $image['data'] = $file;
        // set the foreign keys if needed
        foreach (array_keys($foreign_keys) as $key)
            $image[$key] = $foreign_keys[$key];

        return $image;
    }

    /**
     * @short generates the thumbnail of the given image
     *
     * @param $source_img The image, the thumb of which will be generated
     * @param $thumb_size Array containing the desired width and height of the thumb
     *
     * @return The thumbnail ready to be saved in DB as blob
     */
    private function _createThumbnail($source_img, $thumb_size) {

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

        if (isset($thumb_size['width']) && isset($thumb_size['height'])) {
            $thumb_width = $thumb_size['width'];
            $thumb_height = $thumb_size['height'];
        }
        else if (isset($thumb_size['width'])) {
            $thumb_width = $thumb_size['width'];
            $thumb_height = floor($height * ($thumb_width / $width));
        }
        else if (isset($thumb_size['height'])) {
            $thumb_height = $thumb_size['height'];
            $thumb_width = floor($width * ($thumb_height / $height));
        }

        // create an empty thumbnail and resample the original image to fit its size
        $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
        imagecopyresampled($thumb, $source, 0, 0, 0, 0,
                           $thumb_width, $thumb_height,
                           $width, $height);

        // create a buffer to write the generated thumb in order to avoid
        // writing on filesystem. Store its size in bytes and the binary data.
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

        $result['size_thumb'] = $thumb_size;
        $result['data_thumb'] = $thumb_data;

        return $result;
    }
}
