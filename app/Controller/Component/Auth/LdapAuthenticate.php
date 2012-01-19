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

            $user_info = $ldap->getInfo( $username );
            return $this->saveLdapInfo( $user_info );

        } else {

            return false;
        }

    }

    private function saveLdapInfo( $data ) {

        $model = new User();
        $user = $model->find( 'all', 
            array( 'conditions' => array( 'username' => $data['username'] ) ) 
        );


        if( empty($user) ) {

            $new_user = array( 
                'User' => 
                array(  
                    'username' => $data['username'],
                    'password' => '',
                    'email' => $data['email'],
                    'is_banned'=>0,
                    'role' => 'student' ),
                'Student' =>
                    array(  
                        'firstname' => $data['first_name'],
                        'lastname' => $data['last_name'] ) );
                                
            
            $model->saveAssociated( $new_user, array( 'validate' => false ) );
            return $new_user['User'];
        }

        //return user data and Auth component 
        //automagicaly wite's them in Auth.User
        //inside Session 
        return $user[0]['User'];
    }

}
