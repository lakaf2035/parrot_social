<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    local_parrot_social
 * @copyright  2020 Center La'ahtech
 * @author     Bouh Ivan
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_parrot_social\pages;


use local_parrot_social\forms\CreatePostForm;
use local_parrot_social\forms\DeletePostForm;
use local_parrot_social\forms\EditInvitationForm;
use local_parrot_social\forms\UpdateParrotForm;
use local_parrot_social\forms\UpdatePostForm;
use local_parrot_social\Models\Invitation;
use local_parrot_social\Models\Parrot;
use local_parrot_social\Models\Post;
use moodle_page;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

defined('MOODLE_INTERNAL') || die;

// set system context
$context = \context_system::instance();
$PAGE->set_context($context);

// get logged user
require_login();
global $USER;


//s et page url
$parrot_id = optional_param('parrot_id', 0, PARAM_TEXT);
$page_url = new \moodle_url('/local/parrot_social/pages/parrot_friendship_requests.php', array('parrot_id' => $parrot_id));
$PAGE->requires->js('/local/parrot_social/assets/js/ajax.js', array('defer' => true));
$PAGE->requires->css('/local/parrot_social/assets/css/style.css');
$PAGE->set_url($page_url);
$title = get_string('parrot_profile_title', 'local_parrot_social');
$PAGE->set_title($title);
$PAGE->set_heading("");
echo $OUTPUT->header();

// get parrot from id
$parrot_controller_url = new \moodle_url('/local/parrot_social/controllers/ParrotController.php', array('current_parrot' => $parrot_id));
$parrot = Parrot::get($parrot_id);
$invitation_update_form = new EditInvitationForm($parrot_controller_url->out());
$received_invitations = Invitation::all(array('recipient_parrot_id' => $parrot_id, 'status' => Invitation::STATUS_PENDING));
$received_invitations = array_map(function ($invitation) use ($invitation_update_form) {
    $invitation = (object) $invitation->data;
    $invitation->parrot = Parrot::get($invitation->sending_parrot_id);
    $invitation_update_form->set_data($invitation);
    $invitation->form_html = $invitation_update_form->render();
    return $invitation;
}, $received_invitations);

// echo json_encode(Parrot::getPosts($parrot_id));
// print rest of the page
echo $OUTPUT->render_from_template('local_parrot_social/parrot_friendship_requests', array(
    'is_owner' => $parrot->user_id === $USER->id,
    'parrot' => $parrot,
    'received_invitations' => $received_invitations,
));

// print footer
echo $OUTPUT->footer();
