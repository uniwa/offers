
<h6>Επιχειρησεις</h6>
<table class="table">
    <thead>
        <tr>
            <th>Α/Α</th>
            <th>Όνομα επιχείρησης</th>
            <th>Όνομα χρήστη</th>
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
