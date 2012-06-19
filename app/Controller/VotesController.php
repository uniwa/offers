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
                    $vote_minus--;
                else
                    $vote_plus--;
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
        $result = $this->Offer->save($offer, false);

        // TODO handle web service and different origin
        $this->redirect(array('controller' => 'offers', 'action' => 'view', $offer_id));
    }

}
