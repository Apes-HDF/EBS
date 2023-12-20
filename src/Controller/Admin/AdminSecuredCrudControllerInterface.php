<?php

declare(strict_types=1);

namespace App\Controller\Admin;

/**
 * This is a marker interface to identify crud controllers that need to have the
 * ROLE_ADMIN role.
 *
 * @see User
 * @see CrudControllerSubscriber
 */
interface AdminSecuredCrudControllerInterface
{
}
