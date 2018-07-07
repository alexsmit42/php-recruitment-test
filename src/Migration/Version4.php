<?php

namespace Snowdog\DevTest\Migration;

use Snowdog\DevTest\Core\Database;
use Snowdog\DevTest\Model\PageManager;

class Version4
{
    /**
     * @var Database|\PDO
     */
    private $database;
    /**
     * @var PageManager
     */
    private $pageManager;

    public function __construct(
        Database $database,
        PageManager $pageManager
    ) {
        $this->database = $database;
        $this->pageManager = $pageManager;
    }

    public function __invoke()
    {
        $this->alterTable();
    }

    private function alterTable() {
        $alterQuery = <<<SQL
ALTER TABLE `pages`
  ADD COLUMN `total_visits` INT NOT NULL DEFAULT 1;
SQL;
        $this->database->exec($alterQuery);
    }
}