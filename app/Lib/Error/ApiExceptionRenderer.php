<?php

App::uses('ExceptionRenderer', 'Error');

class ApiExceptionRenderer extends ExceptionRenderer {

    public function __construct(Exception $exception) {
        parent::__construct($exception);
    }

    // @Override
    // Extends the default behaviour of rendering exceptions when a call to the
    // webservice api was performed.
    public function render() {

        // TODO: what should do if `api_initialize throwed an exception
        $this->controller->api_initialize();

        if ($this->controller->is_webservice()) {

            $this->controller->api_compile_response(
                $this->error->getCode(),
                array('message' => $this->error->getMessage()));

            // `false' is given as template so as to impose the use of NO view
            // at all; required for rendering js errors
            $this->_outputMessage(false);
        } else {
            // if not a webservice api call, act as usual
            return parent::render();
        }
    }
}
