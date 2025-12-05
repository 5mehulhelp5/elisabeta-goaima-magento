<?php
namespace MyCompany\LegalPerson\Controller\City;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\Result\JsonFactory;

class Index implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param ResourceConnection $resource
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ResourceConnection $resource,
        RequestInterface $request
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resource = $resource;
        $this->request = $request;
    }

    /**
     * Execute action based on request and return result
     */
    public function execute()
    {
        $regionId = $this->request->getParam('region_id');
        $result = $this->resultJsonFactory->create();

        if (!$regionId) {
            return $result->setData([]);
        }

        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('ro_localities');

        $select = $connection->select()
            ->from($tableName, ['city_name'])
            ->where('region_id = ?', $regionId)
            ->order('city_name ASC');

        $cities = $connection->fetchAll($select);

        $options = [];
        foreach ($cities as $city) {
            $options[] = [
                'value' => $city['city_name'],
                'label' => $city['city_name']
            ];
        }

        return $result->setData($options);
    }
}
