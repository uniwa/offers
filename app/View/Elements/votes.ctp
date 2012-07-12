<div class="well">
    <h4>Προσφορές που έχω ψηφίσει</h4>
    <br />
    <table class="table table-condensed table-striped">
        <thead>
            <tr>
                <th>Τίτλος προσφοράς</th>
                <th>Συνολικοί Ψήφοι</th>
                <th>
                <?php
                // this header is a link that sorts voted offers

                // default votes order
                $order = 'down';
                $icon = '<i class="icon-chevron-down"></i>';
                // set order for url based on previous selection
                if (isset($this->params['named']['order'])) {
                    if ($this->params['named']['order'] == 'down') {
                        $order = 'up';
                        $icon = '<i class="icon-chevron-up"></i>';
                    }
                }
                // sort url for votes
                echo $this->Html->link('Η ψήφος μου '.$icon, array(
                    'controller' => 'students',
                    'action' => 'view',
                    'order' => $order),
                    array('escape' => false)
                );
                ?>
                </th>
            </tr>
        </thead>
        <tbody>
        <?php
        foreach($voted_offers as $offer) {
            echo '<tr>';
            // offer title
            echo '<td>';
            echo $this->Html->link($offer['Offer']['title'], array(
                'controller' => 'offers',
                'action' => 'view',
                $offer['Offer']['id']
                ),
                array()
            );
            echo '</td>';

            // all votes
            echo '<td class="profile-votes">';
            $offer_votes = "<ul><li><span class='green'>+{$offer['Offer']['vote_plus']}</span></li>";
            $offer_votes .= "<li><span class='red'>-{$offer['Offer']['vote_minus']}</span></li>";
            $offer_votes .= "<li>({$offer['Offer']['vote_count']})</li></ul>";
            echo $offer_votes;

            echo '</td>';

            // student vote + vote controls
            echo '<td class="profile-votes">';
            $vote_class = ($offer['Vote']['vote'])?'green':'red';
            $my_vote = ($offer['Vote']['vote'])?'+1':'-1';
            //$vote_elemnts = "<div class='{$vote_class}'>{$my_vote}</div>";
            $vote_elements = "<ul><li class='{$vote_class}'>{$my_vote}</li>";

            if ($this->Session->read('Auth.User.role') === ROLE_STUDENT) {
                $icon_thumbs_up = "<i class='icon-thumbs-up'></i>";
                $icon_thumbs_down = "<i class='icon-thumbs-down'></i>";
                $icon_cancel = "<i class='icon-remove'></i>";
                $link_up = $this->Html->link($icon_thumbs_up,
                    array('controller' => 'votes', 'action' => 'vote_up', $offer['Offer']['id']),
                    array('escape' => false));
                $link_down = $this->Html->link($icon_thumbs_down,
                    array('controller' => 'votes', 'action' => 'vote_down', $offer['Offer']['id']),
                    array('escape' => false));
                $link_cancel = $this->Html->link($icon_cancel,
                    array('controller' => 'votes', 'action' => 'vote_cancel', $offer['Offer']['id']),
                    array('escape' => false));
                //echo "<p>{$link_up} {$link_down} {$link_cancel}</p>";
                $vote_elements .= "<li>{$link_up}</li><li>{$link_down}</li><li>{$link_cancel}</li>";
            }
            // close and print list
            $vote_elements .= '</ul>';
            echo $vote_elements;

            echo '</td>';
            echo '</tr>';
        }
        ?>
        </tbody>
    </table>
</div>
