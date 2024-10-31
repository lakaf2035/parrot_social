<?php

namespace local_parrot_social\forms;

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class DeletePostForm extends \moodleform {
    //Add elements to form
    public function definition() {

        $m_form = $this->_form;

        $attrs = $m_form->getAttributes();
        $attrs = array_merge($attrs, array('callback' => 'postDeletedCallback'));
        $m_form->setAttributes($attrs);

        $m_form->addElement('hidden', 'action', 'delete_post');
        $m_form->setType('action', PARAM_NOTAGS);

        $m_form->addElement('hidden', 'id');
        $m_form->setType('id', PARAM_NOTAGS);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
