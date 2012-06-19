<?php

class AdminsController extends AppController {

    public $name = 'Admins';
    public $uses = array('Company', 'Student');
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

    public function students($id = null) {

        // == force a redirect so as to display pretty options ==
        if (! empty($this->request->data)) {
            // basis of the redirection params
            $redirect = array('controller' => 'admins',
                              'action' => 'students');

            // keep current params but replace those found in POST data
            $redirect = array_merge($redirect,
                                    $this->request->named,
                                    $this->request->data);

            // do NOT preserve page (because results may be differect)
            if (isset($redirect['page'])) unset($redirect['page']);

            // remove empty filters from uri
            $redirect = array_filter($redirect);

            $this->redirect($redirect);
        }

        // POST data have been moved to uri as 'pretty' params
        $request = $this->request->named;

        // == create filter conditions ==
        // will be used to contain filtering options
        $options = array();

        if (isset($request['search'])) {

            $search = $request['search'];
            if (! empty($search)) {
                $search_keyword = "%{$request['search']}%";
                $or_clause = array();
                $or_clause[] = array('Student.name LIKE' => $search_keyword);
                $or_clause[] = array('User.username LIKE' => $search_keyword);
                $or_clause[] = array('User.email LIKE' => $search_keyword);
                $options['OR'] = &$or_clause;
            }
        }

        if (isset($request['banned'])) {

            $is_banned = $request['banned'] === '1';
            $options[] = array('User.is_banned' => $is_banned);
        }

        // == apply pagination options ==
        //TODO: place this somewhere else
        $pagination_limit = 10;

        // let's be lightweight - fetch only required attributes
        $fields = array('Student.id',
                        'Student.name',
                        'User.username',
                        'User.email',
                        'User.is_banned');

        // basic pagination options
        $pagination_options = array('limit' => $pagination_limit,
                                    'fields' => $fields,
                                    'recursive' => 0);

        // append filter conditions, if available
        if (! empty($options))
            $pagination_options['conditions'] = $options;

        $this->paginate = $pagination_options;

        $this->Student->Behaviors->attach('Containable');
        $this->Student->contain(array('User'));
        $data = $this->paginate('Student');

        // data to display
        $this->set('data', $data);
        // search options with which to fill-in the form
        $this->set('request', $request);
    }

    public function companies($id = null) {

        // == force a redirect so as to display pretty options ==
        if (! empty($this->request->data)) {
            // basis of the redirection params
            $redirect = array('controller' => 'admins',
                              'action' => 'companies');

            // keep current params but replace those found in POST data
            $redirect = array_merge($redirect,
                                    $this->request->named,
                                    $this->request->data);

            // do NOT preserve page (because results may be differect)
            if (isset($redirect['page'])) unset($redirect['page']);

            // remove empty filters from uri
            $redirect = array_filter($redirect);

            $this->redirect($redirect);
        }

        // POST data have been moved to uri as 'pretty' params
        $request = $this->request->named;

        // == create filter conditions ==
        // will be used to contain filtering options
        $options = array();

        if (isset($request['search'])) {

            $search = $request['search'];
            if (! empty($search)) {
                $search_keyword = "%{$request['search']}%";
                $or_clause = array();
                $or_clause[] = array('Company.name LIKE' => $search_keyword);
                $or_clause[] = array('User.username LIKE' => $search_keyword);
                $or_clause[] = array('User.email LIKE' => $search_keyword);
                $options['OR'] = &$or_clause;
            }
        }

        if (isset($request['banned'])) {

            $is_banned = $request['banned'] === '1';
            $options[] = array('User.is_banned' => $is_banned);
        }

        if (isset($request['enabled'])) {

            $is_enabled = $request['enabled'] === '1';
            $options[] = array('Company.is_enabled' => $is_enabled);
        }

        // == apply pagination options ==
        //TODO: place this somewhere else
        $pagination_limit = 10;

        // let's be lightweight - fetch only required attributes
        $fields = array('Company.id',
                        'Company.name',
                        'Company.is_enabled',
                        'User.username',
                        'User.email',
                        'User.is_banned');

        // basic pagination options
        $pagination_options = array('limit' => $pagination_limit,
                                    'fields' => &$fields,
                                    'recursive' => 0);

        // append filter conditions, if available
        if (! empty($options))
            $pagination_options['conditions'] = &$options;

        $this->paginate = $pagination_options;

        $this->Company->Behaviors->attach('Containable');
        $this->Company->contain(array('User'));
        $data = $this->paginate();

        // data to display
        $this->set('data', $data);
        // search options with which to fill-in the form
        $this->set('request', $request);
    }
}
