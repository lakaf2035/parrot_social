<?php

namespace local_parrot_social\Models;

defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . '/../../../../config.php');

use local_parrot_social\Utils\Str;

class Base {
    /**
     * Model's table name
     * @var string
     */
    protected static $table = '';

    /**
     * Model's primary key
     */
    protected static $primary = 'id';

    /**
     * Model's attributes as associative array of name => default value
     * @var string|callable[]
     */
    protected $attributes = array();

    /**
     * Model's original data as received at creation as associative array of name => value
     * @var array
     */
    protected $original_data = array();

    /**
     * Model's data as associative array of name => value
     * @var array
     */
    public $data = array();


    public function __construct($data = array()) {
        $this->original_data = $data;
        $this->data = $data;
    }

    /**
     * merge given data with defaults
     * @param array $originalData
     * @return array
     */
    protected function merge($originalData) {
        $attributes = $this->getAttributes();
        foreach ($attributes as $attribute => $default) {
            $default_value = is_callable($default) ? $default($this) : $default;
            $attributes[$attribute] = $originalData[$attribute] ?? $default_value;
        }
        return $attributes;
    }

    /**
     * Get table name for model
     */
    public static function getTableName() {
        return static::$table ? static::$table : Str::camelToSnake(get_called_class());
    }


    /**
     * Get model's attributes
     * @return array
     */
    public function getAttributes() {
        return $this->attributes;
    }


    /**
     * Get model by id
     * @param mixed $id
     * @return static|null
     */
    public static function get($id) {
        return static::first(array(static::$primary => $id));
    }


    /**
     * Get first model satisfying conditions
     * @param mixed $id
     * @return static|null
     */
    public static function first(array $conditions = []) {
        global $DB;
        $data = $DB->get_record(static::getTableName(), $conditions);
        if (empty($data)) {
            return null;
        }
        return new static((array)$data);
    }

    /**
     * Delete model by id, defaults to current model
     * @param mixed $id
     * @return bool
     */
    public function delete($id = null) {
        global $DB;
        return $DB->delete_records(static::getTableName(), array(static::$primary => $id ?? $this->{static::$primary}));
    }

    /**
     * Get all model records from limit_from to limit_num
     * @param array $conditions
     * @param string $sort
     * @param string $fields
     * @param int $limit_from
     * @param int $limit_num
     * @return array(static)
     */
    public  static function all(array $conditions = null, $sort = '', $fields = '*', $limit_from = 0, $limit_num = 0) {
        global $DB;
        $data = $DB->get_records(static::getTableName(), $conditions, $sort, $fields, $limit_from, $limit_num);
        $result = array();
        foreach ($data as $item) {
            $result[] = new static((array)$item);
        }
        return $result;
    }

    public function __get($name) {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        return null;
    }

    public function __set($key, $value) {
        if (array_key_exists($key, $this->data)) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Fill model with new data from associative array
     * @param array $data
     */
    public function fill(array $data) {
        // merge with existing data
        $this->data = array_merge($this->data, $data);
    }

    /**
     * Save current model
     * @return bool
     */
    public function save(): bool {
        global $DB;

        $ok = false;
        $data = $this->merge($this->data);
        if (empty($data[static::$primary])) {
            $ok = $data[static::$primary] = $DB->insert_record(static::getTableName(), (object)$data);
        } else {
            $data['id'] = $data[static::$primary];
            $ok = $DB->update_record(static::getTableName(), (object)$data);
        }

        $this->data = $data;
        return (bool)$ok;
    }
}
// $DB->insert_record(static::getTableName(), (object)['recipient_parrot_id' => "180papagei1", 'sending_parrot_id' => "364papagei3", 'status' => "pending", 'created_at' => 1676972092]);