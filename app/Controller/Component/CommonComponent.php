<?php
class CommonComponent extends Component {

    public function max_upload() {
        // query php.ini and return maximum allowed upload
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $memory_limit = (int)(ini_get('memory_limit'));
        return min($max_upload, $max_post, $memory_limit);
    }

    public function upload_file_type($file) {
        // return uploaded filetype
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file);
        $mime_type = explode("/", $mime_type);
        return $mime_type[1];
    }

    public function get_image_dimensions($file) {
        // return image width and height
        // NOTE: it does not check if file is an image
        //
        if (! is_file($file)) return NULL;
        list($width, $height, $type, $attr) = getimagesize($file);
        return array($width, $height);
    }
}
