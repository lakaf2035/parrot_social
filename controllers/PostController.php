<?php

require_once(__DIR__ . '/../../../config.php');

// namespace
defined('MOODLE_INTERNAL') || die;

// set system context
$context = \context_system::instance();
$PAGE->set_context($context);

// get logged user
require_login();
#create post from moodle form data

use local_parrot_social\forms\CreatePostForm;
use local_parrot_social\forms\DeletePostForm;
use local_parrot_social\forms\LikePostForm;
use local_parrot_social\forms\UpdatePostForm;
use local_parrot_social\Models\Like;
use local_parrot_social\Models\Post;

$action = optional_param('action', 0, PARAM_TEXT);
if ($action && function_exists($action)) {
    $parrot_id = required_param('current_parrot', PARAM_TEXT);
    // TODO handle case when parrot_id is NULL
    $response = $action();
    echo json_encode($response);
    // return redirect(new \moodle_url('/local/parrot_social/pages/parrot_profile.php', array('parrot_id' => $parrot_id)));
} else {
    throw new \moodle_exception('Invalid action', 'local_parrot_social');
}

function save_post_files($text, $context = null) {
    $draft_id_editor = file_get_submitted_draft_itemid('text');
    $context = empty($context) ? \context_system::instance() : $context;
    $text = file_save_draft_area_files(
        // The id of the draft file area.
        $draft_id_editor,

        // The combination of contextid / component / filearea / itemid
        // form the virtual bucket that file are stored in.
        $context->id,
        'local_parrot_social',
        'posts',
        0,

        // The options to pass.
        [
            'subdirs' => false,
        ],

        // The text received from the form.
        $text
    );
    return $text;
}


function create_post() {
    $form = new CreatePostForm();
    $data = (array) $form->get_data();
    $data['text'] = $data['text']['text'];
    $data['text'] = save_post_files($data['text']);
    // TODO validate data

    $post = new Post($data);
    $post->save();

    return array(
        'post' => $post,
    );
}


function update_post() {
    $form = new UpdatePostForm();
    $data = (array) $form->get_data();
    // TODO validate data

    if ($data['id'] && $post = Post::get($data['id'])) {
        $post->title = $data['title'];
        $post->text = $data['text']['text'];
        $post->text = save_post_files($post->text);
        // $post = new Post($data);
        $post->save();
    } else {
        // throw not found error
        throw new \moodle_exception("Post with id {$data['id']} not found", "local_parrot_social");
    }

    return array(
        'post' => $post->data,
    );
}


function delete_post() {
    $form = new DeletePostForm();
    $data = (array) $form->get_data();
    // TODO validate data

    if ($data['id'] && $post = Post::get($data['id'])) {
        $deleted = $post->delete();
    } else {
        // throw not found error
        throw new \moodle_exception("Post with id {$data['id']} not found", 'local_parrot_social');
    }

    return array(
        'deleted' => !!$deleted,
    );
}


function like_post() {
    $form = new LikePostForm();
    $data = (array) $form->get_data();
    // TODO validate data

    // retrieve like with given post_id and parrot_id if it exists
    $like = Like::first(array('post_id' => $data['post_id'], 'parrot_id' => $data['parrot_id']));
    if ($like) {
        // unliking, delete like
        $like->delete();
    } else {
        // liking create like
        $new_like = new Like($data);
        $new_like->save();
    }
    return array(
        'liked' => empty($like),
        'like' => $like || $new_like,
    );
}
