<?php
require_once(__DIR__ . '/../../../config.php');

require_once(__DIR__ . '/../classes/forms/parrot_image_form.php');
require_once(__DIR__ . '/../classes/Models/parrot_image.php');
require_once(__DIR__ . '/../classes/Utils/Media.php');

use local_parrot_social\forms\parrot_social_image_upload_form;
use local_parrot_social\Utils\Media;

$context = \context_system::instance();
$mform = new parrot_social_image_upload_form(null);
$data = (array)$mform->get_data();

$draft_item_id = file_get_submitted_draft_itemid('pictures');
$context = empty($context) ? \context_system::instance() : $context;
file_save_draft_area_files(
    // The id of the draft file area.
    $draft_item_id,

    // The combination of contextid / component / filearea / itemid
    // form the virtual bucket that file are stored in.
    $context->id,
    'local_parrot_social',
    'media',
    $data['picture_id'],

    // The options to pass.
    [
        'subdirs' => false,
        'maxfiles' => 1000,
        'accepted_types' => ['image'],
        // 'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
    ],
);

echo json_encode([
    'images' => Media::all($data['picture_id'], $context->id),
]);
