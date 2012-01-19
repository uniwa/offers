<?php

App::uses('BaseAuthenticate', 'Controller/Component/Auth');
App::uses('User', 'Model');
App::uses('LdapUtil', 'Lib');

class LdapAuthenticate extends BaseAuthenticate {

    public function authenticate(CakeRequest $request, CakeResponse $response) {

        $info = null;
        $ldap = new LdapUtil();

        var_dump( $request ); die();

        if( $ldap->auth( $username, $password ) ) {

            return $ldap->getInfo( $username );
        } else {

            return false;
        }

    }

}
