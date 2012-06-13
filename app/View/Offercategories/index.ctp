<?php

echo $this->Html->link('Προσθήκη.',
                       array('controller' => 'offercategories', 'action' => 'add'));

echo '<br/><br/>';

?>
<table class="table table-condensed">
    <thead>
        <tr>
            <th>Ονομασία κατηγορίας</th>
            <th>Επεξεργασία</th>
            <th>Διαγραφή</th>
        </tr>
    </thead>
    <tbody>
        <?php
            foreach ($results as $id => $name) {

                echo '<tr>';
                echo "<td>{$name}</td>";
                echo "<td><i class=\"icon-edit\"></i></td>";
                echo "<td><i class=\"icon-remove\"></i></td>";
                echo '</tr>';
            }
        ?>
    </tbody>
</table>
