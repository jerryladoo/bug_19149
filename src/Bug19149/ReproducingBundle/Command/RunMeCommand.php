<?php
/**
 * This file is Copyright (c) Ladoo Pty Ltd.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Bug19149\ReproducingBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportStarterCommand
 * @package Ladoo\Brolly\ExportBundle\Command
 */
class RunMeCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("try:me");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('The config settings will only be outputted once because of cache.');
        $output->writeln('If you want to see it again, you can either run: php app/console cache:clear, or delete dev folder before executing php app/console try:me');
    }
}