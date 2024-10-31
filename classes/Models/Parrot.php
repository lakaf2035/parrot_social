<?php

namespace local_parrot_social\Models;

use context_system;
use moodle_url;

defined('MOODLE_INTERNAL') || die;

class Parrot extends Base {
    protected static $table = 'parrot_social_parrots';

    const PARROT_SELECT_QUERY = "
        SELECT
            parrots.id AS id,
            CONCAT(`names`.user_id, `names`.shortname) AS parrot_id, 
            CONCAT('/', files.contextid, '/', files.component, '/', files.filearea, '/', files.itemid, '/', files.filename) AS picture_url,  
            `name`, 
            `description`, 
            parrots.picture_id AS picture_id, 
            parrots.custom_attribute AS custom_attribute, 
            `names`.user_id AS user_id, `names`.shortname AS shortname, 
            `names`.id AS name_id, `names`.field_id AS name_field_id, 
            `descriptions`.id AS description_id, 
            `descriptions`.field_id AS description_field_id
        FROM 
                (SELECT shorts.shortname, datas.userid AS user_id, datas.data AS `name`, RIGHT(shortname,1) AS `index`, datas.id AS id, datas.fieldid AS field_id FROM {user_info_field} AS shorts JOIN  {user_info_data} AS datas ON shorts.id = datas.fieldid WHERE shorts.shortname LIKE 'papagei_') AS `names` 
            LEFT JOIN 
                (SELECT shorts.shortname, datas.userid AS user_id, datas.data AS `description`, RIGHT(shortname,1) AS `index`, datas.id AS id, datas.fieldid AS field_id FROM {user_info_field} AS shorts JOIN  {user_info_data} AS datas ON shorts.id = datas.fieldid WHERE shorts.shortname LIKE 'Papageien_Info_Text_') AS `descriptions`
            ON 
                `names`.user_id = `descriptions`.user_id AND `names`.`index` = `descriptions`.`index`
            LEFT JOIN 
                {parrot_social_parrots} AS parrots
            ON 
                CONCAT(`names`.user_id, `names`.shortname) = parrots.parrot_id
            LEFT JOIN
                {files} AS files
            ON
                parrots.picture_id = files.id
    ";

    public static function getParrotsByUser($user_id) {
        global $DB;
        $parrot_select_query = static::PARROT_SELECT_QUERY . "
            WHERE 
                `names`.user_id = :user_id
                AND `name` IS NOT NULL
                AND `name` <> ''
        ";
        $parrots = self::addCustomAttributes($DB->get_records_sql($parrot_select_query, array('user_id' => $user_id)));
        return $parrots;
    }

    public function getCurrentUserParrots() {
        global $USER;
        return $this->getParrotsByUser($USER->id);
    }

    public static function getPosts($parrot_id) {
        global $DB;
        $post_select_query = "
            SELECT 
                posts.id, posts.title, posts.text, COUNT(likes.id) AS likes_count
            FROM 
                    {parrot_social_posts} AS posts
                LEFT JOIN
                    {parrot_social_likes} AS likes
                ON 
                    posts.id = likes.post_id
            WHERE 
                posts.parrot_id = :parrot_id
            GROUP BY 
                posts.id
            ORDER BY 
                `posts`.created_at DESC
        ";
        $posts = $DB->get_records_sql($post_select_query, array('parrot_id' => $parrot_id));
        return $posts;
    }

    // get friends
    public static function getFriends($parrot_id) {
        global $DB;
        $parrot_select_query = static::PARROT_SELECT_QUERY . "
            WHERE 
                CONCAT(`names`.user_id, `names`.shortname) IN (
                    SELECT 
                        parrot1_id
                    FROM 
                        {parrot_social_friends}
                    WHERE 
                        parrot2_id = ?
                )
                OR CONCAT(`names`.user_id, `names`.shortname) IN (
                    SELECT 
                        parrot2_id
                    FROM 
                        {parrot_social_friends}
                    WHERE 
                        parrot1_id = ?
                )
        ";
        $parrots = $DB->get_records_sql($parrot_select_query, array($parrot_id, $parrot_id));
        $parrots = self::addCustomAttributes($parrots ? $parrots : []);
        return $parrots;
    }

    public static function get($parrot_id) {
        global $DB;
        $parrot_select_query = static::PARROT_SELECT_QUERY . "
            WHERE 
                CONCAT(`names`.user_id, `names`.shortname) = :parrot_id
        ";
        $parrot = self::addCustomAttributes([$DB->get_record_sql($parrot_select_query, array('parrot_id' => $parrot_id))])[0];
        return $parrot;
    }

    public function getAttributes() {
        return array(
            'id' => NULL,
            'parrot_id' => NULL,
            'picture_id' => NULL,
            'custom_attribute' => '',
        );
    }

    public function save(): bool {
        global $DB;

        // first part update name and/or create description FieldData

        // name must exist so we just update
        $name = FieldData::get($this->name_id);
        $name->data['data'] = $this->name;
        $ok = $name->save();

        if ($this->description_id) {
            $description = FieldData::get($this->description_id);
            // TODO if not found logic
            $description->data['data'] = $this->description;
        } else {
            $description = new FieldData([
                'data' => $this->description ?? '',
                'fieldid' => $this->description_field_id,
                'userid' => $this->user_id,
                'dataformat' => 1,
            ]);
        }
        $ok &= $description->save();

        return parent::save();

        // $data = $this->merge($this->data);
        // $table_name = static::getTableName();
        // if (
        //     empty($data[static::$primary])
        //     || !$DB->record_exists(
        //         static::getTableName(),
        //         array(static::$primary => $data[static::$primary])
        //     )
        // ) {
        //     // build insert query
        //     $fields = array();
        //     $values = array();
        //     foreach ($data as $key => $value) {
        //         $fields[] = $key;
        //         $values[] = '?';
        //     }
        //     $fields = implode(', ', $fields);
        //     $values = implode(', ', $values);
        //     $ok &= $DB->execute("INSERT INTO {{$table_name}} ({$fields}) VALUES ({$values})", $data);
        // } else {
        //     $primary = static::$primary;
        //     # build update query from $data
        //     $update = array();
        //     foreach ($data as $key => $value) {
        //         $update[] = $key . ' = ?';
        //     }
        //     $data = array_merge($data, array($data[$primary]));
        //     $update = implode(', ', $update);
        //     $ok &= $DB->execute("UPDATE {{$table_name}} SET {$update} WHERE {$primary} = ?", $data);
        // }

        // return $ok;
    }

    public static function addCustomAttributes(array $parrots) {
        foreach ($parrots as $parrot) {
            $fs = get_file_storage();
            $files = $fs->get_area_files(context_system::instance()->id, 'local_parrot_social', 'profiles', $parrot->picture_id, 'id', false);
            $file = array_shift($files);
            $parrot->picture_url = empty($file) ? null : moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename())->out(false);
        }
        return $parrots;
    }
}
