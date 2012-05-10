<?php

App::uses('ImageExtensionException', 'Error');
App::uses('UploadFileException', 'Error');

App::import('Vendor', 'OfferStates');
App::import('Vendor', 'Flash');

class AppController extends Controller{

    public $components = array(
        'Session',
        'Auth' => array(
            'authenticate' => array(
                'Ldap',
                'Form'
            )
        ),
        'RequestHandler'
    );

    public $helpers = array(
        'Session',
        'Form',
        'Html',
        'Js' => array('Jquery'),
        'Tb' => array('className' => 'TwitterBootstrap.TwitterBootstrap')
    );

    function beforeFilter() {
        //clear authError default message
        $this->Auth->authError = " ";

        // When logged user has not accepted terms,
        // redirect to terms of use (only allow logout)
        $cur_user = $this->Auth->user();
        if (!is_null($cur_user)) {
            if (($cur_user['role'] === ROLE_STUDENT) && !($this->request['controller'] == 'users' && $this->request['action'] == 'terms')){
                if (!$cur_user['terms_accepted']) {
                    if (!($this->request['controller'] == 'users' && $this->request['action'] == 'logout')) {
                        $this->redirect(array('controller' => 'users', 'action' => 'terms'));
                    }
                }
            }
        }
    }

    public function is_authorized($user) {
        // main authorization function
        // override in each controller

        // admin can access every action
        if (isset($user['role']) && $user['role'] === ROLE_ADMIN) {
            return true;
        }

        // default deny
        return false;
    }

    // Convenience method for throwing exceptions while maintaining support for
    // the webservice api. This is to replace all occurences of `throw new
    // Exception(…)' where access to the webservice api is granted.
    //
    // NOTE: Do NOT use this method when all is needed is to return an error
    // code in response to a webservice api call. Use
    // AppController::notify() instead.
    //
    // @param $exception the name of the exception that is to be thrown in case
    //      of html response type, eg `NotFoundException'
    // @param $message the message to display to the user. For api calls, this
    //      affects the content; the header defaults to the description of the
    //      defined code
    // @param $code the code of the exception; if specified, it must be a valid
    //      code and in accordance with CakeResponse::httpCodes(). Generally, it
    //      is a good idea to specify a code.
    protected function alert($exception, $message, $code = 0) {
        // the following two variables should be initialized elsewhere as they
        // are oftenly used
        $is_webservice =
            $this->RequestHandler->prefers(array('xml', 'json', 'js')) != null;

        // if no `code' was specified, instantiate the exception to get its
        // default code
        if ($code == 0) {

            $throwable = new $exception($message);
            $code = $throwable->getCode();
        }

        if ($is_webservice) {

            // should URI be passed in as $extra, or should this become the
            // default behaviour?
            $this->api_compile_response($message, $code);

        } else {

            // if `code' was specified, then the exception object will not have
            // been instantiated yet
            if (empty($throwable)) {
                $throwable = new $exception($message, $code);
            }
            throw $throwable;
        }
    }

    // Convenience method that displays an instant message. It removes the need
    // to manually either call Session::setFlash() method or prepare a response
    // to be returned to an api call. It also enables javascript errors (jsonp)
    // to be returned.
    //
    // May also be used to return an error code specifically for a webservice
    // api call. For example:
    //  $this->notify('Argument missing', null, 406, $this->request->here());
    //
    // @param $flash 0-based array of parameters to be passed into
    //      SessionComponent::setFlash() method directly; must contain AT LEAST
    //      one parameter (which corresponds to the message itself).
    //      NOTE: If it is certain that a webservice api call will be serviced,
    //      a string may be passed in, as well!
    // @param $redirect 0-based array of parameters to be passed into
    //      AppController::redirect() method directly. If left empty or omitted,
    //      no redirection takes place; defaults to null.
    // @param $status affects the status code that appears in the *body* and
    //      *header* of an xml/json(p) response; defaults to 200. NOTE: simply
    //      setting this to an error code does NOT result in an exception to be
    //      thrown for HTML response type; this only affects the webservice
    //      behaviour
    // @param $extra additional messages to be returned in a webservice api
    //      call. Numeric keys are NOT supported. The following should NOT be
    //      used either: `status', `@status' `message', '_serialize'
    protected function notify(
            $flash, $redirect = null, $status = null, $extra = null) {

        // the following two variables should be initialized elsewhere as they
        // are oftenly used
        $is_webservice =
            $this->RequestHandler->prefers(array('xml', 'json', 'js')) != null;

        $response_type = $this->RequestHandler->prefers();

        if ($is_webservice) {

            // get message from setFlash parameters
            if (is_array($flash)) {
                $flash = reset($flash);
            }
            $this->api_compile_response($flash, $status, $extra);

        } else {

            call_user_func_array(array(&$this->Session, 'setFlash'), $flash);

            // redirection does not take place in the webservice api
            if (!empty($redirect)) {
                call_user_func_array(array(&$this, 'redirect'), $redirect);
            }
        }
    }

    // Performs the necessary initializations so that a webservice api call
    // response may be rendered.
    // @param $message convenience for passing a single string for the `message'
    //      element; may be omitted
    // @param $code the HTTP status code of the response; defaults to 200
    // @param $extra additional messages to be returned in a webservice api
    //      call. Numeric keys are NOT supported. The following should NOT be
    //      used either: `status', `@status' `message', '_serialize'
    public function api_compile_response(
            $message = null, $code = 200, $extra = array()) {

        // get value of this from elsewhere
        $response_type = $this->RequestHandler->prefers();

        // status code should appear as an attribute in an xml response
        $status_key = (($response_type == 'xml')?'@':'') . 'status_code';

        $response = array();
        if (!empty($code)) {
            $response[$status_key] = $code;
        }
        if (!empty($message)) {
            $response['message'] = $message;
        }

        // get format description for this status $code and set the header
        $code_desc = $this->response->httpCodes($code);
        $this->response->header('HTTP/1.1 '.$code, $code_desc);

        // append any extra information
        if (!empty($extra)) {
            $response = array_merge($response, $extra);
        }

        // make preparation for views
        if ($response_type == 'js') {
            // get callback and set layout
            $callback = $this->request->query['callback'];

            $this->set('callback', $callback);
            $this->set('data', $response);
            $this->layout = 'js/status';
        } else {
            // this is required so that CakePHP automatically presents the
            // response with Xml/JsonView
            $response['_serialize'] = array_keys($response);
            $this->set($response);
        }
    }

}









