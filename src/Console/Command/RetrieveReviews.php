<?php

declare(strict_types=1);

namespace Elgentos\Kiyoh\Console\Command;

use Elgentos\Kiyoh\Service\RetrieveKiyohReviews;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RetrieveReviews extends Command {

    const COMMAND_NAME = 'elgentos:kiyoh:retrieve';
    const COMMAND_DESCRIPTION = 'Retrieving Kiyoh reviews through the Publishing API';
    private RetrieveKiyohReviews $service;

    public function __construct(
        RetrieveKiyohReviews $service,
        string $name = null
    )
    {
        parent::__construct($name ?? self::COMMAND_NAME);
        $this->service = $service;
    }

    public function configure(): void
    {
        $this->setDescription(self::COMMAND_DESCRIPTION);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->service->execute();
        } catch (LocalizedException $e) {
            $errorOutput = $output instanceof ConsoleOutputInterface
                ? $output->getErrorOutput()
                : $output;

            $errorOutput->write($e->getMessage());
            return Cli::RETURN_FAILURE;
        }

        return Cli::RETURN_SUCCESS;
    }

}
