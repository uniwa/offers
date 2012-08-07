<?php

App::uses('CakeEmail', 'Network/Email');

class OffersController extends AppController {

    public $name = 'Offers';
    public $uses = array('Offer', 'Company', 'Image', 'WorkHour', 'Day',
        'Coupon', 'Student', 'Vote', 'Sanitize', 'Distance', 'StatsToday',
        'StatsTotal', 'Municipality');
    public $paginate = array(
//        'fields' => array('Offer.title', 'Offer.description'),
        'limit' => 6,
        'order'=>array(
            'Offer.starting' => 'desc'
        ),
        'recursive' => -1
    );

    public $order = array(
        'autoend' => array(
            'title' => 'ημ.λήξης',
            'value' => array(
                'Offer.autoend' => 'desc',
                'Offer.vote_sum' => 'desc',)),
        'recent' => array(
            'title' => 'πρόσφατα',
            'value' => array('Offer.modified' => 'desc')),
        'rank' => array(
            'title' => 'βαθμός',
            'value' => array(
                'Offer.vote_sum' => 'desc',
                'Offer.vote_count' => 'desc',)),
        'votes' => array(
            'title' => 'ψήφοι',
            'value' => array(
                'Offer.vote_count' => 'desc',
                'Offer.vote_sum' => 'desc')),
        'distance' => array(
            'title' => 'απόσταση',
            'value' => array('Distance.distance' => 'asc')));

    public $helpers = array('Html', 'Time', 'Text', 'Tag');

    public $components = array('Common', 'Email', 'RequestHandler');

    function beforeFilter(){
        // this call should precede all actions that return data (exceptions
        // included)
        $this->api_initialize();

        if (! $this->is_authorized($this->Auth->user()))
            throw new ForbiddenException('Δεν επιτρέπεται η πρόσβαση');

        parent::beforeFilter();
    }

    public function is_authorized($user) {
        $role = $this->Auth->user('role');
        $allow = array('index', 'category', 'view', 'happyhour', 'coupons',
            'limited', 'tag', 'search', 'statistics');
        $owner = array('edit', 'delete', 'imageedit', 'activate', 'terminate',
            'copy');
        $companies = array('add_happyhour', 'add_coupons', 'add_limited',
            'webservice_add');
        $ban = array_merge($companies, array('activate', 'copy'));
        $students = array('vote_up', 'vote_down');

        // All registered users can view offers
        if (in_array($this->action, $allow)) {
            return true;
        }

        // first check if company that owns the offer is banned
        if (in_array($this->action, $ban)) {
            if ($role == ROLE_COMPANY) {
                if ($this->Company->is_banned($this->Session->read('Auth.Company.id'))) {
                    // we don't want to return false here because the page
                    // will blow up in user's face with a 403
                    // just show a flash and redirect
                    $this->notify(
                        array(
                            'Έχετε κλειδωθεί από τον διαχειριστή σου συστήματος.
                            Αυτή η λειτουργία δεν επιτρέπεται.',
                            'default',
                            array(),
                            'warning'),
                        array($this->referer()),
                        403);
                }
            }
        }

        // The owner of an offer can edit and delete it, as well as activate and
        //  terminate it
        if (in_array($this->action, $owner)) {

            // no id may have been supplied in the url
            if (array_key_exists(0,$this->request->params['pass'])) {

                $offer_id = $this->request->params['pass'][0];
                if ($this->Offer->is_owned_by($offer_id, $user['id'])) {
                    return true;
                }
            }
            // prevent admins from gaining access
            return false;
        }

        // Only companies
        if (in_array($this->action, $companies)) {
            if ($role === ROLE_COMPANY) {
                return true;
            }
            // prevent admins from gaining access
            return false;
        }

        return parent::is_authorized($user);
    }

    public function index() {
        $params = array('valid');

        $this->Offer->Behaviors->attach('Containable');
        // WorkHour is required for the webservice api
        $this->Offer->contain(array('WorkHour', 'Company', 'OfferCategory'));

        $this->ordering($params);
        $this->display($params);
    }

    public function search($search = null) {
        $params = null;
        $contains = null;
        $munic_id = null;

        $is_post = $this->request->is('post');
        if ($is_post) {
            // get POST data
            $contains = $this->request->data('Offer.contains');
            $munic_id = $this->request->data('Offer.municipality');

        } else {
            // get named parameters
            $named = $this->request->params['named'];
            if (! empty($named)) {

                if (isset($named['contains']))
                    $contains = $named['contains'];

                if (isset($named['municipality']))
                    $munic_id = $named['municipality'];
            }
        }

        // ensure that no consecutive whitespaces exist after the replacement
        // because that would cause empty-strings to be passed as query params
        // which, in turn, would produce subqueries as LIKE '%%'

        $alphanum = mb_eregi_replace($this->pattern_simple, ' ', $contains);
        $alphanum = trim($alphanum);

        if ($alphanum != null) $params['contains'] = $alphanum;
        if ($munic_id != null) $params['municipality'] = intval($munic_id);

        // if no actual data were passed, redirect to index
        if (empty($params)) {
            $this->redirect(array('controller' => 'offers',
                                  'action' => 'index'));
        }

        // if the request was made through POST, force agent to repeat it as GET
        if ($is_post) {
            $this->redirect(array_merge(array('controller' => 'offers',
                                              'action' => 'search'),
                                        $params));
        }

        $this->set('search_string', $alphanum);
        $this->set('municipality_id', $munic_id);

        $params = array('search');
        if ($alphanum != null)
            $params['words'] = array_unique(explode(' ', $alphanum));

        if ($munic_id != null)
            $params['conditions'] = array('Company.municipality_id' => $munic_id);

        $this->ordering($params);
        $this->display($params);
    }

    public function happyhour() {
        $params = array('happyhour');
        $this->ordering($params);
        $this->display($params);
    }

    public function coupons() {
        $params = array('coupons');
        $this->ordering($params);
        $this->display($params);
    }

    public function limited() {
        $params = array('limited');
        if (!$this->RequestHandler->isRss())
            $params['orderby'] = 'autoend';
        $this->ordering($params);
        $this->display($params);
    }

    public function tag($tag) {
        $params = array('tag', 'tag' => $tag);
        $this->ordering($params);
        $this->display($params);
    }

    public function category($id) {
        // TODO throw exception if invalid/non-existent id
        $id = (int)$id; // Sanitize id input
        $conditions['Offer.offer_category_id'] = $id;
        $params = array('valid', 'conditions' => $conditions);
        $this->ordering($params);
        $this->display($params);
    }

    public function spam() {
        // make it easy to identify that spam is shown (so as to hide flag link)
        $this->set('shows_spam', true);

        $this->Offer->recursive = 0;
        $params = array('all',
                        // this goes through to the intrinsic _findAll()
                        'conditions' => array('Offer.is_spam' => true),
                        // this persists the options in custom process_find()
                        'show_spam' => true);
        $this->ordering($params);
        $this->display($params);
    }

    // Add ordering into params
    private function ordering(&$params) {
        $order_options = array_keys($this->order);
        $this->set('order_options', $this->order);

        if (isset($params['orderby']))
            $criterion = $params['orderby'];
        if (isset($this->params['named']['orderby']))
            $criterion = $this->params['named']['orderby'];
        if (isset($criterion)) {
            $valid_criterion = in_array($criterion, $order_options);
            if ($valid_criterion) {
                if (($this->params['action'] === 'limited') ||
                    ($criterion !== 'autoend'))
                    $params['order'] = $this->order[$criterion]['value'];
                if ($criterion === 'distance') {
                    $params['radius'] = $this->Session->read('Auth.User.radius');
                }

                return true;
            }

            return false;
        }
    }

    // Displays offers in list according to passed criteria and sorting params
    private function display($params) {
        $pagination_limit = 10;
        $params = array_merge($params, array('limit' => $pagination_limit));
        $this->paginate = $params;
        $offers = $this->paginate();
        $this->Offer->minify_desc($offers, 160);
        if ($this->is_webservice) {
            switch ($this->webservice_type) {
                case 'js':
                case 'json':
                    $data = $this->api_prepare_view($offers, false);
                    break;

                case 'xml':
                    $data = $this->api_prepare_view($offers);
                    break;
            }
            $pagination_info = $this->params['paging']['Offer'];
            $this->api_compile_response(
                200, array( 'pagination' => $pagination_info,
                            'offers' => $data['offers'],
                            'companies' => $data['companies']));

        } else {
            $this->set('municipalities', $this->Municipality->getHierarchy());
            $this->set('offer_categories',
                       $this->OfferCategory->find('countOffers'));
            $this->set('type_stats',
                       $this->Offer->find('typeStats'));
            $this->set('offers', $offers);
            $this->render('index');
        }
    }

    private function flag($id = null, $explanation = null) {
        $flash = $this->Offer->flag_improper($id, $explanation);
        $this->Session->setFlash($flash['msg'], 'default', array(), $flash['type']);
    }

    public function improper($id = null) {
        if (empty($id)) throw new BadRequestException();

        $this->Offer->recursive = -1;
        $offer = $this->Offer->findById($id);

        if ($offer == false) throw new NotFoundException();

        if ($offer['Offer']['offer_state_id'] == STATE_DRAFT) {
            $msg = 'Οι μη ενεργοποιημένες προσφορές δεν μπορούν να επισημανθούν ως ανάρμοστες';
            $flash_type = 'error';
        } else if ($offer['Offer']['is_spam']) {
            $msg = 'Η προσφορά έχει ήδη επισημανθεί ως ανάρμοστη';
            $flash_type = 'warning';
        }

        $this->set('offer', $offer);

        if (!empty($this->request->data)) {
            $target = array('controller' => 'offers', 'action' => 'index');
            if (isset($this->request->data['cancel'])) {
                $this->redirect($target);
            }
            $this->Offer->set($this->request->data);
            $this->Offer->validates();
            $errors = $this->Offer->validationErrors;
            if (!isset($errors['explanation'])){
                $this->flag($id, $this->request->data['Offer']['explanation']);

                $owner = $this->Offer->get_company_email($id);
                $students = $this->Offer->get_student_emails($id);
                $this->improper_offer_notification($offer, $owner, $students);
                $this->redirect($target);
            }
        }
    }

    public function view($id = null) {
        $options['conditions'] = array('Offer.id' => $id);

        $this_user_role = $this->Auth->User('role');
        $this_user_id = $this->Auth->User('id');

        // if role is admin, the offer is displayed no matter what
        if ($this_user_role != ROLE_ADMIN) {
            $options['conditions']['OR'] = array(
                // this allows owner of offer to always view it
                'Company.user_id' => $this_user_id,

                // these must apply for the rest of the members
                array(
                    'Offer.offer_state_id <>' => STATE_DRAFT,
                    'Offer.is_spam' => 0,
                    'Company.is_enabled' => 1
                )
            );
        }

        //TODO check if the company's user is_banned before showing the offer
        $options['recursive'] = 1;
        $offer = $this->Offer->find('first', $options);

        if (empty($offer)) {
            throw new NotFoundException('Η προσφορά δεν βρέθηκε');
        }

        $this->set('offer', $offer);
        $offer_type_id = $offer['Offer']['offer_type_id'];
        $is_user_the_owner = $this->Offer->is_owned_by($id, $this_user_id);

        // notify visitors to log-in to access more features for 'coupons' offers
        if ($offer_type_id == TYPE_COUPONS && $this_user_id === NULL) {
            $login_url = array(
                'controller' => 'users',
                'action' => 'login'
            );
            $flash_msg = '<a href="'.Router::url($login_url, true).'">Συνδεθείτε εδώ</a> για ';
            $flash_msg .= 'να μπορέσετε να δεσμεύσετε κουπόνια και να ψηφίσετε προσφορές!';
            $this->Session->setFlash($flash_msg, 'default', array(), 'info');
        }

        if (($this_user_role === ROLE_COMPANY) && $is_user_the_owner) {
            $visits = $this->get_offer_visits($id);
            $this->set('visits', $visits);
        }

        if ($this_user_role === ROLE_STUDENT) {
            // add visit data to StatsToday (only for logged in students)
            $data = array(
                'offer_id' => $id,
                'company_id' => $offer['Offer']['company_id'],
                'ip' => $this->request->clientIp(),
            );
            $this->StatsToday->add_visit($data);

            if ($offer_type_id == TYPE_COUPONS) {
                $student_id = $this->Session->read('Auth.Student.id');
                // negate result as this returns if max number of coupons is reached
                $coupons['enabled'] =
                    ! $this->Coupon->max_coupons_reached($id, $student_id);
                $coupons['coupons'] = $this->Coupon->student_coupons($id, $student_id);
                $this->set('coupons', $coupons);
            }
        }

        $this->set('is_user_the_owner', $is_user_the_owner);
        // get coupons for offer if user is owner or admin
        // and coupon is of type 'COUPONS'
        if ($is_user_the_owner && $offer_type_id == TYPE_COUPONS) {
                // build query
                $order = array('Coupon.created DESC');
                $conditions = array('Coupon.offer_id' => $id);

                $coupons = $this->Coupon->find('all', array(
                    'conditions' => $conditions,
                    'order' => $order));

                $this->set('coupons', $coupons);
        }

        // if user is logged in get vote status for this offer
        if ($this->Auth->User('id') != null) {
            if ($this_user_role === ROLE_STUDENT) {
                $conditions = array(
                    'Vote.student_id' => $this->Session->read('Auth.Student.id'),
                    'Vote.offer_id' => $id
                );

                $student_vote = $this->Vote->find('first', array(
                    'conditions' => $conditions,
                    'fields' => array('Vote.vote'),
                    'recursive' => -1)
                );

                if (is_null($student_vote['Vote']['vote'])) {
                    // Set this shit to -1 because php is retarded and
                    // cannot differentiate between null and set/not-set
                    // variables. So PHP, fuck you.
                    $student_vote = VOTE_CANCEL;
                } else {
                    $student_vote = $student_vote['Vote']['vote'];
                }
            }
        }


        if ($this->is_webservice) {
            switch ($this->webservice_type) {
                case 'js':
                case 'json':
                    $offer_info = $this->api_prepare_view($offer, false);
                    // set the student's vote type
                    if (isset($student_vote)) {
                        // use === to avoid typecast of -1 to true
                        if ($student_vote === VOTE_CANCEL) {
                            $offer_info['offer']['student_vote']['vote_type'] = null;
                        } else {
                            // be consistent with XML, true => 1 / false => 0
                            $offer_info['offer']['student_vote']['vote_type'] = $student_vote ? 1 : 0;
                        }

                        // designate if student can grap a coupon for this offer
                        //
                        //  "student_coupon": {
                        //      "enabled": 1
                        //  }
                        //  valid values: `0` and `1`
                        if (isset($coupons)) {
                            $offer_info['offer']['student_coupon']['enabled'] = $coupons['enabled'] ? 1 : 0;
                        }
                    }
                    break;

                case 'xml':
                    $offer_info = $this->api_prepare_view($offer);
                    if (isset($student_vote)) {
                        // use === to avoid typecast of -1 to true
                        if ($student_vote === VOTE_CANCEL) $student_vote = null;
                        $offer_info['offer'][0]['student_vote']['vote_type'] = $student_vote;

                        // designate if student can grap a coupon for this offer
                        //
                        //  "student_coupon": {
                        //      "enabled": 1
                        //  }
                        //  valid values: `0` and `1`
                        if (isset($coupons)) {
                            $offer_info['offer'][0]['student_coupon'] = $coupons;
                        }

                    }
                    break;
            }

            $this->api_compile_response(200, array(
                'offer' => $offer_info['offer'],
                'company' => $offer_info['company']));

        } else {

            // alert of spam offer
            $is_spam = $offer['Offer']['is_spam'];
            // but do NOT impose this alert (in case another flash is already
            // set)
            $should_show = $this->Session->read('Message.flash') == null;
            $msg = _("Η προσφορά έχει σημανθεί ως ανάρμοστη από διαχειριστή τους συστήματος");
            if ($is_spam && $should_show) {
                $this->Session->setFlash($msg, 'default', array(), 'error');
            }

            // Prepare information for view
            $offer_info = $this->prepare_view($offer);
            $this->set('offer_info', $offer_info);

            // display vote only if logged-in user is a student
            if ($this_user_role === ROLE_STUDENT) {
                // don't query if we already have student's vote
                if (isset($student_vote)) {
                    if ($student_vote === VOTE_CANCEL) {
                        $student_vote = null;
                    }
                } else {
                    $student_id = $this->Session->read('Auth.Student.id');
                    $options['conditions'] = array(
                        'Vote.offer_id' => $id,
                        'Vote.student_id' => $student_id);
                    $options['recursive'] = -1;
                    $vote = $this->Vote->find('first', $options);
                    $student_vote = $vote['Vote']['vote'];
                }
            } else {
                $student_vote = null;
            }

            $this->set('student_vote', $student_vote);

            // whether to create the flag (as spam) link
            // note that drafts must be excluded
            $can_user_flag = $this_user_role == ROLE_ADMIN;
            $is_in_state = $offer['Offer']['offer_state_id'] != STATE_DRAFT;
            $is_not_flagged = ! $offer['Offer']['is_spam'];
            $is_flaggable = $can_user_flag && $is_in_state && $is_not_flagged;
            $this->set('is_flaggable', $is_flaggable);
        }
    }

    public function statistics() {

        if (! $this->is_webservice) throw new NotFoundException();

        $type_stats = $this->Offer->find('typeStats');
        $cat_stats = $this->OfferCategory->find('countOffers');

        // conditions array is only set to avoid careless merge in process_find
        // when no true conditions exist
        $total = $this->Offer->find('count', array('conditions' => array()));

        if ($this->Auth->user()) {
            if ($this->Session->read('Auth.User.role') == ROLE_STUDENT) {
                $student_id = $this->Session->read('Auth.Student.id');
                $options = array('conditions' => array(
                        'student_id' => $student_id));

                $coupon_count = $this->Coupon->find('count', $options);

                $vote_count = $this->Vote->find('count', $options);
            }
        } else {
            $coupon_count = null;
            $vote_count = null;
        }
        $my_stats = array('coupon_count' => $coupon_count,
                          'vote_count' => $vote_count);

        // provide simple wrapper elements for xml
        if ($this->webservice_type == 'xml') {
            $type_stats = array('type' => $type_stats);
            $cat_stats = array('category' => $cat_stats);
        }

        $this->notify(null, null, 200, array('total_offers' => $total,
                                             'types' => $type_stats,
                                             'categories' => $cat_stats,
                                             'my_stats' => $my_stats));
    }

    private function prepare_view($offer) {
        $offer_type_id = $offer['Offer']['offer_type_id'];
        $offer_info = array();
        $new_elem = array();
        // hide title - we don't need it for the loop because we
        // show it first in the view
        //$new_elem['label'] = "Τίτλος";
        //$new_elem['value'] = $offer['Offer']['title'];
        //$offer_info[] = $new_elem;
        $new_elem['label'] = "Περιγραφή";
        $new_elem['value'] = $offer['Offer']['description'];
        $offer_info[] = $new_elem;
        $new_elem['label'] = "Κατηγορία";
        $new_elem['value'] = $offer['OfferCategory']['name'];
        $offer_info[] = $new_elem;
        $vote_plus = $offer['Offer']['vote_plus'];
        $vote_minus = $offer['Offer']['vote_minus'];
        $vote_count = $offer['Offer']['vote_count'];
        $votes = "<span class='votes green'>+{$vote_plus}</span> ";
        $votes .= "<span class='votes red'>-{$vote_minus}</span> ";
        $votes .= "({$vote_count})";
        $new_elem['label'] = "Ψήφοι";
        $new_elem['value'] = $votes;
        $offer_info[] = $new_elem;
        $new_elem['label'] = "Λέξεις-κλειδιά";
        $new_elem['value'] = $offer['Offer']['tags'];
        $offer_info['tags'] = $new_elem;
        if (($offer_type_id == TYPE_COUPONS) &&
            $offer['Offer']['coupon_count'] >= 0) {
            $new_elem['label'] = "Δεσμευμένα κουπόνια";
            $new_elem['value'] = $offer['Offer']['coupon_count'].
                ' από '.$offer['Offer']['total_quantity'];
            $offer_info[] = $new_elem;
            $max = $offer['Offer']['max_per_student'];
            $new_elem['label'] = "Μέγιστος αριθμός κουπονιών ανά σπουδαστή";
            $new_elem['value'] =
                ($max == BIND_UNLIMITED)?'απεριόριστα':$max;
            $offer_info[] = $new_elem;
            $new_elem['label'] = "Όροι εξαργύρωσης κουπονιού";
            $new_elem['value'] = empty($offer['Offer']['coupon_terms'])? '-' :
                $offer['Offer']['coupon_terms'];
            $offer_info[] = $new_elem;
        }
        if ($offer_type_id == TYPE_LIMITED) {
            App::uses('CakeTime', 'Utility');
            $new_elem['label'] = "Λήξη προσφοράς";
            $new_elem['value'] = CakeTime::format('d-m-Y H:i',
                $offer['Offer']['autoend']);
            $offer_info[] = $new_elem;
        }

        // use the following $offer_info schema to support 2-part working hours
        //
        //  array(
        //      (int) 0 => array(
        //          'label' => 'Τίτλος',
        //          'value' => 'foobar'
        //      ),
        //      'work_hour' => array(
        //            'label' => 'Ώρες προσφοράς',
        //            'value' => array(
        //                (int) 0 => array(
        //                    'label' => 'Δευτέρα',
        //                    'value1' => '00:30 - 05:30',
        //                    'value2' => '07:30 - 09:00'
        //                ),
        //                (int) 1 => array(
        //                    'label' => 'Τρίτη',
        //                    'value1' => '03:00 - 06:30'
        //                )
        //            )
        //        )
        //  )

        if (! empty($offer['WorkHour'])) {
            $offer_info['work_hour']['label'] = 'Ώρες προσφοράς';
        }
        foreach($offer['WorkHour'] as $wh) {
            $new_elem = array();
            $new_elem['label'] = day($wh['day_id']);
            $wh['starting1'] = $this->trim_time($wh['starting1']);
            $wh['ending1'] = $this->trim_time($wh['ending1']);
            $new_elem['value1'] = "{$wh['starting1']} - {$wh['ending1']}";

            if ($wh['starting2'] == $wh['ending2']) {
                // separate work hour array from the rest view data
                $offer_info['work_hour']['value'][] = $new_elem;
                continue;
            }

            // second date part
            $wh['starting2'] = $this->trim_time($wh['starting2']);
            $wh['ending2'] = $this->trim_time($wh['ending2']);
            $new_elem['value2'] = "{$wh['starting2']} - {$wh['ending2']}";

            // separate work hour array from the rest view data
            $offer_info['work_hour']['value'][] = $new_elem;
        }
        //debug($offer_info);die();
        return $offer_info;
    }

    // Wrapper functions for 'add offer' action
    public function add_happyhour() {
        $this->modify(TYPE_HAPPYHOUR, OFFER_ADD);
    }

    public function add_coupons() {
        $this->modify(TYPE_COUPONS, OFFER_ADD);
    }

    public function add_limited() {
        $this->modify(TYPE_LIMITED, OFFER_ADD);
    }

    // The purpose of this function is to replace the wrapper functions add_*,
    // but this time for the webservice api. All it does is to call `modify'
    // with the appropriate parameters (offer_type_id, and id == OFFER_ADD) so the
    // creation of a new offer may take place.
    public function webservice_add($param = null) {

        $is_supported = $this->RequestHandler->prefers(array('xml','json', 'js'));

        // this function is available to webservice api calls only
        if ($is_supported) {

            $request = $this->request->data;

            if (!empty($request)) {

                // remove wrapping
                // later on, xsd-compliance checks must also be performed
                $data = reset($request);

                if (Set::check($data, 'Offer.offer_type_id')) {
                    $this->modify($data['Offer']['offer_type_id'], OFFER_ADD);
                    return;
                }
            }
        }

        throw new BadRequestException(
            'Η δομή του αιτήματος δεν είναι η αναμενόμενη');
    }

    // Wrapper function for 'edit offer' action
    public function edit($id = null) {
        $this->modify(null, $id);
    }

    // Wrapper function for 'clone offer' action
    public function copy($id = null) {
        $this->modify(OFFER_COPY, $id);
    }

    // Function for adding/editing offer
    // $offer_type_id same as global, 1-happy hour, 2-coupons, 3-limited
    // if $id is -1, add a new offer
    // else edit the offer with the corresponding id
    private function modify($offer_type_id, $id = null) {
        $is_copy = $offer_type_id === OFFER_COPY;
        $is_add = $id === OFFER_ADD;

        if (is_null($id)) {
            throw new BadRequestException(
                'Δεν έχει προσδιοριστεί το id της προσφοράς');
        }

        // special treatment for xml
        $is_xml = $this->RequestHandler->requestedWith('xml');

        // Save modified offer
        if (!empty($this->request->data)) {
            // first check if user wants to cancel the offer add action
            if (isset($this->request->data['cancel'])) {
                $this->redirect(array(
                    'controller' => 'companies', 'action' => 'view'));
            }

            // extract data from XML request
            if ($is_xml) {
                // remove unnecessary root (wrapping) element
                $request_data = $this->request->data;
                $this->request->data = reset($request_data);
            }

            // avoid blindly accepting values for all properties
            $this->filter_fields($this->request->data);

            // Avoid changing type through update:
            // EDIT, CLONE: type_id must be consistent; get from trustworthy db
            // ADD: type_id *is* valid anyway
            if (! $is_add) {
                $offer_type_id = $this->Offer->field(
                                           'offer_type_id', array('id' => $id));
            }

            $this->request->data['Offer']['offer_type_id'] = $offer_type_id;

            $this->set_default_values($this->request->data);

            // find the id of the Company related to the logged user
            // and assign it to Offer.company_id
            $options['fields'] = array('Company.id');
            $options['conditions'] = array(
                'Company.user_id' => $this->Auth->User('id')
            );
            $options['recursive'] = -1;
            $company = $this->Company->find('first', $options);
            $this->request->data['Offer']['company_id'] = $company['Company']['id'];

            // Leave id null for copy and add
            // NOTE: request->data is altered so that saveAssociated() may be
            // used when saving HappyHour offer
            if ($is_add || $is_copy) {
                $this->request->data['Offer']['id'] = null;
            } else {
                $this->request->data['Offer']['id'] = $id;
            }

            $error = false;

            if ($offer_type_id == TYPE_HAPPYHOUR) {
                $error = $this->save_happy_offer($id, $is_add, $is_copy) === false;
            } else {
                // limited and coupon do not require special treatment
                $error = !$this->Offer->save($this->request->data);
            }

            if ($error) {

                $this->notify(
                    array(  'Παρουσιάστηκε κάποιο σφάλμα',
                            'default',
                            array(),
                            "error"),
                    null, 400, $this->Offer->validationErrors);
            } else {

                $this->notify(
                    // the message to appear (parameters of `setFlash')
                    array(  'Η προσφορά αποθηκεύτηκε',
                            'default',
                            array(),
                            'success'),
                    // parameters of `redirect' (in case of html response)
                    array(  array(  'controller' => 'companies',
                                    'action' => 'view',
                                    $company['Company']['id'])),
                    // status of response (in case of webservice api call)
                    200,
                    // additional info (in case of webservice api call)
                    array(  'id' => $this->Offer->id));
            }
        } else {

            if ($this->is_webservice) {
                // if data is empty, webservice call should be terminated
                return $this->notify(
                    'Η δομή του αιτήματος δεν είναι η αναμενόμενη', null, 400);
            }

            // Add/edit offer
            if (! $is_add) {
                // Edit existing offer
                $options['conditions'] = array('Offer.id' => $id);
                $options['recursive'] = 0;
                $offer = $this->Offer->find('first', $options);

                if (empty($offer)) throw new NotFoundException();

                if ($offer['Company']['user_id'] != $this->Auth->User('id'))
                    throw new ForbiddenException();

                // Deny edit for non-draft offer
                // Allow copy
                if ($offer['Offer']['offer_state_id'] != STATE_DRAFT)
                    if(! $is_copy)
                        throw new ForbiddenException();

                // Unset autostart & autoend for copy
                if($is_copy) {
                    unset($offer['Offer']['autostart']);
                    unset($offer['Offer']['autoend']);
                }

                // Set offer type
                $offer_type_id = $offer['Offer']['offer_type_id'];

                // required to fill the select boxes with the correct values
                $this->set('work_hour_count', $offer['Offer']['work_hour_count']);

                // find the work_hours of this offer and put them in $offer variable
                if ($offer['Offer']['work_hour_count'] > 0) {
                    $wh_opts['conditions'] = array('WorkHour.offer_id' => $offer['Offer']['id']);
                    $wh_opts['recursive'] = -1;
                    $offer['WorkHour'] = Set::extract(
                        '/WorkHour/.', $this->WorkHour->find('all', $wh_opts));

                    // populate all 7 days of the week for view input
                    $fill_keys = array('starting1','ending1', 'starting2', 'ending2', 'offer_id');
                    $fill_values = array('', '', '', '', $offer['Offer']['id']);
                    $fill_day = array_combine($fill_keys, $fill_values);
                    $fill_week = array_fill(1, 7, $fill_day);

                    // trim ':00' seconds from time and update day
                    foreach ($offer['WorkHour'] as $k => $wh) {
                        $offer['WorkHour'][$k]['starting1'] = $this->trim_time($wh['starting1']);
                        $offer['WorkHour'][$k]['ending1'] = $this->trim_time($wh['ending1']);

                        if ($offer['WorkHour'][$k]['starting2'] == $offer['WorkHour'][$k]['ending2']) {
                            $fill_week[$wh['day_id']] = $offer['WorkHour'][$k];
                            continue;
                        }

                        $offer['WorkHour'][$k]['starting2'] = $this->trim_time($wh['starting2']);
                        $offer['WorkHour'][$k]['ending2'] = $this->trim_time($wh['ending2']);
                        $fill_week[$wh['day_id']] = $offer['WorkHour'][$k];
                    }
                    $offer['WorkHour'] = $fill_week;
                }
                $this->request->data = $offer;
            }
            $this->request->data['Offer']['offer_type_id'] = $offer_type_id;
        }

        // Required to fill the select boxes with the correct values
        $this->set('offerCategories', $this->Offer->OfferCategory->find('list'));
        $this->set('days', $this->Day->find('list'));

        $input_elements = $this->prepare_edit_view($offer_type_id);
        $this->set('input_elements', $input_elements);
        // Work hours for happy hour
        if ($offer_type_id == TYPE_HAPPYHOUR) {
            $work_hours = array(
                'work_hour_count' => 0,
                'timeFormat' => 24,
                //'interval' => 15 //default
                'header' => array('Ημέρα', 'Ώρα Έναρξης', 'Ώρα Λήξης', 'Επιλογή'),
                'table_class' => 'table table-striped',
                //'input_class' => span3, //default
                //'input_label' => null, //default
                //'staring_time_label' => null, //default
                //'ending_time_label' => null, //default
            );
            $this->set('work_hours', $work_hours);
        }
        $this->render('edit');
    }

    private function save_happy_offer($offer_id, $is_add, $is_copy) {
        $success = true;

        $work_hours = array();
        if (isset($this->request->data['WorkHour']) &&
            !empty($this->request->data['WorkHour'])) {
            $input_hours = $this->request->data['WorkHour'];
            for ($i = 1; $i <= count($input_hours); $i++) {

                if (empty($input_hours[$i]['starting1']) and
                    empty($input_hours[$i]['ending1'])) {
                        continue;
                }

                // 2nd part not emmpty: store both 1st and 2nd
                if (! empty($input_hours[$i]['starting2']) and
                    ! empty($input_hours[$i]['ending2'])) {

                    $work_hours[] = array(
                        'day_id' => $i,
                        'starting1' => $input_hours[$i]['starting1'],
                        'ending1' => $input_hours[$i]['ending1'],
                        'starting2' => $input_hours[$i]['starting2'],
                        'ending2' => $input_hours[$i]['ending2']
                    );
                } else {
                    // 2nd part empty - store only 1st part
                    $work_hours[] = array(
                        'day_id' => $i,
                        'starting1' => $input_hours[$i]['starting1'],
                        'ending1' => $input_hours[$i]['ending1']
                    );
                }

/*
                if (!empty($input_hours[$i]['starting']) &&
                    !empty($input_hours[$i]['ending'])) {
                    $h0 = $this->get_time($input_hours[$i]['starting']);
                    $h1 = $this->get_time($input_hours[$i]['ending']);
                    $work_hours[] = array(
                        // 'offer_id' => $this->Offer->id, // auto-set
                        'day_id' => ''.$i,
                        'starting' => $h0,
                        'ending' => $h1);
                    }*/
            }
        }

        // do validation early-on to avoid altering request->data[WorkHour], if
        // validation is going to fail. This way, the hour values the user has
        // set will be available to be redisplayed on the edit form
        $this->Offer->set($this->request->data);
        $errors = $this->Offer->invalidFields();
        if (! empty($errors)) $success = false;


        // set manual error for WorkHours
        if (empty($work_hours)) {
            $this->WorkHour->invalidate(
                        'error',
                        'Συμπληρώστε τουλάχιστον ένα ζεύγος ωρών.');

            $success = false;
        }

        if ($success) {
            // getting in here means that all fields validate and that WorkHours
            // are properly set and validated, as well

            $transaction = $this->Offer->getDataSource();
            $transaction->begin();

            // remove previous work hours, if any
            if (! $is_add && ! $is_copy) {
                if (! $this->WorkHour->deleteAll(
                    array('offer_id' => $offer_id), false)) {

                        $success = false;
                }
            }

            if ($success) {

                $this->request->data['WorkHour'] = $work_hours;
                $success = $this->Offer->saveAssociated(
                                                    $this->request->data,
                                                    array('validate' => false));
            }

            if ($success) {
                $transaction->commit();
            } else {
                $transaction->rollback();
            }
        }

        return $success;
    }

    private function prepare_edit_view($offer_type_id) {
        // Common elements for all offer types
        $input_elements = array();

        $new_elem = array();
        $new_elem['title'] = 'Offer.offer_type_id';
        $new_elem['options']['type'] = 'hidden';
        $input_elements[] = $new_elem;

        $new_elem = array();
        $new_elem['title'] = 'Offer.title';
        $new_elem['options']['label'] = 'Τίτλος';
        $new_elem['options']['type'] = 'text';
        $input_elements[] = $new_elem;

        $new_elem = array();
        $new_elem['title'] = 'Offer.description';
        $new_elem['options']['label'] = 'Περιγραφή';
        $new_elem['options']['type'] = 'textarea';
        $input_elements[] = $new_elem;

        $new_elem = array();
        $new_elem['title'] = 'Offer.tags';
        $new_elem['options']['label'] = 'Λέξεις-κλειδιά';
        $new_elem['options']['type'] = 'text';
        $input_elements[] = $new_elem;

        $new_elem = array();
        $new_elem['title'] = 'Offer.offer_category_id';
        $new_elem['options']['label'] = 'Κατηγορία προσφοράς';
        $input_elements[] = $new_elem;

        // Coupons
        if ($offer_type_id == TYPE_COUPONS) {
            $new_elem = array();
            $new_elem['title'] = 'Offer.total_quantity';
            $new_elem['options']['label'] = 'Αριθμός διαθέσιμων κουπονιών';
            $new_elem['options']['empty'] = 'Δεν έχει κουπόνια';
            $input_elements[] = $new_elem;

            $max_options = array(
                BIND_UNLIMITED => 'απεριόριστα', 1 => '1', 2 => '2',
                3 => '3', 5 => '5', 10 => '10');
            $new_elem = array();
            $new_elem['title'] = 'Offer.max_per_student';
            $new_elem['options']['label'] = "Μέγιστος αριθμός κουπονιών ανά φοιτητή<br />";
            $new_elem['options']['type'] = 'select';
            $new_elem['options']['options'] = $max_options;
            $input_elements[] = $new_elem;

            $new_elem = array();
            $new_elem['title'] = 'Offer.coupon_terms';
            $new_elem['options']['label'] = 'Όροι εξαργύρωσης κουπονιού';
            $new_elem['options']['type'] = 'text';
            $input_elements[] = $new_elem;
        }

        if (in_array($offer_type_id, array(TYPE_COUPONS, TYPE_LIMITED))) {
            $new_elem = array();
            $new_elem['title'] = 'Offer.autostart';
            $new_elem['options']['label'] = 'Ημ/νία & ώρα έναρξης προσφοράς';
            $new_elem['options']['separator'] = ' ';
            $new_elem['options']['dateFormat'] = 'DMY';
            $new_elem['options']['minYear'] = date('Y');
            $new_elem['options']['maxYear'] = date('Y') + 1;
            $new_elem['options']['orderYear'] = 'asc';
            $new_elem['options']['timeFormat'] = '24';
            $new_elem['options']['interval'] = '15';
            $new_elem['options']['empty'] = true;
            $new_elem['options']['class'] = 'span2';
            $input_elements[] = $new_elem;
        }

        // Limited
        if ($offer_type_id == TYPE_LIMITED) {
            $new_elem = array();
            $new_elem['title'] = 'Offer.autoend';
            $new_elem['options']['label'] = 'Ημ/νία & ώρα λήξης προσφοράς';
            $new_elem['options']['separator'] = ' ';
            $new_elem['options']['dateFormat'] = 'DMY';
            $new_elem['options']['minYear'] = date('Y');
            $new_elem['options']['maxYear'] = date('Y') + 1;
            $new_elem['options']['orderYear'] = 'asc';
            $new_elem['options']['timeFormat'] = '24';
            $new_elem['options']['interval'] = '15';
            $new_elem['options']['empty'] = true;
            $new_elem['options']['class'] = 'span2';
            $input_elements[] = $new_elem;
        }

        return $input_elements;
    }

    // Unsets properties that should not be present in create or update
    // requests.
    //
    // @param data to filter; typically, $this->request->data
    private function filter_fields(&$data) {
        $offer = array(
            'id', 'started', 'ended', 'coupon_count', 'image_count', 'is_spam',
            'work_hour_count', 'offer_state_id', 'created', 'modified');

        foreach ($offer as $property) {
            if (array_key_exists($property, $data['Offer'])) {
                unset($data['Offer'][$property]);
            }
        }
    }

    // Makes necessary initializations such as default values for all offers
    // and dummy values for properties that should not be required for the
    // current offer).
    //
    // @param $d data to alter; typically, $this->request->data
    private function set_default_values(&$d) {

        // make alterations to non-coupon offers so that they pass validation of
        // coupon properties
        if ($d['Offer']['offer_type_id'] != TYPE_COUPONS) {

            $d['Offer']['total_quantity'] = 1;
            $d['Offer']['max_per_student'] = '0';
        }

        // set the required default values
        $d['Offer']['current_quantity'] = 0;
        $d['Offer']['offer_state_id'] = STATE_DRAFT;
    }

    // Images administration
    public function imageedit($id = null) {
        if (is_null($id))
            throw new BadRequestException();

        // Get offer
        $options['conditions'] = array('Offer.id' => $id);
        $options['recursive'] = 1;
        $offer = $this->Offer->find('first', $options);

        if (empty($offer)) throw new NotFoundException();

        if ($offer['Company']['user_id'] != $this->Auth->User('id'))
            throw new ForbiddenException();

        if ($offer['Offer']['offer_state_id'] != STATE_DRAFT)
            throw new ForbiddenException();

        $this->set('offer', $offer);

        // bail with a flash if max images reached
        if ((int)$offer['Offer']['image_count'] >= MAX_OFFER_IMAGES) {
            $this->Session->setFlash(
                'Έχετε φτάσει τον μέγιστο επιτρεπτό αρθμό εικόνων',
                'default',
                array(),
                'warning');
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

            $photo = $this->Image->process($this->request->data['Image'],
                array('offer_id' => $id));
            // add company_id
            $company_id = $this->Session->read('Auth.Company.id');
            $photo['company_id'] = $company_id;
            $photo['image_category'] = IMG_OFFER;

            // try to save images
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
                    'controller' => 'offers', 'action' => 'imageedit', $id));
            }
        }
    }

    private function upload_error($id, $error) {
        switch ($error) {
            case 'empty':
                $error_msg = 'Παρακαλώ επιλέξτε εικόνα';
                break;
            case 'size':
                $error_msg = 'Υπερβολικά μεγάλο μέγεθος εικόνας, η εικόνα δεν αποθηκεύτηκε';
                break;
            case 'filetype':
                $error_msg = 'Επιτρέπονται μόνο αρχεία PNG, GIF και JPG';
                break;
            default:
                return false;
        }

        $this->Session->setFlash($error_msg,
            'default', array(), 'error');
        $this->redirect(array(
            'controller' => 'offers', 'action' => 'imageedit', $id));
    }

    private function valid_type($file) {
        // check if uploaded image has a valid filetype
        $valid_types = array('png', 'jpg', 'jpeg', 'gif');

        if (in_array($this->Common->upload_file_type($file), $valid_types)) {
            return true;
        }
        return false;
    }

    // Wrapper function of `_change_state' for the activation of an offer.
    public function activate($id = null) {
        if (is_null($id)) {
            throw new BadRequestException(
                'Δεν έχει προσδιοριστεί το id της προσφοράς');
        }

        return $this->_change_state($id, false);
    }

    // Wrapper function of `_change_state' for the termination of an offer.
    public function terminate($id = null) {
        if (is_null($id)) {
            throw new BadRequestException(
                'Δεν έχει προσδιοριστεί το id της προσφοράς');
        }

        return $this->_change_state($id, true);
    }

    // Responsible for manipulating the state of an offer. After execution,
    // redirects back to the referer.
    //
    // @param $id the offer to activate/terminate
    // @param $should_terminate determines if offer should be activated or
    //      terminated; defaults to false, resulting in its activation
    // @throws ForbiddenException if necessary conditions for
    //      activation/termination are not met
    private function _change_state($id = null, $should_terminate = false) {

        if ($should_terminate) {
            $method = 'terminate';
            $success_verb = 'τερματίστηκε';
        } else {
            $method = 'activate';
            $success_verb = 'ενεργοποιήθηκε';
        }

        if ($this->Offer->{$method}($id)) {
            $msg = "Η προσφορά $success_verb";
            $status = 200;
            $flash_type = "success";
            if ($should_terminate) $this->email_coupon_list($id);
        } else {
            // this is unlinkely to occur
            $msg = 'Προέκυψε κάποιο σφάλμα';
            $status = 400;
            $flash_type = "error";
        }

        $redirect = $this->is_webservice
                        ? null : array($this->request->referer());

        $this->notify(array($msg, 'default', array(), $flash_type),
                      $redirect,
                      $status);
    }

    // Sends an e-mail, listing all coupons of the offer that corresponds to the
    // supplied id to its owner-company.
    //
    // $id - offer id
    private function email_coupon_list($id) {

        // we require the email address to send the coupon listing to, as well
        // as the title of the offer (to use as email subject)
        // also, we need to make sure the offer is indeed of type 'coupons'
        $options = array('conditions' => array('Offer.id' => $id),
                         'fields' => array('Offer.title',
                                           'User.email'));

        $this->Offer->recursive = -1;
        // custom finders run as 'findAll' and not 'findFirst' so to access the
        // actual data, one must specify an index (in this case: 0)
        $offer = $this->Offer->find('couponInfo', $options);

        // fetch any available coupons
        if (! empty($offer)) {
            $coupons = $this->Coupon->get_offer_coupons($id);

            if (! empty($coupons)) {

                $offer_title = $offer[0]['Offer']['title'];
                $owner_email = $offer[0]['User']['email'];

                $email = new CakeEmail('default');
                // set parameters that are the same for all emails to be sent
                $email = $email
                    ->to($owner_email)
                    ->subject(__("Κουπόνια προσφοράς «{$offer_title}»"))
                    ->template('company_coupons', 'default')
                    ->emailFormat('both')
                    ->viewVars(array(
                        'offer_title' => $offer_title,
                        'coupons' => $coupons));

                try {
                    $email->send();
                } catch(Exception $e) {}
            }
        }
    }

    private function get_time($time) {
        $ts = strtotime($time);
        $h = date('H', $ts);
        $m = date('i', $ts);
        return array('hour' => $h, 'min' => $m);
    }

    private function trim_time($time) {
        return substr($time, 0, -3);
    }

    public function delete($id = null) {
        // An Offer can be delete only if it's draft.
        // At first, attempt to delete all Images and WorkHours
        // related to this Offer and then delete Offer.

        $options['conditions'] = array('Offer.id' => $id);
        $offer = $this->Offer->find('first', $options);

        if ($offer['Offer']['offer_state_id'] == STATE_DRAFT) {
            $transaction = $this->Offer->getDataSource();
            $transaction->begin();
            $error = false;

            if ($this->Image->deleteAll(array('Image.offer_id' => $id), false) &&
                $this->WorkHour->deleteAll(array('WorkHour.offer_id' => $id), false))
            {
                if (!$this->Offer->delete($id, false))
                    $error = true;
            } else {
                $error = true;
            }

            if ($error === true) {
                $transaction->rollback();

                return $this->notify(
                    // `setFlash' params
                    array(  'Παρουσιάστηκε κάποιο σφάλμα',
                             'default',
                             array(),
                            "error"),
                    // `redirect' params
                    array(  array(  'controller' => 'offers',
                                    'action' => 'view',
                                    $offer['Offer']['id'])),
                    // webservice api call status and extra info
                    400,
                    array(  'id' => $id));

            } else {
                $transaction->commit();

                return $this->notify(
                    array(  'Η προσφορά διαγράφηκε επιτυχώς',
                             'default',
                             array(),
                            "success"),
                    array(  array(  'controller' => 'companies',
                                    'action' => 'view',
                                    $offer['Company']['id'])),
                    200,
                    array(  'id' => $id));
            }
        } else {

            return $this->notify(
                array(  'Η προσφορά δεν μπορεί να διαγραφεί',
                         'default',
                         array(),
                        "info"),
                array(  array(  'controller' => 'offers',
                                'action' => 'view',
                                $offer['Offer']['id'])),
                400,
                array(  'id' => $id));
        }
    }

    // Computes the haversine distance between the to supplied locations. Each
    // parameter must be an array that contains the keys 'latitude' and
    // 'longitude'. If the [to] parameter is omitted (i.d., is_null on it
    // returns true), the TEI of Athen's coordinates, will be used.
    private function haversine_distance($from, $to=null) {
        if (is_null($to)) {
            if (is_null($this->Session->read('Auth.User.geolocation')))
                throw new NotFoundException();
            else {
                $to = $this->Session->read('Auth.User.geolocation');
            }
        }

        $radius = 6371;

        if(!is_numeric( $from['lat'] ) ||
            !is_numeric( $from['lng'])) {
            return null;
        }

        $latFrom = deg2rad($from['lat']);
        $latTo = deg2rad($to['lat']);
        $latDiff = deg2rad($to['lat'] - $from['lat']);
        $lngDiff = deg2rad($to['lng'] - $from['lng']);

        $latHaversine = sin($latDiff/2)*sin($latDiff/2);
        $lngHaversine = sin($lngDiff/2)*sin($lngDiff/2);

        $root = sqrt($latHaversine+cos($latFrom)*cos($latTo)*sin($lngHaversine));
        $distance = 2*$radius*asin($root);

        return $distance;
    }

    // Send email notification to company
    // when one of their offers has been flagged as improper
    private function improper_offer_notification ($offer = null, $owner = null, $students = array()) {
        if (is_null($offer) || is_null($owner)) {
            throw new BadRequestException();
        }

        $subject = __("Ειδοποίηση ανάρμοστης προσφοράς");
        $url = APP_URL."/offers/view/{$offer['Offer']['id']}";
        $title = $offer['Offer']['title'];
        $explanation = $offer['Offer']['explanation'];

        $cake_email = new CakeEmail('default');
        $cake_email = $cake_email
            ->to($owner)
            ->subject($subject)
            ->template('spam_notify', 'default')
            ->emailFormat('both')
            ->viewVars(array('url' => $url,'title' => $title,'explanation' => $explanation));
        try {
            $cake_email->send();
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    private function get_offer_visits($id) {
        $today = array(
            'd' => date("d"),
            'm' => date("m"),
            'y' => date("Y"));

        // Get today's and total visits statistics
        $visits = array();
        $visits['today'] = $this->StatsToday->get_visits($id, $today);
        $visits['past'] = $this->StatsTotal->get_visits($id);

        $month = (int)date('m');
        $year = (int)date('Y');
        foreach (range(1, MONTHS_BACK_STATS) as $i) {
            $monthly_stats = $this->StatsTotal->get_monthly_visits(
                $id, $month, $year);
            $visits['monthly'][$i - 1] = array(
                'month' => $month,
                'year' => $year,
                'stats' => $monthly_stats);
            $month = ($month === 1)?12:$month - 1;
            $year = ($month === 1)?$year - 1:$year;
        }

        // add today's visits to totals
        $visits['past']['total'] += $visits['today']['total'];
        $visits['past']['unique'] += $visits['today']['unique'];
        $visits['monthly'][0]['stats']['total'] += $visits['today']['total'];
        $visits['monthly'][0]['stats']['unique']+= $visits['today']['unique'];

        return $visits;
    }

    // Transforms an array of offers in CakePHP's intrinsic format into an array
    // capable of being converted into either XML or JSON.
    // Handles individual offer data as well as that of multiple offers.
    //
    // Currently, if `Offer' key is present at root level, then it is presumed
    // that the Retrieve operation was activated; otherwise, indexing is
    // presumed.
    //
    // @param $data must contain `Offer', `WorkHour' (ie, offer hours) and
    //      `Company' on case of a retrieve operation; an array of such
    //      sub-arrays for each 'row', otherwise
    // @param $is_xml defines whether the array should be formatted with xml in
    //      mind; default to true
    // @returns an array to be rendered as xml or json
    private function api_prepare_view($data, $is_xml = true) {

        // the result to be rendered in xml or json format; it is what this
        // function returns
        $result = array();

        $is_index = !array_key_exists('Offer', $data);

        // upon execution of the succeeding block, $offer_data becomes a
        // 0-based-index array of offers; this permits a more uniform handling
        // of iterating
        if ($is_index) {

            // the `_path' variables determine where should an item be inserted
            // into the $result array;
            // it is used to implement the appropriate array format
            $offer_path = 'offers.offer.';
            $company_path = 'companies.company.';

            // allows offers coming from differect operations (most notably
            // retrieve and index) to be handled in a uniform fashion, ie as an
            // array of 0-based-index arrays of offers (note that references are
            // used in order to minimize overhead)
            $offer_data = &$data;

        } else {

            $offer_path = 'offer.';
            $company_path = 'company.';

            $offer_data = array(0 => &$data);
        }

        // affects what details make sense to be returned (ie, not set to null)
        $uid = $this->Auth->user('id');

        // use this format unless one of the predefined constants is preferred
        $date_format = 'Y-m-d\TH:i:s';

        // the ids of companies that have already been formatted (avoid using
        // `in_array')
        $companies_id = array();

        // counters; utilized by `Set' to insert the current offer/company
        $offer_i = 0;
        $company_i = 0;
        foreach ($offer_data as $offer) {

            // -- make all necessary alterations --

            // identify logged in user as owner of company, and thus, offer as
            // well
            $is_owner = $uid == $offer['Company']['user_id'];

            // `r' is a temporary vessel of `Offer'
            $r = &$offer['Offer'];
            $company_id = $r['company_id'];

            // not needed
            unset($r['work_hour_count']);

            if (!$is_owner) {
                $r['autostart'] = null;
                $r['autoend'] = null;
            }

            // null-ify certain tags that wouldn't make sense for all offer
            // types
            if ($r['offer_type_id'] != TYPE_COUPONS) {
                $r['coupon_terms'] = null;
                $r['total_quantity'] = null;
                $r['max_per_student'] = null;
            }

            unset($r['offer_category_id']);
            $r['offer_category'] = $offer['OfferCategory']['name'];

            $r['offer_type'] = offer_type($r['offer_type_id']);
            unset($r['offer_type_id']);

            $r['offer_state'] = offer_state($r['offer_state_id']);
            unset($r['offer_state_id']);


            // work hours (will be displayed as `offer_hours')
            $r['offer_hours'] = array();
            foreach ($offer['WorkHour'] as $wh) {
                unset($wh['id']);
                unset($wh['company_id']);
                unset($wh['offer_id']);

                // don't show 2nd date part if dates are the same
                if ($wh['starting2'] == $wh['ending2']) {
                    unset($wh['starting2']);
                    unset($wh['ending2']);
                }
                $r['offer_hours'][] = $wh;
            }

            // -- perform insertions --
            $result = Set::insert($result, $offer_path.$offer_i, $r);

            if (!array_key_exists($company_id, $companies_id)) {

                // store current offer's company id for future reference
                $companies_id[$company_id] = null;

                // remember: $offer is the current record of the offers array
                $company = &$offer['Company'];
                unset($company['work_hour_count']);

                $result = Set::insert(
                    $result, $company_path.$company_i, $company);

                ++$company_i;
            }

            ++$offer_i;
        }

        if (empty($result)) {
            $result = array('offers' => null, 'companies' => null);
        } else
            // make any additional alterations, specifically for xml format
            if ($is_xml) {

                // create a simple wrapper of entities to be formatted
                // in case of Index operation, just omit the enclosing `offers' and
                // `companies' tags
                if ($is_index) {
                    $wrap = array(
                        'offer' => &$result['offers']['offer'],
                        'company' => &$result['companies']['company']);
                } else {
                    $wrap = &$result;
                }

                $this->xml_alter_view($wrap,$date_format);
            } else {
                // remove redundant index when requesting just one offer
                if ($is_index) {
                    $result['offers'] = &$result['offers']['offer'];
                    $result['companies'] = &$result['companies']['company'];
                } else {
                    $result['offer'] = &$result['offer'][0];
                    $result['company'] = &$result['company'][0];
                }
            }
        return $result;
    }

    // Makes necessary modifications to the supplied array `data' so that it may
    // properly be rendered in XML format. Currently, it is assumed that each
    // element possess an `id' field and several date fields. The latter are
    // defined inside the function.
    //
    // @param $data array containing `'offer' and `company' sub-arrays. Note
    //      that this param is passed by reference
    // @param $date_format a string or predefined date format to apply to all
    //      dates
    private function xml_alter_view(&$data, $date_format) {

        // all the date fields that are to be formatted
        $date_fields = array(
            'offer' => array(
                'started',
                'ended',
                'autostart',
                'autoend',
                'created',
                'modified'),
            'company' => array(
                'created',
                'modified'));

        // it is assumed that all entities possess an `id' attribute and,
        // potentially, dates; if not, a different approach is due
        foreach ($data as $type => $entities) {

            if (empty($entities)) continue;

            foreach ($entities as $index => $entity) {

                // make offer id appear as attribute
                $entity['@id'] = $entity['id'];
                unset($entity['id']);

                // format dates for this entity's date fields
                foreach ($date_fields[$type] as $field) {

                    // get entity's date from field $field
                    $date = $entity[$field];
                    if (!empty($date)) {
                        // format date
                        $entity[$field] = date($date_format, strtotime($date));
                    }
                }

                // insert updated offer back to the results
                $data[$type][$index] = $entity;
            }
        }
    }
}
