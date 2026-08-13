<?php
/**
 * BaseModel — Natal Solidário
 * Fornece acesso ao PDO via Database::getInstance().
 * Todos os Models que extendem esta classe recebem $this->db pronto.
 */
abstract class BaseModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }
}
