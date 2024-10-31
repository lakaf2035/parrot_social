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
use local_parrot_social\forms\InvitationForm;
use local_parrot_social\forms\UpdateParrotForm;
use local_parrot_social\forms\UpdatePostForm;
use local_parrot_social\Models\Invitation;
use local_parrot_social\Models\Parrot;
use local_parrot_social\Models\Post;
use local_parrot_social\forms\parrot_social_image_upload_form;
use local_parrot_social\Utils\Media;

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/../classes/Utils/Media.php');
// @todo remove the line below
require_once(dirname(__FILE__) . '/../classes/forms/parrot_image_form.php');

defined('MOODLE_INTERNAL') || die;

// set system context
$context = \context_system::instance();
$PAGE->set_context($context);

// get logged user
require_login();
global $USER, $DB;

//set page url
$parrot_id = required_param('parrot_id', PARAM_TEXT);
// insert in parrot table if not exist
$DB->record_exists('parrot_social_parrots', array('parrot_id' => $parrot_id)) ? null : $DB->insert_record('parrot_social_parrots', (object) ['parrot_id' => $parrot_id]);
$page_url = new \moodle_url('/local/parrot_social/pages/parrot_profile.php', array('parrot_id' => $parrot_id, 'post_id' => 0));
$PAGE->set_url($page_url);

$PAGE->requires->js('/local/parrot_social/assets/js/ajax.js', array('defer' => true));
// $PAGE->requires->js('/local/parrot_social/assets/js/ekko-lightbox.min.js', array('defer' => true));
// $PAGE->requires->js_call_amd('local_parrot_social/ajax', 'init');
$PAGE->requires->css('/local/parrot_social/assets/css/style.css');
$PAGE->requires->css('/local/parrot_social/assets/css/lightbox.css');

$title = get_string('parrot_media_title', 'local_parrot_social');
$PAGE->set_title($title);
$PAGE->set_heading("");
echo $OUTPUT->header();

// get current user's parrots to use in like and invite form
$viewer_parrots = Parrot::getParrotsByUser($USER->id);
// get parrot from id
$parrot_controller_url = new \moodle_url('/local/parrot_social/controllers/ParrotController.php', array('current_parrot' => $parrot_id));
$invitations_url = new \moodle_url('/local/parrot_social/pages/parrot_friendship_requests.php', array('parrot_id' => $parrot_id, 'post_id' => 0));
$parrot = Parrot::get($parrot_id);
$is_owner = $parrot->user_id === $USER->id;
$owner = $DB->get_record('user', ['id' => $parrot->user_id], '*', MUST_EXIST);
$owner->name = "{$owner->firstname} {$owner->lastname}";
$rs = $DB->get_record_select(
    "user",
    "id = '$owner->id'",
    null,
    \user_picture::fields()
);
$owner->picture_url = $OUTPUT->user_picture($rs, array('size' => 75, 'link' => true));

// update parrot form
$parrot_update_form = new UpdateParrotForm(
    $parrot_controller_url->out(),
    array('picture_id' => $parrot->picture_id)
);
$parrot_update_form->set_data($parrot);
$parrot_update_form->set_data(['description' => ['text' => $parrot->description, 'format' => FORMAT_HTML]]);

// invitation forms
$invitation_form = new InvitationForm($parrot_controller_url->out());
$invitation_form->set_data(['recipient_parrot_id' => $parrot_id]);
$received_invitations = Invitation::all(array('recipient_parrot_id' => $parrot_id, 'status' => Invitation::STATUS_PENDING));
$sender_ids = array_map(function ($invitation) {
    return  $invitation->sending_parrot_id;
}, Invitation::all(['recipient_parrot_id' => $parrot_id]));
$parrot->viewer_parrots = array_reduce($viewer_parrots, function ($acc, $parrot) use ($sender_ids, $invitation_form) {
    if (!in_array($parrot->parrot_id, $sender_ids)) {
        $invitation_form->set_data(['sending_parrot_id' => $parrot->parrot_id]);
        $acc[] = (object) array(
            'id' => $parrot->parrot_id,
            'name' => $parrot->name,
            'form_html' => $invitation_form->render(),
        );
    }
    return $acc;
}, []);
$parrot->invitable = ((!$is_owner) and (count($parrot->viewer_parrots) > 0));

// parrot friends
$parrot_friends = Parrot::getFriends($parrot_id);

// create post form
$post_controller_url = new \moodle_url('/local/parrot_social/controllers/PostController.php', array('current_parrot' => $parrot_id));
$form = new CreatePostForm(
    $post_controller_url->out(),
    array('parrot_id' => $parrot_id)
);
$post_form_html = $form->render();

// create delete post form
$delete_post_form = new DeletePostForm(
    $post_controller_url->out(),
);


// create like form
$like_form = new \local_parrot_social\forms\LikePostForm($post_controller_url->out());

// get posts
$posts = Parrot::getPosts($parrot_id);
// get post id from url and fetch post
$_post = null;
$post_id = optional_param('post_id', 0, PARAM_INT);
if ($post_id && $_post = Post::get($post_id)) {
    $_post->data['likes_count'] = count($_post->likes());
    $_post = (object) $_post->data;

    $_post->text = file_rewrite_pluginfile_urls(
        // The content of the text stored in the database.
        $_post->text,
        // The pluginfile URL which will serve the request.
        'pluginfile.php',

        // The combination of contextid / component / filearea / itemid
        // form the virtual bucket that file are stored in.
        $context->id,
        'local_parrot_social',
        'posts',
        0
    );

    $_post->was_liked = $_post->likes_count > 0;

    $_post->share_url = $page_url->out(false, array('post_id' => $_post->id));

    // create update post form
    $update_post_form = new UpdatePostForm(
        $post_controller_url->out(),
        array('parrot_id' => $parrot_id, 'id' => $_post->id)
    );
    $update_post_form->set_data($_post);
    $update_post_form->set_data(['text' => ['text' => $_post->text, 'format' => FORMAT_HTML]]);
    // $update_post_form->set_data(['id' => $post->id, 'title' => $post->title, 'text' => ['text' => $post->text, 'format' => FORMAT_HTML]]);
    $_post->update_form_html = $update_post_form->render();

    $delete_post_form->set_data($_post);
    $_post->delete_form_html = $delete_post_form->render();

    $_post->viewer_parrots = array_map(function ($parrot)  use ($like_form, $_post) {
        $like_form->set_data(array('post_id' => $_post->id, 'parrot_id' => $parrot->parrot_id));
        return (object) array(
            'form_html' => $like_form->render(),
            'name' => $parrot->name,
        );
    }, array_values($viewer_parrots));
    // array_unshift($posts, (object) $post->data);
}

$images = Media::all($parrot->id, $context->id);
$image_upload_controller_url = new \moodle_url('/local/parrot_social/controllers/UploadImageController.php', array('current_parrot' => $parrot_id));
$image_Upload_form = new parrot_social_image_upload_form(
    $image_upload_controller_url->out(),
    array('parrot_id' => $parrot_id, 'picture_id' => $parrot->id)
);
$image_Upload_form_html = $image_Upload_form->render();

// echo json_encode(Parrot::getPosts($parrot_id));
// print rest of the page
echo $OUTPUT->render_from_template('local_parrot_social/parrot_media', array(
    'media_page' => 'all_media',
    'owner' => $owner,
    'is_owner' => $is_owner,
    'parrot' => $parrot,
    'parrots' => array_values(array_map(function ($friend) {
        $friend->picture = $friend->picture_url;
        return $friend;
    }, $parrot_friends)),
    'invitations_url' => $invitations_url,
    'invitations_count' => count($received_invitations),
    'parrot_update_form_html' => $parrot_update_form->render(),
    // 'parrot_json' => json_encode($parrot),
    'picture_url' => $parrot->picture_url,
    'post_form_html' => $post_form_html,
    'image_Upload_form_html' => $image_Upload_form_html,
    'images' => $images,
    'post' => $_post ? $_post->data : null,
    'posts' => array_map(function ($post) use ($delete_post_form, $like_form, $viewer_parrots, $page_url, $_post, $post_controller_url, $parrot_id, $context) {
        $post->was_liked = $post->likes_count > 0;

        $post->text = file_rewrite_pluginfile_urls(
            // The content of the text stored in the database.
            $post->text,
            // The pluginfile URL which will serve the request.
            'pluginfile.php',

            // The combination of contextid / component / filearea / itemid
            // form the virtual bucket that file are stored in.
            $context->id,
            'local_parrot_social',
            'posts',
            0
        );

        $post->exists = $_post ? $_post->id === $post->id : false;

        $post->share_url = $page_url->out(false, array('post_id' => $post->id));

        // create update post form
        $update_post_form = new UpdatePostForm(
            $post_controller_url->out(),
            array('parrot_id' => $parrot_id, 'id' => $post->id)
        );
        $update_post_form->set_data($post);
        $update_post_form->set_data(['text' => ['text' => $post->text, 'format' => FORMAT_HTML]]);
        $post->update_form_html = $update_post_form->render();

        $delete_post_form->set_data($post);
        $post->delete_form_html = $delete_post_form->render();

        $post->viewer_parrots = array_map(function ($parrot)  use ($like_form, $post) {
            $like_form->set_data(array('post_id' => $post->id, 'parrot_id' => $parrot->parrot_id));
            return (object) array(
                'form_html' => $like_form->render(),
                'name' => $parrot->name,
            );
        }, array_values($viewer_parrots));

        return $post;
    }, array_values($posts)),
));

// print footer
echo $OUTPUT->footer();
