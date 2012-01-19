<?php

App::uses('BaseAuthenticate', 'Controller/Component/Auth');
App::uses('User', 'Model');
App::uses('LdapUtil', 'Lib');

class LdapAuthenticate extends BaseAuthenticate {

    public function authenticate(CakeRequest $request, CakeResponse $response) {

        $username = $request['data']['User']['username'];
        $password = $request['data']['User']['password'];
        $ldap = new LdapUtil();

        if( $ldap->auth( $username, $password ) ) {

            return $ldap->getInfo( $username );
        } else {

            return false;
        }

    }

}
