<?php

namespace local_parrot_social\Utils;

use moodle_url;

class Media {
    /**
     * Get all media for a given parrot 
     */
    public static function all($parrotid, $contextid = null) {
        $contextid = empty($contextid) ? \context_system::instance()->id : $contextid;
        $fs = get_file_storage();

        // Returns an array of `stored_file` instances.
        $files = array_filter(
            array_values($fs->get_area_files($contextid, 'local_parrot_social', 'media', $parrotid, $sort = "timemodified, filename")),
            function ($file) {
                return $file->get_filesize() > 0;
            }
        );
        $images = array_map(function ($file) {
            return (object) [
                'url' => moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    false                     // Do not force download of the file.
                )->out(false),
            ];
        }, array_values($files));
        return $images;
    }
}
