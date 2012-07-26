<?php
$html = '';
$page_title = _("Κλείδωμα επιχείρησης");
$html .= "<h2>{$page_title}</h2>";
$html .= "<h3>{$company['Company']['name']}</h3>";
$html .= "<br />";

$html .= $this->Form->create(false);

$label = _('Αιτιολογία');
$options = array('type' => 'text', 'label' => $label);
$html .= $this->Form->input('Company.explanation', $options);
$html .= "<br />";
$confirm = _('Κλείδωμα');
$cancel = _('Ακύρωση');
$options = array(
    'name' => 'cancel',
    'div' => array('class' => 'twobuttons'));
$html .= $this->Form->submit($cancel, $options);
$html .= $this->Form->end($confirm, $options);
echo $html;
