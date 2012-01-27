<?php

class CompaniesController extends AppController {

    public $name = 'Companies';
    public $helpers = array('Html');
    public $uses = array('Company', 'Offer');

    function index() {

        $options['conditions'] = array('Company.is_enabled' => 1,
                                       'User.is_banned' => 0);
        $options['recursive'] = 0;
        $results = $this->Company->find('all', $options);

        $this->set('companies', $results);
    }


    function view($id = null) {

        $options['conditions'] = array('Company.id' => $id,
                                       'Company.is_enabled' => 1,
                                       'User.is_banned' => 0);
        $options['recursive'] = 0;
        $company = $this->Company->find('first', $options);

        if (empty($company))
            throw new NotFoundException('Η συγκεκριμένη επιχείρηση δεν
                                        βρέθηκε.');

        // find the active offers of this company
        $active_options['conditions'] = array('Offer.company_id' => $id,
                                              'Offer.is_active' => 1);
        $active_options['fields'] = array('Offer.*');
        $active_options['order'] = array();
        $active_options['recrsive'] = 0;
        $offers_tmp = $this->Offer->find('all', $active_options);
        $offers['Offer']['Active'] = Set::extract('/Offer/.', $offers_tmp);

        // find the draft offers of this company
        $draft_options['conditions'] = array('Offer.company_id' => $id,
                                             'Offer.is_draft' => 1);
        $draft_options['fields'] = array('Offer.*');
        $draft_options['order'] = array('Offer.starting ASC');
        $draft_options['recursive'] = 0;
        $offers_tmp = $this->Offer->find('all', $draft_options);
        $offers['Offer']['Draft'] = Set::extract('/Offer/.', $offers_tmp);

        // find the inactive offers of this company
        $inactive_options['conditions'] = array('Offer.company_id' => $id,
                                                'Offer.is_draft' => 0,
                                                'Offer.is_active' => 0);
        $inactive_options['fields'] = array('Offer.*');
        $inactive_options['order'] = array('Offer.starting ASC');
        $inactive_options['recursive'] = 0;
        $offers_tmp = $this->Offer->find('all', $inactive_options);
        $offers['Offer']['Inactive'] = Set::extract('/Offer/.', $offers_tmp);

        $company = array_merge($company, $offers);
        $this->set('company', $company);
// pr($company); die();
    }
}
