<?php
namespace Task\ConsoleBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class TwitterCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('twitter:count-keywords')
            ->setDescription('Count the keywords from the last tweets for a specific twitter account name')
            ->addArgument(
                'accountName',
                InputArgument::REQUIRED,
                'The twitter account name where from to get the tweets'
            )
            ->addOption(
                'limit',
                null,
                InputOption::VALUE_OPTIONAL,
                'The limit of the tweets where from to extract the keywords.',
                100 //default value
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $response = false;
        $accountName = $input->getArgument('accountName');
        $limit = intval($input->getOption('limit'));
        $twitter = $this->getContainer()->get('twitter_service');

        $tweets = $twitter->getTweetsByScreenName($accountName, $limit);
        $error = $twitter->getError();

        //handle the twitter API error
        if (!empty($error)) {
            $response = '<error>' . $error . '</error>';
        } else {
            $tweetsKeywords = $twitter->countTweetsKeywords($tweets, true);
            $response = $this->formatKeywordsCounterOutput($tweetsKeywords);
        }

        $output->writeln($response);

    }

    protected function formatKeywordsCounterOutput($keywords)
    {
        $result = false;
        $keywordsArray = array();
        $countArray = array();
        //multiple sort of the keywords - DESC by count value and ASC by keyword value
        foreach ($keywords as $keyword => $count) {
            $keywordsArray[] = $keyword;
            $countArray[] = $count;
        }
        array_multisort($countArray, SORT_DESC, SORT_NUMERIC,
            $keywordsArray, SORT_STRING, SORT_ASC);

        //create the output string
        foreach ($countArray as $index => $count) {
            $result .= $keywordsArray[$index] . ', ' . $count . "\r\n";
        }

        return $result;
    }
}