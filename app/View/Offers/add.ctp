<?php

echo $this->Form->create('Offer', array(
                            'type' => 'POST',
                            'enctype' => 'multipart/form-data'
                        ));

echo $this->Form->input('title', array('label' => 'Τίτλος'));
echo $this->Form->input('description', array(
                            'label' => 'Περιγραφή'
                       ));
echo $this->Form->input('offer_category_id', array(
                            'label' => 'Κατηγορία Προσφοράς'
                       ));
echo $this->Form->input('offer_type_id', array(
                            'label' => 'Είδος Προσοράς'
                       ));
echo $this->Form->input('starting', array(
                            'label' => 'Ημερ/νία & Ώρα έναρξης προσφοράς',
                            'separator' => ' ',
                            'dateFormat' => 'DMY',
                            'minYear' => date('Y'),
                            'maxYear' => date('Y') + 1,
                            'orderYear' => 'asc',
                            'timeFormat' => 24,
                            'interval' => 15
                       ));
echo $this->Form->input('ending', array(
                            'label' => 'Ημερ/νία & Ώρα λήξης προσφοράς',
                            'separator' => ' ',
                            'dateFormat' => 'DMY',
                            'minYear' => date('Y'),
                            'maxYear' => date('Y') + 1,
                            'orderYear' => 'asc',
                            'timeFormat' => 24,
                            'interval' => 15
                       ));
echo $this->Form->input('expiration_date', array(
                            'label' => 'Η προσφορά ισχύει μέχρι',
                            'type' => 'date',
                            'separator' => ' ',
                            'dateFormat' => 'DMY',
                            'minYear' => date('Y'),
                            'maxYear' => date('Y') + 5,
                            'orderYear' => 'asc'
                       ));
echo $this->Form->input('total_quantity', array(
                            'label' => 'Συνολικός αριθμός διαθέσιμων κουπονιών',
                            'empty' => 'Δεν έχει κουπόνια'
                       ));
echo $this->Form->input('tags', array('label' => 'Λέξεις κλειδιά'));
echo $this->Form->input('image', array(
                            'label' => 'Φωτογραφία',
                            'type' => 'file'
                       ));

echo $this->Form->end('Ανάρτηση');
