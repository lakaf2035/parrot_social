<?php

namespace local_parrot_social\forms;

use context_system;

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");
$context = \context_system::instance();
$PAGE->set_context($context);
class parrot_social_image_upload_form extends \moodleform {


    public function definition() {
        $mform = $this->_form;

        $attrs = $mform->getAttributes();
        $attrs = array_merge($attrs, array('callback' => 'mediaEditedCallback'));
        $mform->setAttributes($attrs);

        $mform->addElement('hidden', 'parrot_id', 'yes');
        $mform->setType('parrot_id', PARAM_TEXT);
        $mform->setDefault('parrot_id', $this->_customdata['parrot_id']);

        $mform->addElement('hidden', 'picture_id', 'yes');
        $mform->setType('picture_id', PARAM_INT);
        $mform->setDefault('picture_id', $this->_customdata['picture_id']);

        $draft_item_id  = file_get_submitted_draft_itemid('pictures');
        $context = context_system::instance();
        file_prepare_draft_area(
            $draft_item_id,
            $context->id,
            'local_parrot_social',
            'media',
            $this->_customdata['picture_id'],
            [
                'subdirs' => false,
                'maxfiles' => 1000,
                'accepted_types' => ['image'],
                // 'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            ]
        );
        $mform->addElement(
            'filemanager',
            'pictures',
            get_string('parrot_profile_picture', 'local_parrot_social'),
            null,
            [
                'subdirs' => false,
                'maxfiles' => 1000,
                'accepted_types' => ['image'],
                // 'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            ]
        );
        $mform->setDefault('pictures', $draft_item_id);

        $this->add_action_buttons(false);
    }

    public function validation($data, $files) {
        return array();
    }
}
