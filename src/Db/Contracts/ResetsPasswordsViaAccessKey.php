<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Contracts;

interface ResetsPasswordsViaAccessKey
{
    /**
     * Generate access key to be used in password recovery urls
     *
     * @param int|null   $expiresIn Minutes until expiration.
     * Default: config('auth.passwords.' . app('auth')->getDefaultDriver() . '.expire', 60)
     * @param array|null $additionalColumns Additional columns to encode.
     */
    public function getPasswordRecoveryAccessKey(?int $expiresIn = null, ?array $additionalColumns = null): string;

    /**
     * Validate access key and find user.
     * Returns null when failed to parse access key, validate data or load user.
     *
     * @param string     $accessKey
     * @param null|array $requiredAdditionalColumns List of columns which must be present in $accessKey
     *      (part or all of $additionalColumns passed to $this->getPasswordRecoveryAccessKey())
     */
    public static function loadFromPasswordRecoveryAccessKey(
        string $accessKey,
        ?array $requiredAdditionalColumns = null
    ): ?static;
}
