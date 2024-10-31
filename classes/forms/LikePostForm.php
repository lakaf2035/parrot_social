<?php

namespace local_parrot_social\forms;

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class LikePostForm extends \moodleform {
    //Add elements to form
    public function definition() {

        $m_form = $this->_form;

        $attrs = $m_form->getAttributes();
        $attrs = array_merge($attrs, array('callback' => 'postLikedCallback'));
        $m_form->setAttributes($attrs);

        $m_form->addElement('hidden', 'action', 'like_post');
        $m_form->setType('action', PARAM_NOTAGS);

        $m_form->addElement('hidden', 'parrot_id', 'yes');
        $m_form->setType('parrot_id', PARAM_NOTAGS);
        $m_form->setDefault('parrot_id', $this->_customdata['parrot_id'] ?? 0);

        $m_form->addElement('hidden', 'post_id', 'yes');
        $m_form->setType('post_id', PARAM_NOTAGS);
        $m_form->setDefault('post_id', $this->_customdata['post_id'] ?? 0);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
