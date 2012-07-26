<?php

class CompaniesController extends AppController {

    public $name = 'Companies';
    public $helpers = array('Html', 'Form');
    public $uses = array('Company', 'Offer', 'Municipality',
                         'User', 'Day', 'WorkHour', 'Image');

    public $components = array('Common', 'Email');

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
        $this->Company->contain(array('WorkHour', 'Municipality'));

        $company = $this->Company->find('first', $options);
        if (empty($company))
            throw new NotFoundException('Η συγκεκριμένη επιχείρηση δεν
                                        βρέθηκε.');

        // set municipality string
        if (! empty($company['Municipality'])) {
            $company['Company']['municipality'] = $company['Municipality']['name'];
        }

        // format working hours
        $wh_tmp = array();
        foreach($company['WorkHour'] as $wh) {
            // clean array because we might have a 2nd date part
            // from the previous loop
            $new_elem = array();

            // use this new element to built the date data for the view
            $new_elem['name'] = day($wh['day_id']);
            $wh['starting1'] = $this->trim_time($wh['starting1']);
            $wh['ending1'] = $this->trim_time($wh['ending1']);
            $new_elem['time1'] = "{$wh['starting1']} - {$wh['ending1']}";

            // gracefully handle same time range
            if ($wh['starting2'] == $wh['ending2']) {
                $wh_tmp[] = $new_elem;
                continue;
            }

            // second date part
            $wh['starting2'] = $this->trim_time($wh['starting2']);
            $wh['ending2'] = $this->trim_time($wh['ending2']);
            $new_elem['time2'] = "{$wh['starting2']} - {$wh['ending2']}";
            $wh_tmp[] = $new_elem;
        }
        $company['WorkHour'] = $wh_tmp;

        $company_id = $company['Company']['id'];

        // spam offers will be visible only to owner company or admin users
        $shows_spam = $company['Company']['user_id'] == $this->Auth->user('id')
            || $this->Auth->user('role') === ROLE_ADMIN;

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
        $this->set('municipalities', $this->Municipality->getHierarchy());

        $this->set('days', $this->Day->find('list'));

        $this->Company->Behaviors->attach('Containable');
        $this->Company->contain(array('User', 'Municipality', 'WorkHour'));
        $company = $this->Company->findById($id);

        if (empty($company)) throw new NotFoundException();

        $work_hours = array();
        foreach ($company['WorkHour'] as $v) {
            $day = $v['day_id'];
            $work_hours[$day]['starting1'] = substr($v['starting1'], 0, -3);
            $work_hours[$day]['ending1'] = substr($v['ending1'], 0, -3);

            if ($v['starting2'] == $v['ending2']) {
                    continue;
            }

            $work_hours[$day]['starting2'] = substr($v['starting2'], 0, -3);
            $work_hours[$day]['ending2'] = substr($v['ending2'], 0, -3);
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
                for ($i = 1; $i <= count($input_hours); $i++) {
                    // if first part of work hours is empty bail
                    // as we don't care about the second part too
                    if (empty($input_hours[$i]['starting1']) and
                        empty($input_hours[$i]['ending1'])) {
                            continue;
                    }

                    // 2nd part not emmpty: store both 1st and 2nd
                    if (! empty($input_hours[$i]['starting2']) and
                        ! empty($input_hours[$i]['ending2'])) {

                        $work_hours[] = array(
                            'day_id' => $i,
                            'company_id' => $id,
                            'starting1' => $input_hours[$i]['starting1'],
                            'ending1' => $input_hours[$i]['ending1'],
                            'starting2' => $input_hours[$i]['starting2'],
                            'ending2' => $input_hours[$i]['ending2']
                        );
                    } else {
                        // 2nd part empty - store only 1st part
                        $work_hours[] = array(
                            'day_id' => $i,
                            'company_id' => $id,
                            'starting1' => $input_hours[$i]['starting1'],
                            'ending1' => $input_hours[$i]['ending1']
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
                                         array(),
                                         "error");
            } else {
                $transaction->commit();
                $this->Session->setFlash('Οι αλλαγές αποθηκεύτηκαν.',
                                         'default',
                                         array(),
                                        "success");
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

    public function ban($id = null) {
        $this->Company->recursive = 0;
        $company = $this->Company->findById($id);

        if ($company['User']['is_banned']){
            $flashmsg = _("Η επιχείρηση είναι ήδη κλειδωμένη.");
            $this->Session->setFlash($flashmsg,
                'default', array(), 'error');

            $this->redirect($this->referer());
        }

        $this->set('company', $company);

        if (!empty($this->request->data)) {
            $target = array('controller' => 'admins', 'action' => 'companies');
            $email = $company['User']['email'];

            if (isset($this->request->data['cancel'])) {
                $this->redirect($target);
            }

            $this->Company->set($this->request->data);
            $this->Company->validates();
            $errors = $this->Company->validationErrors;

            if (!isset($errors['explanation'])) {
                $this->change_company_state($id, true,
                    $this->request->data['Company']['explanation']);
                $this->company_ban_notification($company, $email);
                $this->redirect($target);
            }
        }
    }

    public function unban($id = null) {
        $this->change_company_state($id, false);
        $this->redirect($this->referer());
    }

    private function change_company_state($company_id, $ban = true, $expl = null) {
        //
        // change user field `is_banned`
        //
        if ($company_id == null) throw new BadRequestException();

        // we need user id and company id
        // so we search in Company table with user_id
        $options['conditions'] = array('Company.id' => $company_id);
        $options['recursive'] = 0;
        $company = $this->Company->find('first', $options);
        if (empty($company))
            throw new NotFoundException('Η συγκεκριμένη επιχείρηση δε βρέθηκε.');

        if ($company['User']['is_banned'] == $ban){
            $flashmsg = ($ban)
                ?_("Η επιχείρηση είναι ήδη κλειδωμένη.")
                :_("Η επιχείρηση δεν είναι κλειδωμένη.");
            $this->Session->setFlash($flashmsg,
                'default', array(), 'error');

            return false;
        }

        // we need user id and company id
        // so we search in Company table with user_id
        $options['conditions'] = array(
            'Offer.company_id' => $company_id,
            'Offer.offer_state_id' => STATE_ACTIVE);
        $options['recursive'] = -1;
        $offers = $this->Offer->find('all', $options);
        if (!empty($offers)) {
            // mark all as spam
            foreach ($offers as $offer) {
                $this->Offer->flag_improper($offer['Offer']['id'], $expl);
            }
        }

        $this->User->id = $company['Company']['user_id'];
        $this->Company->id = $company['Company']['id'];
        $saved = $this->User->saveField('is_banned', $ban, false);
        $saved = $this->Company->saveField('explanation', $expl, false);

        if (!$saved) {
            $this->Session->setFlash('Παρουσιάστηκε κάποιο σφάλμα.',
                'default', array(), 'error');
        } else {
            $company_name = $company['Company']['name'];
            $success_message = ($ban)
                ?"Η επιχείρηση '{$company_name}' κλειδώθηκε επιτυχώς."
                :"Η επιχείρηση '{$company_name}' ξεκλειδώθηκε επιτυχώς.";
            $this->Session->setFlash($success_message,
                'default', array(), 'success');
        }
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
                'default', array(), "error");
        } else {
            $transaction->commit();
            $company_name = $company['Company']['name'];
            $success_message = ($enable)
                ?"Η επιχείρηση '{$company_name}' ενεργοποιήθηκε."
                :"Η επιχείρηση '{$company_name}' απενεργοποιήθηκε.";
            $this->Session->setFlash($success_message, 'default', array(), "success");
        }

        $this->redirect($referer);
    }

    // Send email notification to company when they have been banned
    private function company_ban_notification ($company = null, $email = null) {
        if (is_null($company) || is_null($email)) {
            throw new BadRequestException();
        }

        $subject = __("Ειδοποίηση κλειδώματος λογαριασμού");
        $url = APP_URL."/companies/view";
        $name = $company['Company']['name'];
        $explanation = $company['Company']['explanation'];

        $cake_email = new CakeEmail('default');
        $cake_email = $cake_email
            ->to($email)
            ->subject($subject)
            ->template('ban_notify', 'default')
            ->emailFormat('both')
            ->viewVars(array('url' => $url,'name' => $name,'explanation' => $explanation));
        try {
            $cake_email->send();
        } catch (Exception $e) {
            return false;
        }
        return true;
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
                array(),
                "warning");
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
                    'default', array(), 'error');
            } else {
                $transaction->commit();
                $this->Session->setFlash('Η εικόνα προστέθηκε',
                    'default', array(), 'success');
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
                $msg = __('Η διεύθυνση ηλεκτρονικής αλληλογραφίας επικυρώθηκε.');
                $flash_type = "success";
                $http = 200;
                $this->new_company_notification($result);
            } else {
                $msg = __('Δεν ήταν δυνατή η επικύρωση της διεύθυνσης ηλεκτρονικής αλληλογραφίας.');
                $flash_type = "error";
                $http = 400;
            }
            $controller = 'offers';
            $action = 'index';
            $redirect = array('controller' => $controller, 'action' => $action);
            $redirect = array($redirect);
            $this->notify(array($msg, 'default', array(), $flash_type), $redirect, $http);
        }
    }

    private function new_company_notification ($id = null) {
        $email = ADMIN_EMAIL;
        $subject = __("Ειδοποίηση νέας εγγραφής επιχείρησης");
        $url = APP_URL."/companies/view/{$id}";
        $cake_email = new CakeEmail('default');
        $cake_email = $cake_email
            ->to($email)
            ->subject($subject)
            ->template('company_notify', 'default')
            ->emailFormat('both')
            ->viewVars(array('url' => $url));
        try {
            $cake_email->send();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    public function gsis_get($afm = null) {
        $url_gsis = "https://www1.gsis.gr/wsgsis/RgWsBasStoixN/RgWsBasStoixNSoapHttpPort?wsdl";
        $url_manual = "https://www1.gsis.gr/wsgsis/RgWsBasStoixN/RgWsBasStoixNSoapHttpPort";

        // set trace = 1 for debugging
        $client = new SoapClient($url_gsis, array('trace' => 0));
        // we set the location manually, since the one in the WSDL is wrong
        $client->__setLocation($url_manual);

        $pBasStoixNRec_out = array('actLongDescr' => '',
            'postalZipCode' => '',
            'facActivity' => 0,
            'registDate' => '2011-01-01',
            'stopDate' => '2011-01-01',
            'doyDescr' => '',
            'parDescription' => '',
            'deactivationFlag' => 1,
            'postalAddressNo' => '',
            'postalAddress' => '',
            'doy' => '',
            'firmPhone' => '',
            'onomasia' => '',
            'firmFax' => '',
            'afm' => '',
            'commerTitle' => '');

        $pCallSeqId_out = 0;
        $pErrorRec_out = array('errorDescr' => '', 'errorCode' => '');

        try {
            $result = $client->rgWsBasStoixN(
                $afm, $pBasStoixNRec_out, $pCallSeqId_out, $pErrorRec_out);

            if (!$result['pErrorRec_out']->errorCode)
            {
                $this->autoLayout = false;
                $this->autoRender = false;
                $data = json_encode($result['pBasStoixNRec_out']);

                return $data;
            } else {
                return false;
            }

        } catch(SoapFault $fault) {
            return false;
        }
    }

    // Displays a list of emails for all enabled company that belong to unbanned
    // users. Emails are imploded (glued) using commas (,).
    public function emails() {
        $delimiter = ',';

        $conditions = array(
            'User.is_banned' => false,
            'Company.is_enabled' => true
        );

        $this->Company->Behaviors->attach('Containable');
        $this->Company->contain('User');
        $result = $this->Company->find('all', array('fields' => array('User.email'),
                                                    'conditions' => $conditions ));

        $result = Set::classicExtract($result, '{n}.User.email');

        $emails = implode($delimiter, $result);
        $this->set('emails', $emails);

        $this->layout = false;
    }

    public function is_authorized($user) {
        $public = array('view', 'email_confirm', 'send_email_confirmation', 'gsis_get');
        $own = array('edit', 'imageedit');
        $admin_actions = array('enable','disable', 'ban', 'unban', 'emails');

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
