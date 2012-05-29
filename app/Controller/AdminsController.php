<?php

class AdminsController extends AppController {

    public $name = 'Admins';
    public $uses = array('Company');
    public $helpers = array('Html');

    public function beforeFilter() {

        if (! $this->is_authorized($this->Auth->user()))
            throw new ForbiddenException();

        parent::beforeFilter();
    }

    public function is_authorized($user) {

        // only admin may access this controller
        if (isset($user['role']) && $user['role'] === ROLE_ADMIN) {
            return true;
        }

        return false;
    }

    public function view($id = null) {

        //TODO: place this somewhere else
        $pagination_limit = 10;

        // let's be lightweight - fetch only required attributes
        $fields = array('Company.id',
                        'Company.name',
                        'Company.is_enabled');

        $this->paginate = array('limit' => $pagination_limit,
                                'fields' => $fields,
                                'recursive' => -1);

        $data = $this->paginate();

        $this->set('data', $data);
    }
}
