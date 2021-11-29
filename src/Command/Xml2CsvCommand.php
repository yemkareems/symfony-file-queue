<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Port\Csv\CsvWriter;

use Psr\Log\LoggerInterface;

/**
 * A console command that creates users and stores them in the database.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console app:add-user
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console app:add-user -vv
 *
 * See https://symfony.com/doc/current/console.html
 * For more advanced uses, commands can be defined as services too. See
 * https://symfony.com/doc/current/console/commands_as_services.html
 *
 * @author Javier Eguiluz <javier.eguiluz@gmail.com>
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
class Xml2CsvCommand extends Command
{
    // to make your command lazily loaded, configure the $defaultName static property,
    // so it will be instantiated only when the command is actually called.
    protected static $defaultName = 'app:xml-csv';

    /**
     * @var SymfonyStyle
     */
    private $io;

    private $container;
    private $logger;

    public function __construct(LoggerInterface $logger, ParameterBagInterface $container)
    {
        parent::__construct();
        $this->logger = $logger;
        $this->container = $container;

    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('convert xml to csv')
            ->setHelp($this->getCommandHelp())
            // commands can optionally define arguments and/or options (mandatory and optional)
            // see https://symfony.com/doc/current/components/console/console_arguments.html
            ->addArgument('remotefile', InputArgument::OPTIONAL, 'whether fetch from remote or local')

        ;
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * This method is executed after initialize() and before execute(). Its purpose
     * is to check if some of the options/arguments are missing and interactively
     * ask the user for those values.
     *
     * This method is completely optional. If you are developing an internal console
     * command, you probably should not implement this method because it requires
     * quite a lot of work. However, if the command is meant to be used by external
     * users, this method is a nice way to fall back and prevent errors.
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getArgument('remotefile')) {
            return;
        }

        $this->io->title('Add User Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console app:xml-csv remotefile',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for the username if it's not defined
        $remotefile = $input->getArgument('remotefile');
        if (null !== $remotefile) {
            $this->io->text(' > <info>remotefile</info>: '.$remotefile);
        } else {
            $remotefile = $this->io->ask('remotefile', null);
            $input->setArgument('remotefile', $remotefile);
        }

    }

    /**
     * This method is executed after interact() and initialize(). It usually
     * contains the logic to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {

        $stopwatch = new Stopwatch();
        $this->logger->alert("Command xml2csv run");

        $stopwatch->start('xml-2-csv');

        $remotefile = $input->getArgument('remotefile');

        if(!in_array($remotefile, ['0','1'])){
            $this->logger->error("remote file is not 1 or 0. It is ".$remotefile);
            throw new RuntimeException(sprintf('Remote file should be either 1 or 0. 1 specifies fetch from remote'));
        }
        $xmlurl = $this->container->get('localfile');
        if($remotefile == '1'){
            $ftp_server = $this->container->get('ftp_server');
            $ftp_username = $this->container->get('ftp_username');
            $ftp_pass = $this->container->get('ftp_pass');
            $server_file = $this->container->get('server_file');
            $xmlurl = "ftp://$ftp_username:$ftp_pass@$ftp_server/$server_file";
        }
        $this->logger->alert("Loading file from ".$xmlurl);

        $xml = simplexml_load_file($xmlurl);

        if($xml == false){
            $error = "remote file is not found  ".$xmlurl;
            $this->logger->error($error);
            throw new RuntimeException($error);

        }
        $writer = new CsvWriter();
        $csvFile = 'data/coffee_feed-'.time().'-'.$remotefile.'.csv';
        $writer->setStream(fopen($csvFile, 'w'));
        $header = false;

        foreach($xml as $key => $value){
            if(!$header) {
                $writer->writeItem(array_keys(get_object_vars($value)));
                $header = true;
            }
            $writer->writeItem(array_values(get_object_vars($value)));
        }

        $writer->finish();
        $this->io->success(sprintf('%s file created successfully', $csvFile));
        $this->logger->alert(sprintf('%s file created successfully', $csvFile));


        $event = $stopwatch->stop('xml-2-csv');
        $this->io->comment(sprintf('File Created: %s / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $csvFile, $event->getDuration(), $event->getMemory() / (1024 ** 2)));

    }

    /**
     * The command help is usually included in the configure() method, but when
     * it's too long, it's better to define a separate method to maintain the
     * code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
The <info>%command.name%</info> command converts xml file data/coffee_feed.xml to csv

  <info>php %command.full_name%</info> <comment>remotefile</comment>

If remotefile is 0 the file is read from local
If remotefile is 1 the file is read from ftp

Here i am using CsvWriter. We can use DoctrineWriter to write to DB
HELP;
    }
}
