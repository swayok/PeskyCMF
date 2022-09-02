<?php

declare(strict_types=1);

namespace PeskyCMF\Db\Contracts;

interface ResetsPasswordsViaAccessKey
{
    
    /**
     * Generate access key to be used in password recovery urls
     * @param int|null $expiresIn - minutes until expiration.
     * Default: config('auth.passwords.' . app('auth')->getDefaultDriver() . '.expire', 60)
     * @param array|null $additionalColumns - additional columns to encode.
     */
    public function getPasswordRecoveryAccessKey(?int $expiresIn = null, ?array $additionalColumns = null): string;
    
    /**
     * Validate access key and find user
     * @param string $accessKey
     * @param null|array $requiredAdditionalColumns - list of $additionalColumns (see getPasswordRecoveryAccessKey) which must be present in $accessKey
     * @return static|null - null = failed to parse access key, validate data or load user
     */
    public static function loadFromPasswordRecoveryAccessKey(
        string $accessKey,
        ?array $requiredAdditionalColumns = null
    ): ?static;
}