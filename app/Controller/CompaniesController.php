<?php

class CompaniesController extends AppController {

    public $name = 'Companies';
    public $helpers = array('Html', 'Form');
    public $uses = array('Company', 'Offer', 'Municipality',
                         'User', 'Day', 'WorkHour', 'Image');

    public function beforeFilter() {
        if (! $this->is_authorized($this->Auth->user()))
            throw new ForbiddenException();

        parent::beforeFilter();
    }

    public function view($id = null) {
        // everyone can view a company by id
        $options['conditions'] = array('Company.id' => $id);

        // allow companies to view their own profile without id
        if ($this->Auth->user('role') === ROLE_COMPANY) {
            if ($id == null) {
                // view own profile
                $options['conditions'] = array(
                    'Company.user_id' => $this->Auth->user('id'));
            }
        }

        // filter only enabled companies
        $options['conditions'] += array('Company.is_enabled' => 1);
        $options['recursive'] = 1;

        // ignore offers for the following `find'
        $this->Company->unbindModel(array('hasMany' => array('Offer')));
        $company = $this->Company->find('first', $options);
        if (empty($company))
            throw new NotFoundException('Η συγκεκριμένη επιχείρηση δεν
                                        βρέθηκε.');

        $company_id = $company['Company']['id'];

        // spam offers will be visible only to owner company or admin users
        $shows_spam = $company['Company']['user_id'] == $this->Auth->user('id')
            || $this->Auth->user('role') === ROLE_ADMIN;

        // update the state of the offers of current company
        $this->Offer->update_state($company_id);
        // append offers of this company
        $company['Offer'] = $this->Offer->find_all($company_id, $shows_spam);

        $this->set('company', $company);
    }


    public function edit ($id = null) {

        if ($id == null) throw new BadRequestException();
        $this->set('municipalities',
                   $this->Municipality->find('list', array(
                                             'order' => 'Municipality.name ASC')
                                            ));

        $this->set('days', $this->Day->find('list'));

        $options['conditions'] = array('Company.id' => $id);
        $options['recursive'] = 1;
        $company = $this->Company->find('first', $options);
        $this->set('company', $company);

        if (empty($company)) throw new NotFoundException();

        if ($company['Company']['user_id'] !== $this->Auth->User('id'))
            throw new ForbiddenException();

        if (empty($this->request->data)) {
            $this->request->data = $company;
        } else {

            $transaction = $this->Company->getDataSource();
            $transaction->begin();
            $error = false;

            if (!$this->Company->save($this->request->data))
                $error = true;

            $del_opts['WorkHour.company_id'] = $this->request->data['Company']['id'];
            if (!$this->WorkHour->deleteAll($del_opts, true, true))
                $error = true;

            if (isset($this->request->data['WorkHour']) && !empty($this->request->data['WorkHour'])) {
                for ($i = 0; $i < count($this->request->data['WorkHour']); $i++)
                    $this->request->data['WorkHour'][$i]['company_id'] = $company['Company']['id'];

                if (!$this->WorkHour->saveAll($this->request->data['WorkHour']))
                    $error = true;
            }

            $this->User->id = $company['Company']['user_id'];
            if (!$this->User->saveField('email', $this->request->data['User']['email']))
                $error = true;

            $photos = $this->Image->process($this->request->data['Image'],
                                     array('company_id' => $company['Company']['id']),
                                     1, false);
            if (!empty($photos) && !$this->Image->saveMany($photos))
                $error = true;

            if ($error) {
                $transaction->rollback();
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                                         'default',
                                         array('class' => Flash::Error));
            } else {
                $transaction->commit();
                $this->Session->setFlash('Οι αλλαγές αποθηκεύτηκαν.',
                                         'default',
                                         array('class' => Flash::Success));
                $this->redirect(array(
                        'controller' => 'companies',
                        'action' => 'view',
                        $company['Company']['id']
                    ));
            }
        }
    }

    public function is_authorized($user) {
        if ($user['is_banned'] == 0) {
            // all users can view company views that are not banned
            if ($this->action === 'view') {
                return true;
            }
            // TODO
            // add check for action 'edit'
            // check if company belongs to the user
            // who requested the edit action
        }

        // admin can see banned users too
        return parent::is_authorized($user);
    }
}
