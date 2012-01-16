<?php

class AppController extends Controller{

    public $components = array(
        
        'Session',
        'Auth' => array(
            'authenticate' => array(
                'Form' => array(
                    'scope' => array( 'User.is_banned' => 0 )
                )
            )
        )
    );

}