<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\PermissionResolver;

class UpdateContentCommand extends Command
{
    private $contentService;

    private $userService;

    private $permissionResolver;

    public function __construct(ContentService $contentService, UserService $userService, PermissionResolver $permissionResolver)
    {
        $this->contentService = $contentService;
        $this->userService = $userService;
        $this->permissionResolver = $permissionResolver;
        parent::__construct('doc:update_content');
    }

    protected function configure()
    {
        $this
            ->setDescription('Update provided Content item with a new name')
            ->setDefinition([
                new InputArgument('contentId', InputArgument::REQUIRED, 'Content ID'),
                new InputArgument('newName', InputArgument::REQUIRED, 'New name for the updated Content item')
            ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->userService->loadUserByLogin('admin');
        $this->permissionResolver->setCurrentUserReference($user);

        $contentId = $input->getArgument('contentId');
        $newName = $input->getArgument('newName');

        $contentInfo = $this->contentService->loadContentInfo($contentId);
        $contentDraft = $this->contentService->createContentDraft($contentInfo);

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = 'eng-GB';
        $contentUpdateStruct->setField('name', $newName);

        $contentDraft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
        $this->contentService->publishVersion($contentDraft->versionInfo);

        $output->writeln('Content item ' . $contentId . ' updated with new name: ' . $newName);

        return self::SUCCESS;
    }
}