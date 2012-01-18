<?php

class AppController extends Controller{

    public $components = array(
        
        'Session',
        'Auth' => array(
            'authenticate' => array(
                'Form'
            )
        )
    );

    function beforeFilter() {

        //clear authError default message
        $this->Auth->authError = " ";
    }

}
