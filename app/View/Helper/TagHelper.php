<?php
App::uses('AppHelper', 'View/Helper');

class TagHelper extends AppHelper {
    public $helpers = array('Html');

    public function generate($tag_string, $options = array()) {
        // handle with stupid way (the PHP way) optional parameters
        $defaults = array(
            'label' => 'Tags: ',
            'separator' => ' ',
            'element' => 'div',
            'class' => NULL,
            'link' => array()
        );
        $options = array_merge($defaults, $options);
        extract($options);

        // generate html with tags as links
        $tags = explode($separator, trim($tag_string));

        // remove duplicates
        $tags = array_unique($tags);

        // init counters
        $tag_num = count($tags);
        $tag_counter = 0;

        if ($class === NULL) {
//            $html = "<{$element}>{$label}";
            $html = "<{$element}>";
        } else {
//            $html = "<{$element} class=\"{$class}\">{$label}";
            $html = "<{$element} class=\"{$class}\">";
        }

        foreach ($tags as $tag) {
            $html .= $this->Html->link(
                $tag,
                array(
                    'controller' => $link['controller'],
                    'action' => $link['action'],
                    $tag)
            );

            // this is the only (and horrible) solution
            // because CakePHP is a lie and does not provide a
            // template engine and thus no mechanism to make this
            // intuitive
            $tag_counter++;
            if ($tag_counter !== $tag_num)
                $html .= ", ";
        }
        $html .= "</${element}>";

        return $html;
    }
}
