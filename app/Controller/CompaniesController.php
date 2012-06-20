<?php

class CompaniesController extends AppController {

    public $name = 'Companies';
    public $helpers = array('Html', 'Form');
    public $uses = array('Company', 'Offer', 'Municipality',
                         'User', 'Day', 'WorkHour', 'Image');

    public $components = array('Common');

    public function beforeFilter() {
        // this call should precede all actions that return data
        // (exceptions included)
        $this->api_initialize();

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

        // filter only enabled companies, but not for admin
        if ($this->Auth->user('role') !== ROLE_ADMIN) {
            $options['conditions'] += array('Company.is_enabled' => 1);
        }

        $options['recursive'] = 1;

        // ignore offers for the following `find'
        $this->Company->unbindModel(array('hasMany' => array('Offer')));

        // we need the company's working hours
        $this->Company->Behaviors->attach('Containable');
        $this->Company->contain(array('WorkHour'));

        $company = $this->Company->find('first', $options);
        if (empty($company))
            throw new NotFoundException('Η συγκεκριμένη επιχείρηση δεν
                                        βρέθηκε.');

        // format working hours
        $wh_tmp = array();
        foreach($company['WorkHour'] as $wh) {
            $new_elem['name'] = day($wh['day_id']);
            $wh['starting'] = $this->trim_time($wh['starting']);
            $wh['ending'] = $this->trim_time($wh['ending']);
            $new_elem['time'] = "{$wh['starting']} - {$wh['ending']}";
            $wh_tmp[] = $new_elem;
        }
        $company['WorkHour'] = $wh_tmp;

        $company_id = $company['Company']['id'];

        // spam offers will be visible only to owner company or admin users
        $shows_spam = $company['Company']['user_id'] == $this->Auth->user('id')
            || $this->Auth->user('role') === ROLE_ADMIN;

        // update the state of the offers of current company
        $this->Offer->update_state($company_id);
        // append offers of this company
        $company['Offer'] = $this->Offer->find_all($company_id, $shows_spam);

        // fetch only company images
        $conditions['conditions'] = array('Image.company_id' => $company_id, 'Image.offer_id' => null);
        $conditions['recursive'] = -1;
        $company['Image'] = $company_img = $this->Image->find('all', $conditions);
        $this->set('company', $company);
    }


    public function edit ($id = null) {

        if ($id == null) throw new BadRequestException();
        $this->set('municipalities',
                   $this->Municipality->find('list', array(
                                             'order' => 'Municipality.name ASC')
                                            ));

        $this->set('days', $this->Day->find('list'));

        $this->Company->Behaviors->attach('Containable');
        $this->Company->contain(array('User', 'Municipality', 'WorkHour'));
        $company = $this->Company->findById($id);

        if (empty($company)) throw new NotFoundException();

        $work_hours = array();
        foreach ($company['WorkHour'] as $v) {
            $day = $v['day_id'];
            $work_hours[$day]['starting'] = substr($v['starting'], 0, -3);
            $work_hours[$day]['ending'] = substr($v['ending'], 0, -3);
        }
        $company['WorkHour'] = $work_hours;
        $this->set('company', $company);

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

            $work_hours = array();
            if (isset($this->request->data['WorkHour']) && !empty($this->request->data['WorkHour'])) {
                $input_hours = $this->request->data['WorkHour'];
                for ($i = 1; $i < count($input_hours); $i++) {
                    if (! empty($input_hours[$i]['starting']) and
                        ! empty($input_hours[$i]['ending'])) {
                            $work_hours[] = array(
                                'day_id' => $i,
                                'company_id' => $id,
                                'starting' => $input_hours[$i]['starting'],
                                'ending' => $input_hours[$i]['ending']
                            );
                    }
                }
            }

            if (! empty($work_hours)) {
                if (!$this->WorkHour->saveAll($work_hours)) {
                    $error = true;
                }
            }

            $this->User->id = $company['Company']['user_id'];
            if (!$this->User->saveField('email', $this->request->data['User']['email']))
                $error = true;

            // ---------------------------------------------------------
            // use separate controller action to support multiple images
            // ---------------------------------------------------------
            //
            //$photos = $this->Image->process($this->request->data['Image'],
            //                         array('company_id' => $company['Company']['id']),
            //                         1, false);
            //if (!empty($photos) && !$this->Image->saveMany($photos))
            //    $error = true;

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

    public function enable($id = null, $view = null) {
        $this->alter($id, $view, true);
    }

    public function disable($id = null, $view = null) {
        $this->alter($id, $view, false);
    }

    public function alter($id = null, $view = null, $enable = true) {
        $referer = $this->referer();
        if ($id == null) throw new BadRequestException();

        $options['conditions'] = array('Company.id' => $id);
        $options['recursive'] = -1;
        $company = $this->Company->find('first', $options);
        if (empty($company))
            throw new NotFoundException('Η συγκεκριμένη επιχείρηση δε βρέθηκε.');

        $data = array('id' => $id, 'is_enabled' => $enable);

        $transaction = $this->Company->getDataSource();
        $transaction->begin();
        $error = false;
        $saved = $this->Company->save($data, false);
        if (!$saved)
            $error = true;

        if ($error) {
            $transaction->rollback();
            $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                'default', array('class' => Flash::Error));
        } else {
            $transaction->commit();
            $company_name = $company['Company']['name'];
            $success_message = ($enable)
                ?"Η επιχείρηση '{$company_name}' ενεργοποιήθηκε."
                :"Η επιχείρηση '{$company_name}' απενεργοποιήθηκε.";
            $this->Session->setFlash($success_message,
                'default', array('class' => Flash::Success));
        }

        $this->redirect($referer);
    }

    public function imageedit() {
        // Get company info
        $company_id = $this->Session->read('Auth.Company.id');
        if (is_null($company_id))
            throw new BadRequestException();
        $options['conditions'] = array('Company.id' => $company_id);
        $options['recursive'] = -1;
        $company = $this->Company->find('first', $options);

        // fetch only company images
        $conditions['conditions'] = array(
            'Image.company_id' => $company['Company']['id'],
            'Image.offer_id' => null);
        $conditions['recursive'] = -1;
        $company['Image'] = $company_img = $this->Image->find('all', $conditions);
        $this->set('company', $company);

        // bail with a flash if max images reached
        if ((int)$company['Company']['image_count'] >= MAX_COMPANY_IMAGES) {
            $this->Session->setFlash(
                'Έχετε φτάσει τον μέγιστο επιτρεπτό αρθμό εικόνων',
                'default',
                array('class' => Flash::Warning));
                return;
        }

        // create input element
        $new_elem = array();
        $new_elem['title'] = 'Image';
        $new_elem['options']['label'] = 'Προσθήκη εικόνας';
        $new_elem['options']['type'] = 'file';
        $input_elements[] = $new_elem;
        $this->set('input_elements', $input_elements);

        if (!empty($this->request->data)) {
            // check if user pressed upload without image
            if (empty($this->request->data['Image']['name']))
                $this->upload_error($id, 'empty');

            // check if image is uploaded
            if (!is_uploaded_file($this->request->data['Image']['tmp_name'])) {
                $this->upload_error($id, 'size');
            } else {
                $tmp_size = filesize($this->request->data['Image']['tmp_name']);
                if ($tmp_size > MAX_UPLOAD_SIZE)
                    $this->upload_error($id, 'size');
            }

            // check file type
            if (!$this->valid_type($this->data['Image']['tmp_name']))
                $this->upload_error($id, 'filetype');

            $photo = $this->Image->process($this->request->data['Image']);
            // add company_id
            $photo['company_id'] = $company_id;
            $photo['image_category'] = IMG_COMPANY;

            // try to save images
            //
            // TODO: do we really need trasnaction here? O_o
            //
            $transaction = $this->Image->getDataSource();
            $transaction->begin();
            $error = false;
            if (!empty($photo) && !$this->Image->save($photo))
                $error = true;
            if ($error) {
                $transaction->rollback();
                $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα',
                    'default', array('class' => Flash::Error));
            } else {
                $transaction->commit();
                $this->Session->setFlash('Η εικόνα προστέθηκε',
                    'default', array('class' => Flash::Success));
                $this->redirect(array(
                    'controller' => 'companies', 'action' => 'imageedit'));
            }
        }
    }

    public function email_confirm($token = null, $email = null) {
        $token_len = 40;
        $length = strlen($token);
        if ($length === $token_len) {
            $result = $this->User->email_confirm($token, $email);
            if ($result) {
                $msg = __('Η διεύθυνση ηλεκτρονικής αλληλογραφίας επικυρώθυκε.');
                $class = array('class' => Flash::Success);
                $http = 200;
            } else {
                $msg = __('Δεν ήταν δυνατή η επικύρωση της διεύθυνσης ηλεκτρονικής αλληλογραφίας.');
                $class = array('class' => Flash::Error);
                $http = 400;
            }
            $controller = 'offers';
            $action = 'index';
            $redirect = array('controller' => $controller, 'action' => $action);
            $redirect = array($redirect);
            $this->notify(array($msg, 'default', $class), $redirect, $http);
        }
    }

    public function is_authorized($user) {
        $public = array('view', 'email_confirm');
        $own = array('edit', 'imageedit');
        $admin_actions = array('enable','disable');

        if ($user['is_banned'] == 0) {
            // all users can view company views that are not banned
            if (in_array($this->action, $public)) {
                return true;
            }

            if (in_array($this->action, $own)) {
                if (isset($user['role']) && $user['role'] === ROLE_COMPANY) {
                    $company_id = $this->Session->read('Auth.Company.id');
                    if ($this->Company->is_owned_by($company_id, $user['id'])) {
                        return true;
                    }
                }
                // admin cannot edit company profiles
                return false;
            }

            if (in_array($this->action, $admin_actions)) {
                if (isset($user['role']) && $user['role'] === ROLE_ADMIN) {
                    return true;
                }
            }
        }

        // admin can see banned users too
        return parent::is_authorized($user);
    }

    private function valid_type($file) {
        // check if uploaded image has a valid filetype
        $valid_types = array('png', 'jpg', 'jpeg', 'gif');

        if (in_array($this->Common->upload_file_type($file), $valid_types)) {
            return true;
        }
        return false;
    }

    private function trim_time($time) {
        return substr($time, 0, -3);
    }

}
