<?php

// namespace local_parrot_social;

# TODO - change class path for calling classes by using "use" keyword

defined('MOODLE_INTERNAL') || die;

function local_parrot_social_myprofile_navigation(\core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $OUTPUT;
    $category = new \core_user\output\myprofile\category('parrots', get_string('category_heading', 'local_parrot_social'), 'contact', ' no-one-like-you');
    $tree->add_category($category);

    $string = get_string('profile_heading', 'local_parrot_social');
    $data = (object) [
        'user' => $user,
        'is_owner' => $iscurrentuser,
        'parrots' => array_values((new \local_parrot_social\Models\Parrot())->getParrotsByUser($user->id))
    ];
    $content = $OUTPUT->render_from_template('local_parrot_social/myparrots', $data);
    $node = new core_user\output\myprofile\node(
        'parrots',
        'my_parrots',
        $string,
        null,
        null,
        $content
    );
    $tree->add_node($node);
    return true;
}

// file_pluginfile
function local_parrot_social_pluginfile($course, $cm, $context, $file_area, $args, $force_download, array $options = array()) {
    if (in_array($file_area, ['profiles', 'posts', 'media'])) {
        $itemid = clean_param(array_shift($args), PARAM_INT);
        $filename = clean_param(array_shift($args), PARAM_FILE);

        // Find the original file.
        $fs = get_file_storage();
        if (!$file = $fs->get_file($context->id, 'local_parrot_social', $file_area, $itemid, '/', $filename)) {
            send_file_not_found();
        }
        send_stored_file($file, null, $itemid, $force_download, $options);
    }

    send_file_not_found();
}
