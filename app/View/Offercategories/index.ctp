<?php

echo $this->Html->link('Προσθήκη νέας κατηγορίας',
                       array('controller' => 'offercategories', 'action' => 'add'),
                       array('class' => 'btn'));

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
            foreach ($results as $category) {

                $id = $category['OfferCategory']['id'];
                $name = $category['OfferCategory']['name'];
                $link_edit = $this->Html->link(
                    '<i class="icon-edit"></i>',
                    array('controller' => 'offercategories',
                          'action' => 'edit',
                           $id),
                    array('escape' => false,
                          'title' => "Επεξεργασία κατηγορίας &laquo;$name&raquo;"));

                if ($category[0]['offer_count']) {
                    $link_delete = '<i class="icon-lock" '
                        .'title="Η κατηγορία διαθέτει προσφορές και δεν μπορεί '
                        .'να διαγραφεί"></i>';
                } else {
                    $link_delete = $this->Html->link(
                        '<i class="icon-remove"></i>',
                        array('controller' => 'offercategories',
                              'action' => 'delete',
                               $id),
                        array('escape' => false,
                              'title' => "Διαγραφή κατηγορίας &laquo;$name&raquo;",
                              'confirm' => 'Είστε βέβαιοι;'));
                }

                echo '<tr>';
                echo "<td>{$name}</td>";
                echo "<td>{$link_edit}</td>";
                echo "<td>{$link_delete}</td>";
                echo '</tr>';
            }
        ?>
    </tbody>
</table>
