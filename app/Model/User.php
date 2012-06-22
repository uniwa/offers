<?php

class User extends AppModel {
    public $name = 'User';
    public $hasOne = array( 'Company', 'Student' );
    public $hasMany = array('Distance');

    public $validate = array(

        'username' => array(

            'notEmpty'=>array(
                'rule' => array('notEmpty'),
                'required' => true,
                'message' => 'Απαιτείται όνομα χρήστη'
            ),

            'alphanumeric' => array(
                'rule' => 'alphanumeric',
                'message' => 'Επιτρέπονται μόνο αλφαριθμητικά',
                'allowEmpty' => true
            ),

            'unique' => array(
                'rule' => 'isUnique',
                'message' => 'Αυτό το όνομα χρήστη χρησιμοποιείται ήδη',
                'allowEmpty' => true
            ),

            'max'=> array(

                'rule'=>array( 'between', 2, 16 ),
                'required'=>true,
                'message' => 'Ο κωδικός πρέπει να είναι μεταξύ 2 και 16 χαρακτήρων'
            ),

            'ldap' => array(
                'rule' => 'in_ldap',
                'required' => true,
                'message' => 'Αυτό το όνομα χρήστη χρησιμοποιείται ήδη'
            )
        ),

        'password' => array(

            'notEmpty'=>array(

                'rule' => array('notEmpty'),
                'required' => true,
                'message' => 'Απαιτείται κωδικός πρόσβασης'
            ),

            'length' => array (

                'rule' => array('between', 8, 16),
                'allowEmpty' => true,
                'message' => 'Ο κωδικός πρέπει να είναι μεταξύ 8 και 16 χαρακτήρων'
            ),

            'alphanumeric' => array(

                'rule' => '/^[\d\w!@#\$%&\*\^\+\?-_.,]+$/',
                'allowEmpty' => true,
                'message' => 'Υπάρχει κάποιος μη αποδεκτός χαρακτήρας'
            )

        ),

        'repeat_password' => array(

            'notEmpty'=> array(

                'rule'=>array('notEmpty'),
                'required'=>true,
                'message' =>'Απαιτείται επανάληψη κωδικού'
            ),

            'repeat' => array(

                'rule'=>array('passwordsMatch'),
                'required'=>true,
                'message'=>'Λανθασμένος κωδικός επαλήθευσης'
            ),

            'alphanumeric' => array(

                'rule' => '/^[\d\w!@#\$%&\*\^\+\?-_.,]+$/',
                'allowEmpty' => true,
                'message' => 'Υπάρχει κάποιος μη αποδεκτός χαρακτήρας'
            ),

            'length' => array (
                'rule' => array('between', 8, 16),
                'allowEmpty' => true,
                'message' => 'Ο κωδικός πρέπει να είναι μεταξύ 8 και 16 χαρακτήρων.'
            )
        ),

        'email' => array(
            'mail' => array(

                'rule' =>array('email'),
                'required' => true,
                'message' =>'Λανθασμένo e-mail'
            ),

            'notEmpty' => array(

                'rule' => array('notEmpty'),
                'required' =>true,
                'message' =>'Απαιτείται e-mail'
            )
        ),

        'terms_accepted' => array(
            'notEmpty' => array(
                'rule' => array('equalTo', '1'),
                'required' => 'create',
                'allowEmpty' => 'false',
                'message' =>'Απαιτείται αποδοχή των όρων χρήσης για να συνεχίσετε την εγγραφή.'
            )
        )
    );



    public function passwordsMatch() {

        if( $this->data['User']['password'] === $this->data['User']['repeat_password'] ) {

            return true;
        }

        return false;
    }

    public function beforeSave( $options = array() ) {
        $password = (isset($this->data['User']['password']))
            ?$this->data['User']['password']:null;
        if (!is_null($password)){
            $this->data['User']['password'] = AuthComponent::password($password);
        }

        return true;
    }

    public function in_ldap() {
        App::uses('LdapUtil', 'Lib');
        $ldap = new LdapUtil();
        return (! $ldap->exists($this->data['User']['username']));
    }

    public function email_confirm($token = null, $email = null) {
        $conditions = array('User.token' => $token);
        $id = $this->field('id', $conditions);
        $user_email = $this->field('email', $conditions);
        $result = false;
        if ($user_email)
            if ($user_email === $email) {
                $email_verified = $this->field('email_verified', $conditions);
                if (!$email_verified) {
                    $this->read(null, $id);
                    $this->set('email_verified', true);
                    $saved = $this->save(null, false);
                    $result = $id;
                }
            }

        return $result;
    }

}
