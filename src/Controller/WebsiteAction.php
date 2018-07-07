<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Model\PageManager;
use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\Website;
use Snowdog\DevTest\Model\VarnishManager;
use Snowdog\DevTest\Model\WebsiteManager;

class WebsiteAction
{

    /**
     * @var VarnishManager
     */
    private $varnishManager;

    /**
     * @var WebsiteManager
     */
    private $websiteManager;
    /**
     * @var PageManager
     */
    private $pageManager;
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var Website
     */
    private $website;

    /**
     * @var User
     */
    private $user;


    public function __construct(UserManager $userManager, VarnishManager $varnishManager, WebsiteManager $websiteManager, PageManager $pageManager)
    {
        $this->varnishManager = $varnishManager;
        $this->websiteManager = $websiteManager;
        $this->pageManager = $pageManager;
        $this->userManager = $userManager;
    }

    public function execute($id)
    {
        if (isset($_SESSION['login'])) {
            $user = $this->userManager->getByLogin($_SESSION['login']);

            $website = $this->websiteManager->getById($id);

            if ($website->getUserId() == $user->getUserId()) {
                $this->website = $website;
                $this->user = $user;
            }
        }

        require __DIR__ . '/../view/website.phtml';
    }

    protected function getPages()
    {
        if($this->website) {
            return $this->pageManager->getAllByWebsite($this->website);
        } 
        return [];
    }
}