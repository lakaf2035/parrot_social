<?php

namespace local_parrot_social\forms;

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class InvitationForm extends \moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;

        $m_form = $this->_form;

        $attrs = $m_form->getAttributes();
        $attrs = array_merge($attrs, array('callback' => 'invitationCreatedCallback'));
        $m_form->setAttributes($attrs);

        $m_form->addElement('hidden', 'action', 'invite_parrot');
        $m_form->setType('action', PARAM_NOTAGS);

        $m_form->addElement('hidden', 'recipient_parrot_id', 'yes');
        $m_form->setType('recipient_parrot_id', PARAM_NOTAGS);
        $m_form->setDefault('recipient_parrot_id', $this->_customdata['recipient_parrot_id'] ?? 0);

        $m_form->addElement('hidden', 'sending_parrot_id', 'yes');
        $m_form->setType('sending_parrot_id', PARAM_NOTAGS);
        $m_form->setDefault('sending_parrot_id', $this->_customdata['sending_parrot_id'] ?? 0);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
