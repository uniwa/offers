<?php
class TokenComponent extends Object {
    function generate($entropy = "") {
        /* return a 40 chars unique id */
        return sha1(uniqid($entropy, true));
    }

    function to_id($token) {
        /* return user_id associated with given token */
        $profile =& ClassRegistry::init('User');
        $id = $user->find('first', array(
            'fields' => array('id'),
            'conditions' => array('token' => $token)
        ));
        if (isset($id['User']['id'])) {
            return $id['User']['id'];
        }
        return NULL;
    }
}
