<?php



defined('MOODLE_INTERNAL') || die;

class ParrotImage  {
    public $id;
    public $parrot_id;
    public $filename;
    public $createdat;

    public function __construct($id, $parrot_id, $filename, $createdat) {
        $this->id = $id;
        $this->parrot_id = $parrot_id;
        $this->filename = $filename;
        $this->createdat = $createdat;
    }

    // Save a new image to the database and the file system
    public static function create($parrot_id, $filename, $createdat) {
        global $DB, $CFG;

        $record = new stdClass();
        $record->parrot_id = $parrot_id;
        $record->filename = $filename;
        $record->createdat = $createdat;

        $id = $DB->insert_record('local_parrots_images', $record);
        
        // Move the uploaded file to the parrot's folder
        $src_path = $CFG->dirroot . '/local/parrot_social/images/tmp/' . $filename;
        $dst_dir = $CFG->dirroot . '/local/parrot_social/gallery' . $parrot_id;
        if (!file_exists($dst_dir)) {
            mkdir($dst_dir, 0777, true);
        }
        $dst_path = $dst_dir . '/' . $filename;
        rename($src_path, $dst_path);

        return new ParrotImage($id, $parrot_id, $filename, $createdat);
    }

    // Retrieve an image by its ID
    public static function get_by_id($id) {
        global $DB;

        $record = $DB->get_record('local_parrots_images', array('id' => $id));
        if (!$record) {
            return null;
        }

        return new ParrotImage($record->id, $record->parrot_id, $record->filename, $record->createdat);
    }

    // Retrieve all images for a parrot
    public static function get_by_parrot_id($parrot_id) {
        global $DB;

        $records = $DB->get_records('local_parrots_images', array('parrot_id' => $parrot_id), 'id ASC');
        $images = array();
        foreach ($records as $record) {
            $images[] = new ParrotImage($record->id, $record->parrot_id, $record->filename, $record->createdat);
        }

        return $images;
    }

    // Delete an image from the database and the file system
    public function delete() {
        global $DB, $CFG;

        // Delete the image file from the file system
        $filepath = $CFG->dirroot . '/local/parrot_social/images/parrots/' . $this->parrot_id . '/' . $this->filename;
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Delete the image record from the database
        $DB->delete_records('local_parrots_images', array('id' => $this->id));
    }

    // Update the image createdat in the database
    public function update_createdat($createdat) {
        global $DB;

        $record = new stdClass();
        $record->id = $this->id;
        $record->createdat = $createdat;

        $DB->update_record('local_parrots_images', $record);
        $this->createdat = $createdat;
    }
}

?>
