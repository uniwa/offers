<?php

class CompaniesController extends AppController {

    public $name = 'Companies';

    function index() {

        $results = $this->Company->find('all');

        $this->set('companies', $results);
//         pr($result);die();
    }
}
