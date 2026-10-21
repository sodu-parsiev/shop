<?php

namespace App\Services\Bitrix24;

use RuntimeException;

/**
 * Carries an admin-facing explanation of why the webhook can't be used.
 */
class Bitrix24ConnectionException extends RuntimeException {}
