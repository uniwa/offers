<?php

    // == search panel ==
    // get params from request to fill-in the form
    $search_text = isset($request['contains']) ? $request['contains'] : '';
    $search_banned = isset($request['banned']) ? $request['banned'] : '';
    $search_enabled = isset($request['enabled']) ? $request['enabled'] : '';

    $search_form = $this->Form->create(false);

    $search_form .= $this->Form->input('contains', array(
        'label' => 'Μερικό όνομα ή e-mail',
        'value' => $search_text,
    ));

    $search_form .= $this->Form->input('banned', array(
        'label' => 'κλειδωμένος',
        'type' => 'select',
        'options' => array('αδιάφορο', 'ναι', 'όχι'),
        'value' => $search_banned,
    ));

    $search_form .= $this->Form->input('enabled', array(
        'label' => 'ενεργός',
        'type' => 'select',
        'options' => array('αδιάφορο', 'ναι', 'όχι'),
        'value' => $search_enabled,
    ));

    $search_form .= $this->Form->end(array('label' => 'Αναζήτηση', 'class' => 'btn'));

    // == companies listing ==
    // create sort order links; these will be placed as table headers
    $comp_header = $this->Paginator->sort('Company.name', 'Όνομα επιχείρησης');
    $user_header = $this->Paginator->sort('User.username', 'Όνομα χρήστη');

    // set sort order icon in appropriate table header
    $sort_key = $this->Paginator->sortKey();
    if (isset($sort_key)) {
        $order = $this->Paginator->sortDir();
        $order_icon = $order == 'asc' ? 'up' : 'down';
        $order_icon = " <i class=\"icon-chevron-{$order_icon}\"></i>";

        // append icon tag to appropriate header link
        if ($sort_key == 'Company.name') {
            $comp_header .= $order_icon;
        } else if ($sort_key == 'User.username') {
            $user_header .= $order_icon;
        }
    }
?>
<!-- admin controls wrapper -->
<div class='row-fluid'>
    <div class='well span3'>
        <h6>Αναζητηση επιχειρησεων</h6>
        <?php echo $search_form; ?>
    </div>

<div class="well span8">
    <h6>Ενημερωση επιχειρησεων</h6>
    <p>Όποτε επιθυμείτε τη μαζική ενημέρωση των εγγεγραμμένων επιχειρήσεων, χρησιμοποιείστε το παρακάτω πλήκτρο για να εμφανίσετε μία λίστα με τις διευθύνσεις ηλεκτρονικού ταχυδρομείου τους (e-mail).</p>
    <ol>
        <li><p>Πατήστε το πλήκτρο «Προβολή e-mail» (που βρίσκεται παρακάτω) ώστε να εμφανιστεί μία λίστα με τις διευθύνσεις ηλεκτρονικού ταχυδρομείου (e-mail) των ενεργοποιημένων επιχειρήσεων.</p></li>
        <li><p>Επιλέξτε όλες τις διευθύνσεις e-mail (πχ δεξί κλικ → επιλογή όλων) και αντιγράψτε τες (πχ, δεξί κλικ → αντιγραφή).</p></li>
        <li><p>Επικολλήστε τις διευθύνσεις στο πεδίο «BCC» (Ιδιαίτερη κοινοποίηση) του προγράμματος αποστολής e-mail που χρησιμοποιείτε (πχ, Thunderbird, Microsoft Outlook®).</p></li>
    </ol>
    <p><i class="icon-warning-sign"></i> Είναι σημαντικό να επικολλήσετε τις διευθύνσεις στο πεδίο «BCC» και όχι στο «Προς» για την τήρηση του ιδιωτικού απορρήτου των επιχειρήσεων!</p>
    <div><?php echo $this->Html->link('Προβολή e-mail', array('controller' => 'companies', 'action' => 'emails'), array('class' => 'btn')); ?></div>
</div>
</div> <!-- admin-controls-wrapper -->

<div class="admin-results">
<h6>Επιχειρησεις</h6>
<table class="table table-condensed">
    <thead>
        <tr>
            <th>Α/Α</th>
            <?php echo "<th>{$comp_header}</th>" ?>
            <?php echo "<th>{$user_header}</th>" ?>
            <th>Διεύθυνση e-mail</th>
            <th>κλείδωμα / ξεκλείδωμα</th>
            <th>ενεργοποίηση / απενεργοποίηση</th>
        </tr>
    </thead>
    <tbody>
        <?php

            // css options for buttons
            $disable_icon = $this->Html->tag('i', '', array('class' => 'icon-warning-sign icon-white'));
            $ban_icon = $this->Html->tag('i', '', array('class' => 'icon-lock icon-white'));
            $btn_green = 'btn-mini btn-success';
            $btn_red = 'btn-mini btn-danger';

            $disable_title = "{$disable_icon}&nbsp;απενεργοποίηση";
            $disable_action = 'disable';
            $enable_title = "ενεργοποίηση";
            $enable_action = 'enable';
            $ban_title = "{$ban_icon}&nbsp;κλείδωμα";
            $ban_action = 'ban';
            $unban_title = "ξεκλείδωμα";
            $unban_action = 'unban';

            // incremental id; start counting from current's page 1st result
            $counter = (int) $this->Paginator->counter('{:start}');
            foreach ($data as $r) {

                $comp_name = $r['Company']['name'];
                $comp_url = array('controller' => 'companies',
                                  'action' => 'view',
                                  $r['Company']['id']);

                $link_disable = $this->Html->link($disable_title, array(
                    'controller' => 'companies',
                    'action' => $disable_action,
                    $r['Company']['id']),
                    array('class' => $btn_red, 'escape' => false)
                );
                $link_enable = $this->Html->link($enable_title, array(
                    'controller' => 'companies',
                    'action' => $enable_action,
                    $r['Company']['id']),
                    array('class' => $btn_green)
                );
                $link_ban = $this->Html->link($ban_title, array(
                    'controller' => 'companies',
                    'action' => $ban_action,
                    $r['Company']['id']),
                    array('class' => $btn_red, 'escape' => false)
                );
                $link_unban = $this->Html->link($unban_title, array(
                    'controller' => 'companies',
                    'action' => $unban_action,
                    $r['Company']['id']),
                    array('class' => $btn_green)
                );

                $comp_state = $r['Company']['is_enabled'] ? $link_disable : $link_enable;

                $user_name = $r['User']['username'];
                $user_email = $r['User']['email'];

                $user_state = $r['User']['is_banned'] ? $link_unban : $link_ban;

                echo '<tr>';
                echo "<td>$counter</td>";
                echo "<td>{$this->Html->link($comp_name, $comp_url)}</td>";
                echo "<td>$user_name</td>";
                echo "<td>$user_email</td>";
                echo "<td>$user_state</td>";
                echo "<td>$comp_state</td>";
                echo '</tr>';

                ++$counter;
            }
        ?>
    </tbody>
</table>
</div>
<div class = 'pagination'>
    <ul>
        <?php echo $this->Paginator->numbers(array(
                                            'separator' => ' ',
                                            'tag' => 'li')); ?>
    </ul>
</div>
