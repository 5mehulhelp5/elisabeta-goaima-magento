<?php
namespace MyCompany\AdminLogger\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use MyCompany\AdminLogger\Model\ActionLogFactory;
use Magento\Backend\Model\Auth\Session;
use MyCompany\AdminLogger\Helper\Config;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\RequestInterface;

class LogAdminAction implements ObserverInterface
{
    protected $logFactory;
    protected $authSession;
    protected $configHelper;
    protected $remoteAddress;
    protected $dateTime;
    protected $jsonSerializer;
    protected $request;

    // Proprietati noi pentru injectare
    protected $actionType;
    protected $entityType;

    public function __construct(
        ActionLogFactory $logFactory,
        Session $authSession,
        Config $configHelper,
        RemoteAddress $remoteAddress,
        DateTime $dateTime,
        Json $jsonSerializer,
        RequestInterface $request,
        string $actionType = 'unknown', // Injectat via di.xml
        string $entityType = 'unknown'  // Injectat via di.xml
    ) {
        $this->logFactory = $logFactory;
        $this->authSession = $authSession;
        $this->configHelper = $configHelper;
        $this->remoteAddress = $remoteAddress;
        $this->dateTime = $dateTime;
        $this->jsonSerializer = $jsonSerializer;
        $this->request = $request;
        $this->actionType = $actionType;
        $this->entityType = $entityType;
    }

    public function execute(Observer $observer)
    {
        if (!$this->configHelper->isEnabled()) {
            return;
        }

        // Folosim valorile injectate in constructor
        $actionType = $this->actionType;
        $entityType = $this->entityType;

        $allowedActions = $this->configHelper->getLoggedActionTypes();
        $allowedEntities = $this->configHelper->getLoggedEntities();

        // Optional: Filtrare stricta
        // if (!in_array($actionType, $allowedActions) || !in_array($entityType, $allowedEntities)) { return; }

        $entityId = null;
        $requestData = null;

        if ($actionType == 'save' || $actionType == 'delete') {
            // Incercam sa luam obiectul specific (ex: $observer->getProduct()) sau cel generic
            $eventObject = $observer->getData($entityType) ?: $observer->getData('object');

            // Fallback pentru Customer care vine uneori ca 'customer_data_object'
            if (!$eventObject && $entityType == 'customer') {
                $eventObject = $observer->getData('customer');
            }

            if ($eventObject && method_exists($eventObject, 'getId')) {
                $entityId = $eventObject->getId();
            }

            if ($actionType == 'save') {
                $requestData = $this->jsonSerializer->serialize($this->request->getPostValue());
            }
        } elseif ($actionType == 'edit' || $actionType == 'view') {
            $entityId = $this->request->getParam('id') ?: $this->request->getParam('entity_id');
        }

        $user = $this->authSession->getUser();
        if (!$user) {
            return;
        }

        try {
            $log = $this->logFactory->create();
            $log->setData([
                'admin_user_id' => $user->getId(),
                'admin_username' => $user->getUserName(),
                'action_type' => $actionType,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'request_data' => $requestData,
                'ip_address' => $this->remoteAddress->getRemoteAddress(),
                'user_agent' => $this->request->getHeader('User-Agent'),
                'created_at' => $this->dateTime->gmtDate()
            ]);
            $log->save();
        } catch (\Exception $e) {
            // Silent fail
        }
    }
}
