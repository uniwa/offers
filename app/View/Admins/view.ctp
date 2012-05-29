
<h6>Επιχειρησεις</h6>
<table class="table table-condensed table-striped">
    <thead>
        <tr>
            <th>Α/Α</th>
            <th>Όνομα επιχείρησης</th>
            <th>Κατάσταση</th>
        </tr>
    </thead>
    <tbody>
        <?php

            // incremental id; start counting from current's page 1st result
            $counter = (int) $this->Paginator->counter('{:start}');
            foreach ($data as $r) {

                $comp_id = $r['Company']['id'];
                $comp_name = $r['Company']['name'];

                $comp_url = array('controller' => 'companies',
                                  'action' => 'view',
                                  $comp_id);

                $comp_title = $this->Html->link($comp_name, $comp_url);

                if ($r['Company']['is_enabled']) {
                    $state = '<i class="icon-ok"></i>';
                } else {
                    $state = '<i class="icon-remove"></i>';
                }

                echo '<tr>';
                echo "<td>$counter</td>";
                echo "<td>$comp_title</td>";
                echo "<td>$state</td>";
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
