<?php

declare(strict_types=1);

namespace App\Enum\ServiceRequest;

/**
 * @see config/packages/workflow.yaml
 * @see docs/service_request_status_workflow.png
 */
enum ServiceRequestStatusTransition: string
{
    case ACCEPT = 'accept';
    case MODIFY_OWNER = 'modifyOwner';
    case MODIFY_RECIPIENT = 'modifyRecipient';
    case CONFIRM = 'confirm';
    case FINALIZE = 'finalize';
    case AUTO_FINALIZE = 'autoFinalize';
    case REFUSE = 'refuse';
}
