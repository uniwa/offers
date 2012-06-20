<?php
class TokenComponent extends Component {
    function generate($entropy = "") {
        list($usec, $sec) = explode(' ', microtime());
        mt_srand($usec + $sec);
        $r = (string) mt_rand();
        $entropy .= $r;
        /* return a 40 chars unique id */
        return sha1(uniqid($entropy, true));
    }

    function to_id($token) {
        /* return user_id associated with given token */
        $user = ClassRegistry::init('User');
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
