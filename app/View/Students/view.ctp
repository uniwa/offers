<div class="well">
    <h4>Στοιχεία χρήστη</h4>
    <p><?php echo "{$user['firstname']} {$user['lastname']}";?></p>
    <p><i class="icon-user"></i> <?php echo $user['username'];?></p>
    <p><i class="icon-envelope"></i> <?php echo $user['email'];?></p>
</div>

<div class="well">
    <h4>Κουπόνια</h4>
    <br />
    <table class="table table-condensed table-striped">
        <thead>
            <tr>
                <th>Τίτλος προσφοράς</th>
                <th>Εταιρία</th>
                <th>Κωδικός κουπονιού</th>
                <th>Ημ/νία δέσμευσης</th>
                <th>Διαγραφή</th>
            </tr>
        </thead>
        <tbody>
            <?php
                foreach ($coupons as $c) {
                    // filter data we want
                    $date = $c['Coupon']['created'];

                    // build links
                    $offer_link = $this->Html->link(
                        $c['Offer']['title'],
                        array(
                            'controller' => 'offers',
                            'action' => 'view',
                            $c['Offer']['id']
                        ),
                        array()
                    );

                    $company_link = $this->Html->link(
                        $c['Offer']['Company']['name'],
                        array(
                            'controller' => 'companies',
                            'action' => 'view',
                            $c['Offer']['company_id']
                        ),
                        array()
                    );

                    $coupon_link = $this->Html->link(
                        $c['Coupon']['serial_number'],
                        array(
                            'controller' => 'coupons',
                            'action' => 'view',
                            $c['Coupon']['id']
                        ),
                        array()
                    );

                    $delete_link = $this->Html->link(
                        "delete",
                        array(
                            'controller' => 'coupons',
                            'action' => 'delete',
                            $c['Coupon']['id']
                        ),
                        array(),
                        "Αυτή η ενέργεια δεν μπορεί να αναιρεθεί"
                    );

                    echo "<tr>";
                    echo "<td>{$offer_link}</td>";
                    echo "<td>{$company_link}</td>";
                    echo "<td>{$coupon_link}</td>";
                    echo "<td>{$this->Time->format('d-m-Y',$date)}</td>";
                    echo "<td>{$delete_link}";
                    // to add trach icon use: <i class=\"icon-trash\"></i>
                    echo "</tr>";



                }

            ?>
        </tbody>
    </table>
</div>

