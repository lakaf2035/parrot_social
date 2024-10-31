<?php

use local_parrot_social\forms\EditInvitationForm;
use local_parrot_social\forms\InvitationForm;
use local_parrot_social\forms\UpdateParrotForm;
use local_parrot_social\Models\Friendship;
use local_parrot_social\Models\Invitation;
use local_parrot_social\Models\Parrot;
use local_parrot_social\Utils\Notification;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->dirroot . '/lib/filelib.php');

// namespace
defined('MOODLE_INTERNAL') || die;

// set system context
$context = \context_system::instance();
$PAGE->set_context($context);

// get logged user
require_login();
$parrot_id = required_param('current_parrot', PARAM_TEXT);

$action = optional_param('action', 0, PARAM_TEXT);
if ($action && function_exists($action)) {
    // TODO handle case when parrot_id is NULL
    $response = $action();
    echo json_encode($response);
    // return redirect(new \moodle_url('/local/parrot_social/pages/parrot_profile.php', array('parrot_id' => $parrot_id)));
} else {
    throw new \moodle_exception('Invalid action', 'local_parrot_social');
}

function update_invitation() {
    global $DB, $OUTPUT;
    $id = required_param('id', PARAM_INT);
    $invitation = Invitation::get($id);
    $invitation->status = !empty($_REQUEST['accept']) ? Invitation::STATUS_ACCEPTED : Invitation::STATUS_REJECTED;
    $invitation->save();
    $friendship_exists = Friendship::all(
        array(
            'parrot1_id' => $invitation->recipient_parrot_id,
            'parrot2_id' => $invitation->sending_parrot_id
        )
    )
        || Friendship::all(
            array(
                'parrot1_id' => $invitation->sending_parrot_id,
                'parrot2_id' => $invitation->recipient_parrot_id
            )
        );

    // check if friendship does not already exist and invitation was accepted
    $notificationmessage = 'rejected_friendship_request';
    if (!$friendship_exists and $invitation->status == Invitation::STATUS_ACCEPTED) {
        $notificationmessage = 'accepted_friendship_request';
        $friendship = new Friendship(array(
            'parrot1_id' => $invitation->recipient_parrot_id,
            'parrot2_id' => $invitation->sending_parrot_id,
        ));
        $friendship->save();
    } else {
        $friendship = null;
    }
    $parrot = Parrot::get($invitation->recipient_parrot_id);
    $sendingparrot = Parrot::get($invitation->sending_parrot_id);
    $user = $DB->get_record('user', ['id' => $sendingparrot->user_id], '*', MUST_EXIST);
    $page_url = new \moodle_url('/local/parrot_social/pages/parrot_profile.php', array('parrot_id' => $parrot->parrot_id));
    Notification::send(
        'friendrequestresponded',
        $user,
        get_string('friendship_request', 'local_parrot_social'),
        "<a href='{$page_url}'>{$parrot->name}</a> " . get_string($notificationmessage, 'local_parrot_social'),
        $page_url,
        get_string('check_it_out', 'local_parrot_social')
    );
    return [
        'invitation' => $invitation,
        'friendship_exists' => $friendship_exists,
        'friendship' => $friendship,
    ];
}

function invite_parrot() {
    global $DB, $OUTPUT;
    $form = new InvitationForm();
    $data = (array) $form->get_data();
    $invitation = new Invitation($data);
    $invitation->save();
    $parrot = Parrot::get($data['recipient_parrot_id']);
    $user = $DB->get_record('user', ['id' => $parrot->user_id], '*', MUST_EXIST);
    
    $sending_parrot = Parrot::get($data['sending_parrot_id']);
    $page_url = new \moodle_url('/local/parrot_social/pages/parrot_friendship_requests.php', array('parrot_id' => $parrot->parrot_id));
    $profile_url = new \moodle_url('/local/parrot_social/pages/parrot_profile.php', array('parrot_id' => $sending_parrot->parrot_id));
    Notification::send(
        'friendrequest',
        $user,
        get_string('friendship_request', 'local_parrot_social'),
        $OUTPUT->render_from_template(
            'local_parrot_social/messages/received_friend_request',
            array(
                'parrot' => $parrot,
                'sending_parrot' => $sending_parrot,
                'profile' => $profile_url,
                'parrot_friendship_requests_page' => $page_url,
            )
        ),
        $page_url,
        get_string('check_it_out', 'local_parrot_social')
    );
    return [
        'invitation' => $invitation,
    ];
}

function update_parrot() {
    $form = new UpdateParrotForm();
    $data = (array) $form->get_data();
    // TODO validate data

    if ($data['parrot_id'] && $parrot = Parrot::get($data['parrot_id'])) {
        $parrot = new Parrot((array)$parrot);
        $data['dataformat'] = $data['description']['format'];
        $data['description'] = $data['description']['text'];

        $draft_item_id = file_get_submitted_draft_itemid('picture');
        $context = empty($context) ? \context_system::instance() : $context;
        $filearea = 'profiles'; // replace this with your file area

        // save any ploaded file
        file_save_draft_area_files(
            // The id of the draft file area.
            $draft_item_id,

            // The combination of contextid / component / filearea / itemid
            // form the virtual bucket that file are stored in.
            $context->id,
            'local_parrot_social',
            $filearea,
            $draft_item_id,

            // The options to pass.
            [
                'subdirs' => false,
                'maxfiles' => 1,
                'accepted_types' => ['image'],
                // 'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            ],
        );


        // Check if there are any uploaded files
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_parrot_social', $filearea, $draft_item_id, 'id', false);
        if (!empty($files)) {
            // There are uploaded files
            $data['picture_id'] = $draft_item_id;
        } else {
            // There are no uploaded files
            $data['picture_id'] = null;
        }

        $parrot->fill($data);
        $parrot->save();
        $parrot = Parrot::get($parrot->parrot_id);
    } else {
        // throw not found error
        throw new \moodle_exception("Parrot with parrot_id {$data['parrot_id']} not found", "local_parrot_social");
    }

    return array(
        'parrot' => $parrot,
    );
}

function cancel_friendship() {
    global $parrot_id;

    $friend_id = required_param('friend_id', PARAM_TEXT);
    $friendship = Friendship::all([
        'parrot1_id' => $parrot_id,
        'parrot2_id' => $friend_id
    ]) ?: Friendship::all(
        [
            'parrot1_id' => $friend_id,
            'parrot2_id' => $parrot_id
        ]
    );
    $friendship = $friendship[0];
    $friendship->delete();
    return [
        'friendship' => $friendship,
    ];
}
