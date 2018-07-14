<?php

namespace Snowdog\DevTest\Migration;

use Snowdog\DevTest\Core\Database;
use Snowdog\DevTest\Model\VarnishManager;
use Snowdog\DevTest\Model\WebsiteManager;
use Snowdog\DevTest\Model\UserManager;

class Version3
{
    /**
     * @var Database|\PDO
     */
    private $database;
    /**
     * @var VarnishManager
     */
    private $varnishManager;
    /**
     * @var WebsiteManager
     */
    private $websiteManager;
    /**
     * @var UserManager
     */
    private $userManager;

    public function __construct(
        Database $database,
        VarnishManager $varnishManager,
        WebsiteManager $websiteManager,
        UserManager $userManager
    ) {
        $this->database = $database;
        $this->varnishManager = $varnishManager;
        $this->websiteManager = $websiteManager;
        $this->userManager = $userManager;
    }

    public function __invoke()
    {
        $this->createVarnishTable();
        $this->createVarnishWebsiteTable();

        $this->addVarnishData();
    }

    private function createVarnishTable()
    {
        $createQuery = <<<SQL
CREATE TABLE `varnishes` (
  `varnish_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ip` varchar(40) NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`varnish_id`),
  KEY `user_id` (`user_id`),
  UNIQUE KEY `ip` (`ip`),
  CONSTRAINT `varnish_user_fk` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->database->exec($createQuery);
    }

    private function createVarnishWebsiteTable()
    {
        $createQuery = <<<SQL
CREATE TABLE `varnish_website` (
  `vw_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `varnish_id` int(11) unsigned NOT NULL,
  `website_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`vw_id`),
  CONSTRAINT `vw_varnish_fk` FOREIGN KEY (`varnish_id`) REFERENCES `varnishes` (`varnish_id`),
  CONSTRAINT `vw_website_fk` FOREIGN KEY (`website_id`) REFERENCES `websites` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
        $this->database->exec($createQuery);
    }

    private function addVarnishData()
    {
        $testUser = $this->userManager->getByLogin('test');
        $testIps = ['1.2.3.4', '33.44.55.66', '120.120.11.11'];
        $websites = $this->websiteManager->getAllByUser($testUser);

        $countIps = count($testIps);
        for ($i = 0; $i < $countIps; $i++) {
            $varnishId = $this->varnishManager->create($testUser, $testIps[$i]);
            if (!$varnishId) {
                continue;
            }

            $varnish = $this->varnishManager->getById($varnishId);

            if (!isset($websites[$i])) {
                continue;
            }

            $this->varnishManager->link($varnish, $websites[$i]);
        }
    }
}