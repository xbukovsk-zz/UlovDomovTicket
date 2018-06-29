<?php
/**
 * Created by PhpStorm.
 * User: vaclav
 * Date: 29.6.18
 * Time: 8:41
 */

namespace Models;


class VillageModel
{

    /**
     * @var \Nette\Database\Connection
     */
    private $db;


    public function __construct(
        \Nette\Database\Connection $db
    )
    {
        $this->db = $db;
    }


    public function get()
    {
        return $this->db->fetchAll("SELECT * FROM village");
    }
}