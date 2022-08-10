<?php
namespace Zero1\SearchQueryOptimization\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Zero1\SearchQueryOptimization\Model\Job\Decay as DecayJob;

class Decay extends Command
{
    /** @var DecayJob */
    protected $decay;

    public function __construct(
        DecayJob $decay,
        string $name = null
    ){
        $this->decay = $decay; 
        return parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('zero1:search-query-optimization:decay');
        $this->setDescription('Reduce the popularity of all search_query terms by 1, if they haven\'t been update for over a week. If popularity falls below zero remove.');
        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Running');
        try{
            $this->decay->execute();
        }catch(\Exception $e){
            $output->writeln('Error: '.$e->getMessage());
            return 1;
        }
        $output->writeln('Done');
        return 0;
    }
} 