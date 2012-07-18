<?php
    $html = '';
    $html .= "<div class='well'>";
    $html .= "<h4>Τα κουπόνια μου</h4><br />";
    $html .= "<table class='table table-condensed table-striped'>";
    $html .= "<thead><tr>";
    if ($view === 'profile') {
        $html .= "<th>Τίτλος προσφοράς</th>";
        $html .= "<th>Εταιρία</th>";
    }
    $html .= "<th>Κωδικός κουπονιού</th>";
    $html .= "<th>Ημ/νία δέσμευσης</th>";

    if ($role === ROLE_STUDENT) {
        $html .= "<th>Διαγραφή</th>\n";
        $html .= "<th>Download</th>\n";
    }

    $html .= "</tr></thead><tbody>";
        foreach ($coupons as $c) {
            // if we use it more than one time put it here
            // to avoid bloated code!
            $date = $c['Coupon']['created'];
            $serial_number = $c['Coupon']['serial_number'];

            if ($view === 'profile') {
                $title = $c['Offer']['title'];

                // build links
                $offer_link = $this->Html->link(
                    $title,
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
            }

            $coupon_link = $this->Html->link(
                $serial_number,
                array(
                    'controller' => 'coupons',
                    'action' => 'view',
                    $c['Coupon']['id']
                ),
                array()
            );

            if ($role === ROLE_STUDENT) {
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
            }

            $html .= "<tr>";

            if ($view === 'profile') {
                // offer title - append "spam" keyword on spam
                $html .= "<td>";
                if ($c['Offer']['is_spam']) {
                    $spam = _('spam');
                    $html .= "<span class='label label-important'>{$spam}</span> ";
                    $html .= $title;
                }
                else {
                    $html .= $offer_link;
                }
                $html .= "</td>";

                $html .= "<td>{$company_link}</td>";
            }

            $pdf_link = $this->Html->link(
                "pdf",
                array(
                    'controller' => 'coupons',
                    'action' => 'pdf',
                    $c['Coupon']['id']
                )
            );
            $strikethrough = array();
            $pdf = " - ";
            if ($c['Coupon']['reinserted']) {
                $strikethrough['pre'] = "<span class='strikethrough'>";
                $strikethrough['post'] = "</span>";
            } else {
                $strikethrough['pre'] = "";
                $strikethrough['post'] = "";
                $pdf = $pdf_link;
            }
            // coupon link - don't allow coupon view if spam
            $html .= "<td>{$strikethrough['pre']}";
            if (($c['Offer']['is_spam'])
                || ($c['Coupon']['reinserted'])
                || ($role !== ROLE_STUDENT)) {
                $html .= $serial_number;
            } else {
                $html .= $coupon_link;
            }
            $html .= "{$strikethrough['post']}</td>";

            $html .= "<td>{$this->Time->format('d-m-Y',$date)}</td>";

            // show delete link only for finished offers
            if ($role === ROLE_STUDENT) {
                if ($c['Offer']['ended'] === true) {
                    $html .= "<td>{$delete_link}</td>";
                } else {
                    $delete = "<td title='μόνο κουπόνια από μη ενεργές";
                    $delete .= "προσφορές μπορούν να διαγραφούν'";
                    $delete .= "class='help-text'>διαγραφή</td>";
                    $html .= $delete;
                }

                $html .= "<td>{$pdf}</td>";
            }
            // to add trash icon use: <i class=\"icon-trash\"></i>
            $html .= "</tr>";
        }

    $html .= "</tbody></table></div>";

    echo $html;
