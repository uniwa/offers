<?

class UsersController extends AppController {

    function login() {

        if( $this->Auth->login() ) {

            return $this->redirect( $this->Auth->redirect() );
        } else {

            $this->Session->setFlash(__("Δώστε έγκυρο όνομα και κωδικό χρήστη"), 'default', array(), 'auth' );  
        }
    }

    function logout() {

        $this->redirect( $this->Auth->logout() );

    }
}
