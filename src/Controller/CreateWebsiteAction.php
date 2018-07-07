<?php

namespace Snowdog\DevTest\Controller;

use AlexSmith\SitemapParser\SitemapParser;
use Snowdog\DevTest\Model\PageManager;
use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\WebsiteManager;

class CreateWebsiteAction
{
    /**
     * @var PageManager
     */
    private $pageManager;

    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    public function __construct(PageManager $pageManager, UserManager $userManager, WebsiteManager $websiteManager)
    {
        $this->pageManager = $pageManager;
        $this->userManager = $userManager;
        $this->websiteManager = $websiteManager;
    }

    public function execute()
    {
        $name = $_POST['name'];
        $hostname = $_POST['hostname'];

        $pages = [];
        if ($_FILES['sitemap']) {
            $parser = new SitemapParser();
            $websiteInfo = $parser->parseFile($_FILES['sitemap']['tmp_name']);
            if ($websiteInfo) {
                $pages = $websiteInfo['pages'];
            }
        }

        if(!empty($name) && !empty($hostname)) {
            if (isset($_SESSION['login'])) {
                $user = $this->userManager->getByLogin($_SESSION['login']);
                if ($user) {
                    $websiteId = $this->websiteManager->create($user, $name, $hostname);
                    $website = $this->websiteManager->getById($websiteId);

                    if ($pages) {
                        foreach ($pages as $page) {
                            $this->pageManager->create($website, $page);
                        }
                    }

                    if ($this->websiteManager->create($user, $name, $hostname)) {
                        $_SESSION['flash'] = 'Website ' . $name . ' added!';
                    }
                }
            }
        } else {
            $_SESSION['flash'] = 'Name and Hostname cannot be empty!';
        }

        header('Location: /');
    }
}