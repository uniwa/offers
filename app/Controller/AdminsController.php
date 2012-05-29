<?php

class AdminsController extends AppController {

    public $name = 'Admins';

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
}
