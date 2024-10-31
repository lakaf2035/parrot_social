<?php

namespace local_parrot_social\forms;

use local_parrot_social\Models\Invitation;

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class EditInvitationForm extends \moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;

        $m_form = $this->_form;

        $attrs = $m_form->getAttributes();
        $attrs = array_merge($attrs, array('callback' => 'invitationUpdatedCallback'));
        $m_form->setAttributes($attrs);

        $m_form->addElement('hidden', 'action', 'update_invitation');
        $m_form->setType('action', PARAM_NOTAGS);

        $m_form->addElement('hidden', 'accept', 'true');
        $m_form->setType('action', PARAM_NOTAGS);

        $m_form->addElement('hidden', 'id');
        $m_form->setType('id', PARAM_NOTAGS);

        $actions = array();
        $accept = $m_form->createElement('submit', 'validate', get_string('invitation_accept', 'local_parrot_social'));
        $actions[] = &$accept;

        $reject = $m_form->createElement('submit', 'reject', get_string('invitation_reject', 'local_parrot_social'));
        $actions[] = &$reject;
        $m_form->addGroup($actions, 'actions', '', ' ', false);
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
