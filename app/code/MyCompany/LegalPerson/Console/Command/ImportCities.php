<?php
namespace MyCompany\LegalPerson\Console\Command;

use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Csv;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCities extends Command
{
    protected $resource;
    protected $csvProcessor;
    protected $regionFactory;
    protected $state;

    public function __construct(
        ResourceConnection $resource,
        Csv $csvProcessor,
        RegionFactory $regionFactory,
        State $state
    ) {
        $this->resource = $resource;
        $this->csvProcessor = $csvProcessor;
        $this->regionFactory = $regionFactory;
        $this->state = $state;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('legalperson:import:cities')
            ->setDescription('Import Romanian cities from CSV into ro_localities table.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_GLOBAL);

            $output->writeln('<info>Starting import...</info>');

            $filePath = BP . '/pub/media/ro_localities.csv';

            if (!file_exists($filePath)) {
                $output->writeln('<error>File not found at: ' . $filePath . '</error>');
                return Cli::RETURN_FAILURE;
            }

            $regions = $this->getRoRegions();
            $output->writeln('Loaded ' . count($regions) . ' regions for RO.');

            $data = $this->csvProcessor->getData($filePath);
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName('ro_localities');

            $count = 0;
            $rowsToInsert = [];

            $connection->truncateTable($tableName);

            foreach ($data as $row) {
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }
                $cityName = trim($row[0]);
                $regionName = trim($row[1]);

                $regionId = $this->findRegionId($regionName, $regions);

                if ($regionId) {
                    $rowsToInsert[] = [
                        'region_id' => $regionId,
                        'city_name' => $cityName
                    ];
                    $count++;
                }

                if (count($rowsToInsert) >= 500) {
                    $connection->insertMultiple($tableName, $rowsToInsert);
                    $rowsToInsert = [];
                }
            }

            if (!empty($rowsToInsert)) {
                $connection->insertMultiple($tableName, $rowsToInsert);
            }

            $output->writeln("<info>Successfully imported $count cities!</info>");
            return Cli::RETURN_SUCCESS;

        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Cli::RETURN_FAILURE;
        }
    }

    /**
     * @throws LocalizedException
     */
    private function getRoRegions()
    {
        $regionCollection = $this->regionFactory->create()->getCollection()
            ->addCountryFilter('RO');

        $map = [];
        foreach ($regionCollection as $region) {
            $map[mb_strtolower($region->getDefaultName())] = $region->getId();
            $map[mb_strtolower($region->getName())] = $region->getId();
        }
        return $map;
    }

    private function findRegionId($csvRegionName, $map)
    {
        $key = mb_strtolower($csvRegionName);
        return $map[$key] ?? null;
    }
}
