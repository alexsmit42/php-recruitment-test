<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Model\User;
use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\WebsiteManager;

class IndexAction
{

    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    /**
     * @var User
     */
    private $user;

    public function __construct(UserManager $userManager, WebsiteManager $websiteManager)
    {
        $this->websiteManager = $websiteManager;
        if (isset($_SESSION['login'])) {
            $this->user = $userManager->getByLogin($_SESSION['login']);
            $this->userInfo = $userManager->getUserInfo($this->user);
        }
    }

    protected function getWebsites()
    {
        if($this->user) {
            return $this->websiteManager->getAllByUser($this->user);
        } 
        return [];
    }

    protected function getUserInfo()
    {
        if($this->user) {
            return $this->userManager->getUserInfo($this->user);
        }
        return [];
    }

    protected function getServers()
    {
        if($this->user) {
            return $this->serverManager->getAllByUser($this->user);
        }
        return [];
    }

    public function execute()
    {
        require __DIR__ . '/../view/index.phtml';
    }
}