<?php
/**
 * Base Model providing database access
 */
abstract class BaseModel {
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }
}
