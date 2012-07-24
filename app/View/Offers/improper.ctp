<?php
$html = '';
$page_title = _("Επισήμανση προσφοράς ως ανάρμοστη");
$html .= "<h2>{$page_title}</h2>";
$html .= "<h3>{$offer['Offer']['title']}</h3>";
$html .= "<br />";

$html .= $this->Form->create(false);

$label = _('Αιτιολογία');
$options = array('type' => 'text', 'label' => $label);
$html .= $this->Form->input('Offer.explanation', $options);
$html .= "<br />";
$confirm = _('Επισήμανση ως ανάρμοστη');
$cancel = _('Ακύρωση');
$options = array(
    'name' => 'cancel',
    'div' => array('class' => 'twobuttons'));
$html .= $this->Form->submit($cancel, $options);
$html .= $this->Form->end($confirm, $options);
echo $html;
