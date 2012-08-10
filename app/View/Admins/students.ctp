<?php

    // == search panel ==
    // get params from request to fill-in the form
    $search_text = isset($request['contains']) ? $request['contains'] : '';
    $search_banned = isset($request['banned']) ? $request['banned'] : '';

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

    $search_form .= $this->Form->end(array('label' => 'Αναζήτηση', 'class' => 'btn'));

    // == students listing ==
    // create sort order links; these will be placed as table headers
    $student_header = $this->Paginator->sort('Student.name', 'Όνοματεπώνυμο');
    $user_header = $this->Paginator->sort('User.username', 'Όνομα χρήστη');

    // set sort order icon in appropriate table header
    $sort_key = $this->Paginator->sortKey();
    if (isset($sort_key)) {
        $order = $this->Paginator->sortDir();
        $order_icon = $order == 'asc' ? 'up' : 'down';
        $order_icon = " <i class='icon-chevron-{$order_icon}'></i>";

        // append icon tag to appropriate header link
        if ($sort_key == 'Student.name') {
            $student_header .= $order_icon;
        } else if ($sort_key == 'User.username') {
            $user_header .= $order_icon;
        }
    }
?>

<div class='pull-left well'>
<h6>Αναζητηση Σπουδαστων</h6>
<?php echo $search_form; ?>
</div>

<div class='admin-results'>
<h6>Σπουδαστές</h6>
<table class="table table-condensed">
    <thead>
        <tr>
            <th>Α/Α</th>
            <?php echo "<th>{$student_header}</th>" ?>
            <?php echo "<th>{$user_header}</th>" ?>
            <th>Διεύθυνση e-mail</th>
        </tr>
    </thead>
    <tbody>
        <?php

            // incremental id; start counting from current's page 1st result
            $counter = (int) $this->Paginator->counter('{:start}');
            foreach ($data as $r) {

                $student_name = $r['Student']['name'];
                $student_url = array('controller' => 'students',
                                  'action' => 'view',
                                  $r['Student']['id']);

                $user_name = $r['User']['username'];
                $user_email = $r['User']['email'];

                $user_state = $r['User']['is_banned']
                                    ? '<i class="icon-ok"></i>'
                                    : '<i class="icon-remove"></i>';

                echo '<tr>';
                echo "<td>$counter</td>";
                echo "<td>{$this->Html->link($student_name, $student_url)}</td>";
                echo "<td>$user_name</td>";
                echo "<td>$user_email</td>";
                echo '</tr>';

                ++$counter;
            }
        ?>
    </tbody>
</table>
</div> <!-- admin results -->
<div class = 'pagination'>
    <ul>
        <?php echo $this->Paginator->numbers(array(
                                            'separator' => ' ',
                                            'ellipsis' => "<li><a>...</a></li>",
                                            'tag' => 'li')); ?>
    </ul>
</div>
