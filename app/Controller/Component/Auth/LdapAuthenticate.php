<?php

App::uses('BaseAuthenticate', 'Controller/Component/Auth');
App::uses('User', 'Model');
App::uses('LdapUtil', 'Lib');

class LdapAuthenticate extends BaseAuthenticate {

    public function authenticate(CakeRequest $request, CakeResponse $response) {
        $username = $request['data']['User']['username'];
        $password = $request['data']['User']['password'];
        $ldap = new LdapUtil();

        if ($ldap->auth($username, $password)) {
            $user_info = $ldap->getInfo($username);
            $user = $this->_findUser($username,$password);
            if (empty($user)) {
                return $this->saveLdapInfo($user_info);
            } else {
                return $user;
            }
        } else {
            return false;
        }
    }
    
    // Override to return hash for empty password
	protected function _password($password) {
		return AuthComponent::password('');
	}

    private function saveLdapInfo($data) {
        $model = new User();

        $user = array(
            'User' => array(
                'username' => $data['username'],
                'password' => '',
                'email' => $data['email'],
                'is_banned' => 0,
                'terms_accepted' => 0,
                'role' => 'student'),
            'Student' => array(
                'firstname' => $data['first_name'],
                'lastname' => $data['last_name']));

        $model->saveAssociated($user, array('validate' => false));
        $user['User']['id'] = $model->id;

        return $user['User'];
    }
}
