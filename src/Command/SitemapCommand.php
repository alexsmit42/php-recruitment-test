<?php

namespace Snowdog\DevTest\Command;

use AlexSmith\SitemapParser\SitemapParser;
use Snowdog\DevTest\Model\PageManager;
use Snowdog\DevTest\Model\UserManager;
use Snowdog\DevTest\Model\WebsiteManager;
use Symfony\Component\Console\Output\OutputInterface;

class SitemapCommand {

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

    public function __invoke($login, $name, $file, OutputInterface $output)
    {
        $user = $this->userManager->getByLogin($login);

        if (!$user) {
            $output->writeln('<error>User with login ' . $login . ' does not exist!</error>');
            return;
        }

        $filename = __DIR__.'/../../files/'.$file;
        $parser = new SitemapParser();
        $websiteInfo = $parser->parseFile($filename);

        if (!$websiteInfo || !isset($websiteInfo['website'])) {
            $output->writeln('<error>File is not exists or no valid!</error>');
            return;
        }

        $websiteId = $this->websiteManager->create($user, $name, $websiteInfo['website']);

        if (!$websiteId) {
            $output->writeln('<error>Website already exists!</error>');
        }

        $website = $this->websiteManager->getById($websiteId);

        $pages = $websiteInfo['pages'];
        foreach ($pages as $page) {
            $this->pageManager->create($website, $page);
        }

        $output->writeln('<error>All pages was added!</error>');
    }
}