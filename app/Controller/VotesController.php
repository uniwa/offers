<?php

class VotesController extends AppController {

    public $name = 'Votes';
    public $uses = array('Vote', 'Offer');
    public $components = array('RequestHandler');

    public function beforeFilter() {
        if (! $this->is_authorized($this->Auth->user()))
            throw new ForbiddenException();

        // this call should precede all actions that return data (exceptions
        // included)
        $this->api_initialize();

        parent::beforeFilter();
    }

    public function is_authorized($user) {
        $role = $this->Auth->user('role');
        $students = array('vote_up', 'vote_down', 'vote_cancel', 'index');

        // Only students
        if (in_array($this->action, $students)) {
            if ($role === ROLE_STUDENT) {
                return true;
            }
            // prevent admins from performing student actions
            return false;
        }

        // admin can see banned users too
        return parent::is_authorized($user);
    }

    public function index() {
        if (! $this->Auth->user('id')) {
            throw new ForbiddenException();
        }

        if ($this->Auth->user('role') != ROLE_STUDENT) {
            throw new ForbiddenException();
        }

        $conditions = array(
            'student_id' => $this->Session->read('Auth.Student.id'));
        $fields = array(
            'Vote.vote',
            'Offer.id',
            'Offer.title',
            'Offer.vote_count',
            'Offer.vote_plus',
            'Offer.vote_minus',
            'Offer.offer_type_id'
        );
        $user_votes = $this->Vote->find('all', array(
            'conditions' => $conditions,
            'fields' => $fields,
            'recursive' => 0)
        );

        if ($this->is_webservice) {
            // proper format for API
            foreach ($user_votes as $key => $vote) {
                // omg this is handy :P
                $vote = array_change_key_case($vote, CASE_LOWER);
                $vote['offer']['vote_sum'] =
                    $vote['offer']['vote_plus'] - $vote['offer']['vote_minus'];
                $vote['vote'] = $vote['vote']['vote'] ? 1 : 0;
                unset($vote['vote']['vote']);
                $user_votes[$key] = $vote;
            }

            if ($this->webservice_type == 'xml') {
                $this->api_compile_response(
                    200,
                    array('votes' => array('vote_info' => $user_votes))
                );
            } else {
                $this->api_compile_response(
                    200, array('votes' => $user_votes));
            }
        } else {
            throw new ForbiddenException();
        }

    }

    // wrapper action for positive vote
    public function vote_up($id = null) {
        $this->vote($id, VOTE_UP);
    }

    // wrapper action for negative vote
    public function vote_down($id = null) {
        $this->vote($id, VOTE_DOWN);
    }

    // wrapper action for canceling vote
    public function vote_cancel($id = null) {
        $this->vote($id, VOTE_CANCEL);
    }

    // vote processing
    // update/add student vote and update offer vote count
    private function vote($offer_id = null, $value) {
        if (is_null($offer_id))
            throw new BadRequestException();

        // Get offer
        $options['conditions'] = array('Offer.id' => $offer_id);
        $options['recursive'] = -1;
        $offer = $this->Offer->find('first', $options);

        if (empty($offer)) throw new NotFoundException();

        if ($offer['Offer']['offer_state_id'] != STATE_ACTIVE)
            throw new ForbiddenException();

        // Get student id and vote
        $student_id = $this->Session->read('Auth.Student.id');
        $options['conditions'] = array(
            'Vote.offer_id' => $offer_id,
            'Vote.student_id' => $student_id);
        $options['recursive'] = -1;
        $vote = $this->Vote->find('first', $options);
        $vote_add = 0;

        if ($vote) {
            $cur_vote = (int)$vote['Vote']['vote'];
            if ($value === VOTE_CANCEL) {
                if ($cur_vote === VOTE_DOWN)
                    $offer['Offer']['vote_minus']--;
                else
                    $offer['Offer']['vote_plus']--;
                $vote_add = -1;
                // Delete vote
                $this->Vote->delete($vote['Vote']['id']);
            } else {
                if ($cur_vote <> $value)
                    if ($value === VOTE_UP) {
                        $offer['Offer']['vote_plus']++;
                        $offer['Offer']['vote_minus']--;
                    } else {
                        $offer['Offer']['vote_plus']--;
                        $offer['Offer']['vote_minus']++;
                    }
            }
        } else {
            if ($value !== VOTE_CANCEL) {
                if ($value === VOTE_UP) {
                    $offer['Offer']['vote_plus']++;
                } else {
                    $offer['Offer']['vote_minus']++;
                }
                $vote_add = 1;
                // Set other fields
                $vote['Vote']['offer_id'] = $offer_id;
                $vote['Vote']['student_id'] = $student_id;
            }
        }

        if ($value > VOTE_CANCEL){
            // Save vote
            $vote['Vote']['vote'] = $value;
            $this->Vote->save($vote);
        }

        // Get vote count
        $cur_count = (int)$offer['Offer']['vote_count'];

        // Update vote count and save offer without validation
        $offer['Offer']['vote_count'] = $cur_count + $vote_add;

        $update_fields = array(
            'Offer.vote_plus' => $offer['Offer']['vote_plus'],
            'Offer.vote_minus' => $offer['Offer']['vote_minus'],
            'Offer.vote_count' => $offer['Offer']['vote_count']);
        $update_conditions = array('Offer.id' => $offer['Offer']['id']);
        $this->Offer->updateAll($update_fields, $update_conditions);

        if ($this->is_webservice) {
            $vote_type = ($value === VOTE_CANCEL)?null:$value;
            $vote_result = array(
                'offer_id' => $offer_id,
                'vote_type' => $vote_type);
            $this->api_compile_response(
                200, array('vote' => $vote_result));
        } else {
            $this->redirect($this->request->referer());
        }
    }

}
