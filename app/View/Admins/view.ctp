<?php

    // == search panel ==
    $search_form = $this->Form->create(false);

    $search_form .= $this->Form->input('search', array(
        'label' => 'Μερικό όνομα ή e-mail',
    ));

    $search_form .= $this->Form->input('banned', array(
        'label' => 'κλειδωμένος',
        'type' => 'select',
        'empty' => true,
        'options' => array('ναι', 'όχι'),
    ));

    $search_form .= $this->Form->input('enabled', array(
        'label' => 'ενεργός',
        'type' => 'select',
        'empty' => true,
        'options' => array('ναι', 'όχι'),
    ));

    $search_form .= $this->Form->submit();
    $search_form .= $this->Form->end();

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

<?php echo $search_form; ?>

<h6>Επιχειρησεις</h6>
<table class="table table-condensed">
    <thead>
        <tr>
            <th>Α/Α</th>
            <?php echo "<th>{$comp_header}</th>" ?>
            <?php echo "<th>{$user_header}</th>" ?>
            <th>Διεύθυνση e-mail</th>
            <th>είναι κλειδωμένος;</th>
            <th>έχει ενεργοποιηθεί;</th>
        </tr>
    </thead>
    <tbody>
        <?php

            $enabled_title = "[απενεργοποίηση]";
            $enabled_action = 'disable';
            $disabled_title = "[ενεργοποίηση]";
            $disabled_action = 'enable';

            // incremental id; start counting from current's page 1st result
            $counter = (int) $this->Paginator->counter('{:start}');
            foreach ($data as $r) {

                $comp_name = $r['Company']['name'];
                $comp_url = array('controller' => 'companies',
                                  'action' => 'view',
                                  $r['Company']['id']);

                $link_disable = $this->Html->link($enabled_title, array(
                    'controller' => 'companies',
                    'action' => $enabled_action,
                    $r['Company']['id']));
                $link_enable = $this->Html->link($disabled_title, array(
                    'controller' => 'companies',
                    'action' => $disabled_action,
                    $r['Company']['id']));

                $comp_state = $r['Company']['is_enabled']
                                    ? '<i class="icon-ok"></i> '.$link_disable
                                    : '<i class="icon-remove"></i> '.$link_enable;

                $user_name = $r['User']['username'];
                $user_email = $r['User']['email'];

                $user_state = $r['User']['is_banned']
                                    ? '<i class="icon-ok"></i>'
                                    : '<i class="icon-remove"></i>';

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
<div class = 'pagination'>
    <ul>
        <?php echo $this->Paginator->numbers(array(
                                            'separator' => ' ',
                                            'tag' => 'li')); ?>
    </ul>
</div>
