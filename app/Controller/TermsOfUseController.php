<?php
class TermsOfUseController extends AppController {

    public $uses = array('User');

    public function beforeFilter(){
        parent::beforeFilter();
        $this->Auth->allow( 'index' );

    }

    //terms of use action
    public function index() {

        $data = $this->request->data;
        if( !empty( $data ) ) {

            $accept = $data['User']['accept'];
            if( $accept == 1 ) {

               // $this->loadModel( 'User', $id );
                $this->User->id = $this->Auth->user('id');
                $this->User->saveField('terms_accepted', true);

                $this->Session->setFlash( __('Έχετε αποδεχτεί του όρους χρήσης'), 'default',
                   array( 'class'=>Flash::Success ) ); 
                $this->redirect( array( 'controller'=>'Offers', 'action' => 'index' ) );
            } else {

                $this->Session->setFlash( __('Δεν έχετε αποδεχτεί τους όρους χρήσης'), 'default', 
                    array( 'class'=>Flash::Error ) );
                $this->Auth->logout();
                $this->redirect( array( 'controller'=>'Offers', 'action' => 'index' ) );
            }

        }
    }

}
