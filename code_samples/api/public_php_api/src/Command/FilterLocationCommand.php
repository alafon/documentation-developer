<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Filter\Filter;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\API\Repository\Values\Content\Query\SortClause;

class FilterLocationCommand extends Command
{
    private $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
        parent::__construct('doc:filter_location');
    }

    public function configure()
    {
        $this->setDescription('Returns children of the provided Location, sorted by name in descending order.');
        $this->setDefinition([
            new InputArgument('parentLocationId', InputArgument::REQUIRED, 'ID of the parent Location')
        ]);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $parentLocationId = (int)$input->getArgument('parentLocationId');

        $filter = new Filter();
        $filter
            ->withCriterion(new Criterion\ParentLocationId($parentLocationId))
            ->withSortClause(new SortClause\ContentName(Query::SORT_DESC));

        $result = $this->locationService->find($filter, []);

        $output->writeln('Found ' . $result->totalCount . ' items');

        foreach ($result as $content) {
            $output->writeln($content->getContent()->getName());
        }

        return self::SUCCESS;
    }
}