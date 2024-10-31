<?php

namespace local_parrot_social\forms;

use context_system;

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class UpdateParrotForm extends \moodleform {

    //Add elements to form
    public function definition() {
        global $CFG;

        $m_form = $this->_form;

        $attrs = $m_form->getAttributes();
        $attrs = array_merge($attrs, array('callback' => 'parrotUpdatedCallback'));
        $m_form->setAttributes($attrs);

        $m_form->addElement('hidden', 'action', 'update_parrot');
        $m_form->setType('action', PARAM_NOTAGS);

        $m_form->addElement('hidden', 'parrot_id', 'yes');
        $m_form->setType('parrot_id', PARAM_NOTAGS);
        $m_form->setDefault('parrot_id', $this->_customdata['parrot_id'] ?? 0);

        $draft_item_id  = file_get_submitted_draft_itemid('picture');
        $context = context_system::instance();
        file_prepare_draft_area(
            $draft_item_id,
            $context->id,
            'local_parrot_social',
            'profiles',
            $this->_customdata['picture_id'] ?? 0,
            [
                'subdirs' => false,
                'maxfiles' => 1,
                'accepted_types' => ['image'],
                // 'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            ]
        );
        $m_form->addElement(
            'filemanager',
            'picture',
            get_string('parrot_profile_picture', 'local_parrot_social'),
            null,
            [
                'subdirs' => false,
                'maxfiles' => 1,
                'accepted_types' => ['image'],
                // 'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            ]
        );
        $m_form->setDefault('picture', $draft_item_id);

        $m_form->addElement('text', 'name', get_string('parrot_title', 'local_parrot_social'));
        $m_form->setType('name', PARAM_NOTAGS);
        $m_form->addRule('name', get_string('name_required', 'local_parrot_social'), 'required', null, 'server');

        $m_form->addElement('editor', 'description', get_string('parrot_description', 'local_parrot_social'), 'cols="30" rows="10"');
        $m_form->setType('description', PARAM_RAW);

        $this->add_action_buttons(false, get_string('update', 'local_parrot_social'));
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
