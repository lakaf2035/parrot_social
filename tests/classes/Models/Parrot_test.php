<?php // File: mod/myplugin/tests/complex_test.php


namespace local_parrot_social\Models;

defined('MOODLE_INTERNAL') || die;

// require_once(__DIR__ . '/../../../../config.php');

class PostTest extends \advanced_testcase
{
    public function test_create_parrot()
    {
        global $DB;

        $this->resetAfterTest(true);
        $this->setUser(2);

        $post_data = array(
            'parrot_id' => 'papagei1',
            'text' => 'This is a test post',
        );

        $post = new Post($post_data);
        $post->save();

        // that was not a good idea, let's go back
        $this->assertTrue(
            $DB->record_exists_select(
                (new Post())->getTableName(),
                $DB->sql_compare_text('text', 2000) . ' = ' . $DB->sql_compare_text(':text', 2000) . "AND parrot_id = :parrot_id",
                $post_data
            )
        );

        // $DB->delete_records('user', array()); // lets do something crazy

        // $this->resetAllData();    
    }
}
