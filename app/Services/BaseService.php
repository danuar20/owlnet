<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Abstract base service.
 *
 * All domain services extend this class. It intentionally holds no logic; it
 * exists to give services a shared typing point and to document the
 * convention that services must never touch the HTTP layer or Eloquent
 * directly — they orchestrate repositories and other services only.
 */
abstract class BaseService
{
    //
}
