<?php

namespace Snowdog\DevTest\Controller;

use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\Varnish;
use Snowdog\DevTest\Model\VarnishManager;
use Snowdog\DevTest\Model\Website;
use Snowdog\DevTest\Model\WebsiteManager;

class CreateVarnishLinkAction
{
    /**
     * @var UserManager
     */
    private $userManager;
    /**
     * @var VarnishManager
     */
    private $varnishManager;
    /**
     * @var WebsiteManager
     */
    private $websiteManager;

    public function __construct(UserManager $userManager, WebsiteManager $websiteManager, VarnishManager $varnishManager)
    {
        $this->userManager = $userManager;
        $this->varnishManager = $varnishManager;
        $this->websiteManager = $websiteManager;
    }

    public function execute()
    {
        $websiteId = $_POST['website'];
        $varnishId = $_POST['varnish'];
        $link = $_POST['link'];

        if (isset($_SESSION['login'])) {
            $user = $this->userManager->getByLogin($_SESSION['login']);
            $varnish = $this->varnishManager->getById($varnishId);
            $website = $this->websiteManager->getById($websiteId);

            if ($website->getUserId() == $user->getUserId() && $varnish->getUserId() == $user->getUserId()) {
                if (intval($link)) {
                    $this->varnishManager->link($varnish, $website);
                } else {
                    $this->varnishManager->unlink($varnish, $website);
                }
            }
        }
    }
}