<?php

declare(strict_types=1);

namespace KayStrobach\Dyncss\Command;

use KayStrobach\Dyncss\Service\DyncssService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * vendor/bin/typo3 dyncss:compile EXT:my_ext/Resources/Public/Css/style.less
 */
class CompileCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setDescription('Compiles a dynamic stylesheet (less, scss, ...) and prints the path of the CSS file')
            ->addArgument('file', InputArgument::REQUIRED, 'file to compile, absolute, relative to the public folder or EXT:');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $file = (string)$input->getArgument('file');
        $absoluteFile = GeneralUtility::getFileAbsFileName($file) ?: $file;
        if (!is_file($absoluteFile)) {
            $output->writeln('<error>' . $file . ' not found</error>');
            return Command::FAILURE;
        }
        $output->writeln(DyncssService::getCompiledFile($absoluteFile));
        return Command::SUCCESS;
    }
}
