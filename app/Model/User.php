<?php

class User extends AppModel {
    public $name = 'User';
    public $hasOne = array( 'Company', 'Student' );

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
        )


    );



    public function passwordsMatch() {

        if( $this->data['User']['password'] === $this->data['User']['repeat_password'] ) {

            return true;
        }

        return false;
    }

    public function beforeSave( $options = array() ) {
        if (!is_null($this->data['User']['password'])){
            $this->data['User']['password'] = AuthComponent::password($this->data['User']['password']);
        }

        return true;
    }

}
