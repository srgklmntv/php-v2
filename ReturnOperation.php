<?php

namespace NW\WebService\References\Operations\Notification;

use NW\WebService\References\Operations\Notification\Enum\ContractorTypes;
use NW\WebService\References\Operations\Notification\Enum\NotificationEventStatuses;
use NW\WebService\References\Operations\Notification\Enum\NotificationTypes;
use NW\WebService\References\Operations\Notification\Helpers\ReferencesOperation;
use NW\WebService\References\Operations\Notification\Helpers\GetEmail;
use NW\WebService\References\Operations\Notification\Helpers\Status;
use NW\WebService\References\Operations\Notification\Services\Contractor;
use NW\WebService\References\Operations\Notification\Services\Employee;
use NW\WebService\References\Operations\Notification\Services\Seller;

class TsReturnOperation extends ReferencesOperation
{
    /**
     * Send notifications
     * 
     * @return array $result The result of sending notifications
     * @throws \Exception
     */
    public function sendNotifications(): array
    {
        /* Get request data */
        $data = (array)$this->getRequest('data');

        /* Get reseller id */
        $resellerId = (int)$data['resellerId'];
        $result = [
            'notificationEmployeeByEmail' => false,
            'notificationClientByEmail'   => false,
            'notificationClientBySms'     => [
                'isSent'  => false,
                'message' => '',
            ],
        ];

        if (empty($resellerId)) {
            $result['notificationClientBySms']['message'] = 'Empty resellerId';
            return $result;
        }

        /* Get notification type */
        $notificationType = (int)$data['notificationType'];

        if (empty($notificationType)) {
            throw new \Exception('Empty notificationType', 400);
        }

        /* Get seller data */
        $reseller = Seller::getById($resellerId);

        if ($reseller === null) {
            throw new \Exception('Seller not found!', 400);
        }

        /* Get client data */
        $clientId = (int)$data['clientId'];
        $client = Contractor::getById($clientId);
        if (
            $client === null
            || $client->type !== ContractorTypes::TYPE_CUSTOMER
            || $client->Seller->id !== $resellerId
        ) {
            throw new \Exception('сlient not found!', 400);
        }

        $cFullName = $client->getFullName();
        if (empty($client->getFullName())) {
            $cFullName = $client->name;
        }

        /* Get creator data */
        $creatorId = (int)$data['creatorId'];
        $creator = Employee::getById($creatorId);
        if ($creator === null) {
            throw new \Exception('Creator not found!', 400);
        }

        /* Get expert data */
        $expertId = (int)$data['expertId'];
        $expert = Employee::getById($expertId);
        if ($expert === null) {
            throw new \Exception('Expert not found!', 400);
        }

        /* Get differences data */
        $differences = '';

        if ($notificationType === NotificationTypes::NEW) {
            $differences = __('NewPositionAdded', null, $resellerId);
        } elseif (
            $notificationType === NotificationTypes::CHANGED
            && !empty($data['differences'])
        ) {
            $differences = __('PositionStatusHasChanged', [
                'FROM' => Status::getName((int)$data['differences']['from']),
                'TO'   => Status::getName((int)$data['differences']['to']),
            ], $resellerId);
        }

        /* Create template data */
        $templateData = [
            'COMPLAINT_ID'       => (int)$data['complaintId'],
            'COMPLAINT_NUMBER'   => (string)$data['complaintNumber'],
            'CREATOR_ID'         => $creatorId,
            'CREATOR_NAME'       => $creator->getFullName(),
            'EXPERT_ID'          => $expertId,
            'EXPERT_NAME'        => $expert->getFullName(),
            'CLIENT_ID'          => $clientId,
            'CLIENT_NAME'        => $cFullName,
            'CONSUMPTION_ID'     => (int)$data['consumptionId'],
            'CONSUMPTION_NUMBER' => (string)$data['consumptionNumber'],
            'AGREEMENT_NUMBER'   => (string)$data['agreementNumber'],
            'DATE'               => (string)$data['date'],
            'DIFFERENCES'        => $differences,
        ];

        /* Если хоть одна переменная для шаблона не задана, то не отправляем уведомления */
        foreach ($templateData as $tp_key => $templateParam) {
            if (empty($templateParam)) {
                throw new \Exception("Template Data ({$tp_key}) is empty!", 500);
            }
        }

        $emailFrom = GetEmail::getResellerEmailFrom($resellerId);
        /* Получаем email сотрудников из настроек */
        $emails = GetEmail::getEmailsByPermit($resellerId, 'tsGoodsReturn');
        if (!empty($emailFrom) && count($emails) > 0) {
            foreach ($emails as $email) {
                MessagesClient::sendMessage([
                    0 => [ // MessageTypes::EMAIL
                           'emailFrom' => $emailFrom,
                           'emailTo'   => $email,
                           'subject'   => __('complaintEmployeeEmailSubject', $templateData, $resellerId),
                           'message'   => __('complaintEmployeeEmailBody', $templateData, $resellerId),
                    ],
                ], $resellerId, NotificationEventStatuses::CHANGE_RETURN_STATUS);
                $result['notificationEmployeeByEmail'] = true;
            }
        }

        /* Шлём клиентское уведомление, только если произошла смена статуса */
        if (
            $notificationType === NotificationTypes::CHANGED
            && !empty($data['differences']['to'])
        ) {
            if (!empty($emailFrom) && !empty($client->email)) {
                MessagesClient::sendMessage([
                    0 => [ // MessageTypes::EMAIL
                           'emailFrom' => $emailFrom,
                           'emailTo'   => $client->email,
                           'subject'   => __('complaintClientEmailSubject', $templateData, $resellerId),
                           'message'   => __('complaintClientEmailBody', $templateData, $resellerId),
                    ],
                ], $resellerId, $client->id, NotificationEvents::CHANGE_RETURN_STATUS, (int)$data['differences']['to']);
                $result['notificationClientByEmail'] = true;
            }

            if (!empty($client->mobile)) {
                $sendResult = NotificationManager::send($resellerId, $client->id, NotificationEvents::CHANGE_RETURN_STATUS, (int)$data['differences']['to'], $templateData, $error);
                if ($sendResult) {
                    $result['notificationClientBySms']['isSent'] = true;
                }
                if (!empty($error)) {
                    $result['notificationClientBySms']['message'] = $error;
                }
            }
        }

        return $result;
    }
}