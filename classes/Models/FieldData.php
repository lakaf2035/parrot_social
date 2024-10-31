<?php

namespace local_parrot_social\Models;

defined('MOODLE_INTERNAL') || die;

class FieldData extends Base
{
    protected static $table = 'user_info_data';
    protected static $primary = 'id';

    public function getAttributes()
    {
        return array(
            'id' => NULL,
            'userid' => NULL,
            'fieldid' => NULL,
            'data' => '',
            'dataformat' => 0,
        );
    }
}
