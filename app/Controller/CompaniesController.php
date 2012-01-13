<?php

class CompaniesController extends AppController {

    public $name = 'Companies';


    function index() {

        $options['conditions'] = array('Company.is_enabled' => 1,
                                       'User.is_banned' => 0);
        $this->Company->recursive = 0;
        $results = $this->Company->find('all', $options);

        $this->set('companies', $results);
    }


    function view($id = null) {

        $options['conditions'] = array('Company.id' => $id,
                                       'Company.is_enabled' => 1,
                                       'User.is_banned' => 0);
        $this->Company->recursive = 1;
        $company = $this->Company->find('first', $options);

        $this->set('company', $company);

        if (empty($company))
            throw new NotFoundException('Η συγκεκριμένη επιχείρηση δεν
                                        βρέθηκε.');
// pr($company);die();
    }
}
