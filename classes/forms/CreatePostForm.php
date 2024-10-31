<?php

namespace local_parrot_social\forms;

use context_system;

require_once(__DIR__ . '/../../../../config.php');
require_once("$CFG->libdir/formslib.php");

class CreatePostForm extends \moodleform {
    //Add elements to form
    public function definition() {
        global $CFG;

        $m_form = $this->_form;

        $attrs = $m_form->getAttributes();
        $attrs = array_merge($attrs, array('callback' => 'postCreatedCallback'));
        $m_form->setAttributes($attrs);

        $m_form->addElement('hidden', 'action', 'create_post');
        $m_form->setType('action', PARAM_NOTAGS);

        $m_form->addElement('hidden', 'parrot_id', 'yes');
        $m_form->setType('parrot_id', PARAM_NOTAGS);
        $m_form->setDefault('parrot_id', $this->_customdata['parrot_id'] ?? 0);

        $m_form->addElement('text', 'title', get_string('post_title', 'local_parrot_social'));
        $m_form->setType('title', PARAM_NOTAGS);
        $m_form->addRule('title', get_string('title_required', 'local_parrot_social'), 'required', null, 'server');

        $draft_id_editor = file_get_submitted_draft_itemid('text');
        $context = context_system::instance();
        $current_text = file_prepare_draft_area(
            $draft_id_editor,
            $context->id,
            'local_parrot_social',
            'posts',
            0,
            [
                'subdirs' => false,
                '',
            ]
        );
        $m_form->addElement('editor', 'text', get_string('post_text', 'local_parrot_social'), 'cols="30" rows="10"', array(
            'noclean' => true,
            'trusttext' => true,
            'context' => $context,
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'enable_filemanagement' => true,
        ));
        $m_form->setDefault('text', [
            'text' => $current_text,
            'format' => FORMAT_HTML,
            'itemid' => $draft_id_editor,
        ]);
        $m_form->setType('text', PARAM_RAW);

        $this->add_action_buttons(false, get_string('create_post', 'local_parrot_social'));
    }

    //Custom validation should be added here
    function validation($data, $files) {
        return array();
    }
}
