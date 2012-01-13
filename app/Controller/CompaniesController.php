<?php

class CompaniesController extends AppController {

    public $name = 'Companies';

    function index() {

        $options['conditions'] = array('Company.is_enabled' => 1,
                                       'User.is_banned' => 0);
        $results = $this->Company->find('all', $options);

        $this->set('companies', $results);
    }
}
