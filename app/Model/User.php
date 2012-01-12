<?php
    
class User extends AppModel {
    public $name = 'User';
    public $hasOne = array( 'Company', 'Student' );

    public $validate = array(

        'username' => array( 
            'required' =>array( 
                'rule' => array('notEmpty'), 
                'message' => 'Απαιτείται όνομα χρήστη'
            ) 
        ),
        'password' => array( 
            'required' => array(
                'rule' => array('notEmpty'),
                'message' => 'Απαιτείτα κωδικός πρόσβασης'
            )
        )
    );

}

