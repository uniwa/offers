<?php

class VotesController extends AppController {

    public $name = 'Votes';
    public $uses = array('Vote', 'Offer');
    public $components = array('RequestHandler');

    public function beforeFilter() {
        if (! $this->is_authorized($this->Auth->user()))
            throw new ForbiddenException();

        parent::beforeFilter();
    }

    public function is_authorized($user) {
        $role = $this->Auth->user('role');
        $students = array('vote_up', 'vote_down', 'vote_cancel');

        // Only students
        if (in_array($this->action, $students)) {
            if ($role === ROLE_STUDENT) {
                return true;
            }
        }

        // admin can see banned users too
        return parent::is_authorized($user);
    }

    public function vote_up($id = null) {
        $this->vote($id, 1);
    }

    public function vote_down($id = null) {
        $this->vote($id, 0);
    }

    public function vote_cancel($id = null) {
        $this->vote($id, -1);
    }

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

        $student_id = $this->Session->read('Auth.Student.id');
        $options['conditions'] = array(
            'Vote.offer_id' => $offer_id,
            'Vote.student_id' => $student_id);
        $options['recursive'] = -1;
        $vote = $this->Vote->find('first', $options);

debug("val:".$value);
        if ($vote) {
            $cur_vote = (int)$vote['Vote']['vote'];
debug("cur:".$cur_vote);
            if ($value === -1) {
                $vote_diff = ($cur_vote === 0)?1:-1;
                // Delete vote
                $this->Vote->delete($vote['Vote']['id']);
            } else {
                $vote_diff = ($cur_vote <> $value)?($value - $cur_vote) * 2:0;
            }
        } else {
            if ($value === -1) {
                $vote_diff = 0;
            } else {
                $cur_vote = -1;
                $vote_diff = ($value)?1:-1;
                $vote['Vote']['offer_id'] = $offer_id;
                $vote['Vote']['student_id'] = $student_id;
            }
        }

        if ($value > -1){
            // Save vote
            $vote['Vote']['vote'] = $value;
            $this->Vote->save($vote);
        }

        $cur_count = (int)$offer['Offer']['vote_count'];
        $offer['Offer']['vote_count'] = $cur_count + $vote_diff;
        $result = $this->Offer->save($offer, false);
debug($result);

        $this->redirect(array('controller' => 'offers', 'action' => 'view', $offer_id));
    }

}
