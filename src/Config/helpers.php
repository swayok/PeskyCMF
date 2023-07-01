<?php

declare(strict_types=1);

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use PeskyCMF\CmfManager;
use PeskyCMF\CmfUrl;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\PeskyCmfAppSettings;
use Swayok\Utils\StringUtils;

if (!defined('DOTJS_INSERT_REGEXP_FOR_ROUTES')) {
    define('DOTJS_INSERT_REGEXP_FOR_ROUTES', '(\{\{\s*=.*?\}\}|\{\s*=.*?\})');
}

if (!function_exists('cmfManager')) {
    function cmfManager(): CmfManager
    {
        return app(CmfManager::class);
    }
}

if (!function_exists('cmfConfig')) {
    function cmfConfig(): CmfConfig
    {
        return cmfManager()->getCurrentCmfConfig();
    }
}

if (!function_exists('cmfRoute')) {
    function cmfRoute(
        string $routeName,
        array $parameters = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        return CmfUrl::route($routeName, $parameters, $absolute, $cmfConfig);
    }
}

if (!function_exists('cmfRouteTpl')) {
    function cmfRouteTpl(
        string $routeName,
        array $parameters = [],
        array $tplParams = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        return CmfUrl::routeTpl($routeName, $parameters, $tplParams, $absolute, $cmfConfig);
    }
}

if (!function_exists('routeToCmfPage')) {
    function routeToCmfPage(
        string $pageId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toPage($pageId, $queryArgs, $absolute, $cmfConfig, $ignoreAccessPolicy);
    }
}

if (!function_exists('redirectToCmfPage')) {
    function redirectToCmfPage(
        string $pageId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): RedirectResponse {
        return CmfUrl::redirectToPage($pageId, $queryArgs, $absolute, $cmfConfig);
    }
}

if (!function_exists('routeToCmfItemsTable')) {
    function routeToCmfItemsTable(
        string $resourceName,
        array $filters = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemsTable(
            $resourceName,
            $filters,
            $absolute,
            $cmfConfig,
            $ignoreAccessPolicy
        );
    }
}

if (!function_exists('routeToCmfTableCustomData')) {
    function routeToCmfTableCustomData(
        string $resourceName,
        string $dataId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toTableCustomData(
            $resourceName,
            $dataId,
            $absolute,
            $cmfConfig,
            $ignoreAccessPolicy
        );
    }
}

if (!function_exists('routeToCmfItemAddForm')) {
    function routeToCmfItemAddForm(
        string $resourceName,
        array $data = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemAddForm(
            $resourceName,
            $data,
            $absolute,
            $cmfConfig,
            $ignoreAccessPolicy
        );
    }
}

if (!function_exists('routeToCmfItemEditForm')) {
    function routeToCmfItemEditForm(
        string $resourceName,
        string $itemId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemEditForm(
            $resourceName,
            $itemId,
            $absolute,
            $cmfConfig,
            $ignoreAccessPolicy
        );
    }
}

if (!function_exists('routeForCmfTempFileUpload')) {
    function routeForCmfTempFileUpload(
        string $resourceName,
        string $inputName,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toTempFileUpload(
            $resourceName,
            $inputName,
            $absolute,
            $cmfConfig,
            $ignoreAccessPolicy
        );
    }
}

if (!function_exists('routeForCmfTempFileDelete')) {
    function routeForCmfTempFileDelete(
        string $resourceName,
        string $inputName,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toTempFileDelete(
            $resourceName,
            $inputName,
            $absolute,
            $cmfConfig,
            $ignoreAccessPolicy
        );
    }
}

if (!function_exists('routeToCmfItemCloneForm')) {
    function routeToCmfItemCloneForm(
        string $resourceName,
        string $itemId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemCloneForm(
            $resourceName,
            $itemId,
            $absolute,
            $cmfConfig,
            $ignoreAccessPolicy
        );
    }
}

if (!function_exists('routeToCmfItemDetails')) {
    function routeToCmfItemDetails(
        string $resourceName,
        string $itemId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemDetails(
            $resourceName,
            $itemId,
            $absolute,
            $cmfConfig,
            $ignoreAccessPolicy
        );
    }
}

if (!function_exists('routeToCmfItemDelete')) {
    function routeToCmfItemDelete(
        string $resourceName,
        string $itemId,
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null,
        bool $ignoreAccessPolicy = false
    ): ?string {
        return CmfUrl::toItemDelete(
            $resourceName,
            $itemId,
            $absolute,
            $cmfConfig,
            $ignoreAccessPolicy
        );
    }
}

if (!function_exists('routeToCmfResourceCustomPage')) {
    function routeToCmfResourceCustomPage(
        string $resourceName,
        string $pageId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        return CmfUrl::toResourceCustomPage(
            $resourceName,
            $pageId,
            $queryArgs,
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeToCmfItemCustomPage')) {
    function routeToCmfItemCustomPage(
        string $resourceName,
        string $itemId,
        string $pageId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        return CmfUrl::toItemCustomPage(
            $resourceName,
            $itemId,
            $pageId,
            $queryArgs,
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeToCmfItemCustomAction')) {
    function routeToCmfItemCustomAction(
        string $resourceName,
        string $itemId,
        string $actionId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        return CmfUrl::toItemCustomAction(
            $resourceName,
            $itemId,
            $actionId,
            $queryArgs,
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('routeToCmfResourceCustomAction')) {
    function routeToCmfResourceCustomAction(
        string $resourceName,
        string $actionId,
        array $queryArgs = [],
        bool $absolute = false,
        ?CmfConfig $cmfConfig = null
    ): string {
        return CmfUrl::toResourceCustomAction(
            $resourceName,
            $actionId,
            $queryArgs,
            $absolute,
            $cmfConfig
        );
    }
}

if (!function_exists('transChoiceRu')) {
    /**
     * @param array|string $idOrTranslations - array: translations rray with 3 values:
     *      array(0 => 'variant for 1', 1 => 'variant for 4', 2 => 'variant for 5')
     * @param int          $itemsCount
     * @param array        $parameters
     * @param string       $locale
     * @return string
     */
    function transChoiceRu(
        array|string $idOrTranslations,
        int $itemsCount,
        array $parameters = [],
        string $locale = 'ru'
    ): string {
        return transChoiceAlt($idOrTranslations, $itemsCount, $parameters, $locale);
    }
}

if (!function_exists('transChoiceAlt')) {
    /**
     * @param array|string $idOrTranslations - array: translations array with 3 values:
     *      array(0 => 'variant for 1', 1 => 'variant for 4', 2 => 'variant for 5')
     * @param int          $itemsCount
     * @param array        $parameters
     * @param string|null  $locale
     * @return string
     */
    function transChoiceAlt(
        array|string $idOrTranslations,
        int $itemsCount,
        array $parameters = [],
        ?string $locale = null
    ): string {
        $trans = StringUtils::pluralizeRu(
            $itemsCount,
            is_array($idOrTranslations)
                ? $idOrTranslations
                : trans($idOrTranslations, [], $locale)
        );
        if (!empty($parameters)) {
            $trans = StringUtils::insert($trans, $parameters, ['before' => ':']);
        }
        return $trans;
    }
}

if (!function_exists('cmfTransGeneral')) {
    /**
     * @param string      $path Without dictionary name.
     *      Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array       $parameters
     * @param null|string $locale
     * @return string|array
     */
    function cmfTransGeneral(
        string $path,
        array $parameters = [],
        ?string $locale = null
    ): array|string {
        return cmfConfig()->transGeneral($path, $parameters, $locale);
    }
}

if (!function_exists('cmfTransCustom')) {
    /**
     * @param string      $path Without dictionary name.
     *      Example: 'admins.test' will be converted to '{dictionary}.admins.test'
     * @param array       $parameters
     * @param null|string $locale
     * @return string|array
     */
    function cmfTransCustom(
        string $path,
        array $parameters = [],
        ?string $locale = null
    ): array|string {
        return cmfConfig()->transCustom($path, $parameters, $locale);
    }
}

if (!function_exists('cmfJsonResponseForValidationErrors')) {
    /**
     * @param array       $errors
     * @param null|string $message
     * @return CmfJsonResponse
     */
    function cmfJsonResponseForValidationErrors(
        array $errors = [],
        ?string $message = null
    ): CmfJsonResponse {
        if (empty($message)) {
            $message = (string)cmfTransGeneral('form.message.validation_errors');
        }
        return CmfJsonResponse::create(HttpCode::CANNOT_PROCESS)
            ->setErrors($errors, $message);
    }
}

if (!function_exists('cmfJsonResponseForHttp404')) {
    /**
     * @param null|string $fallbackUrl
     * @param null|string $message
     * @return CmfJsonResponse
     */
    function cmfJsonResponseForHttp404(
        ?string $fallbackUrl = null,
        ?string $message = null
    ): CmfJsonResponse {
        if (empty($message)) {
            $message = (string)cmfTransGeneral('message.http404');
        }
        if (empty($fallbackUrl)) {
            $fallbackUrl = cmfConfig()->homePageUrl();
        }
        return CmfJsonResponse::create(HttpCode::NOT_FOUND)
            ->setMessage($message)
            ->goBack($fallbackUrl);
    }
}

if (!function_exists('cmfRedirectResponseWithMessage')) {
    function cmfRedirectResponseWithMessage(
        bool $isAjax,
        string $url,
        string $message,
        string $type = 'info',
        ?CmfConfig $cmfConfig = null
    ): RedirectResponse|CmfJsonResponse {
        if ($isAjax) {
            return CmfJsonResponse::create()
                ->setMessage($message)
                ->setRedirect($url);
        }
        if (!$cmfConfig) {
            $cmfConfig = cmfConfig();
        }
        return (new RedirectResponse($url))->with(
            $cmfConfig->sessionMessageKey(),
            [
                'message' => $message,
                'type' => $type,
            ]
        );
    }
}

if (!function_exists('formatDate')) {
    /**
     * @param int|string|CarbonInterface|null $date
     * @param bool                            $addTime
     * @param string                          $yearSuffix - 'none', 'full', 'short' or custom value
     * @param bool|int|string                 $ignoreYear
     *      - false: year will be added
     *      - true: year will not be added;
     *      - 'current': drop year only when it is same as current
     *      - integer: drop year only when it is same as passed integer
     *      - other values: year will be added
     * @param string|null                     $default
     * @return string|null
     */
    function formatDate(
        int|string|CarbonInterface|null $date,
        bool $addTime = false,
        string $yearSuffix = 'full',
        bool|int|string $ignoreYear = false,
        ?string $default = ''
    ): ?string {
        if (!$date) {
            return $default;
        }
        if (!($date instanceof CarbonInterface)) {
            if (is_numeric($date)) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $date = Carbon::createFromTimestamp($date);
            } else {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                $date = Carbon::parse($date);
            }
        }
        if (in_array(app()->getLocale(), ['ru', 'ru_RU'], true)) {
            $month = mb_strtolower(cmfTransGeneral('month.when.' . $date->format('m')));
            if (
                $ignoreYear === true //< ignore any year
                || (is_numeric($ignoreYear) && (int)$ignoreYear === $date->year) //< ignore certain year ($ignoreYear)
                || ($ignoreYear === 'current' && $date->isCurrentYear()) //< ignore current year
            ) {
                $year = '';
            } else {
                switch ($yearSuffix) {
                    case 'short':
                        $yearSuffix = (string)cmfTransGeneral('year_suffix.short');
                        break;
                    case 'full':
                        $yearSuffix = (string)cmfTransGeneral('year_suffix.full');
                        break;
                    case 'none':
                        $yearSuffix = '';
                }
                $year = $date->year . $yearSuffix;
            }
            $dateStr = rtrim("{$date->day} {$month} {$year}");
            $timeStr = $addTime
                ? ' ' . ltrim(cmfTransGeneral('time.at') . $date->format(' H:i'))
                : '';
            return $dateStr . $timeStr;
        }

        return date('H:i d F Y')
            . (in_array($yearSuffix, ['short', 'full', 'none'], true) ? '' : $yearSuffix);
    }
}

if (!function_exists('formatMoney')) {
    /**
     * @param float  $number
     * @param int    $decimals
     * @param string $thousandsSeparator
     * @return string
     */
    function formatMoney(
        float $number,
        int $decimals = 2,
        string $thousandsSeparator = ' '
    ): string {
        return number_format($number, $decimals, '.', $thousandsSeparator);
    }
}

if (!function_exists('formatSeconds')) {
    /**
     * @param int  $seconds
     * @param bool $displaySeconds
     *  - true: display "days hours minutes seconds";
     *  - false: display "days hours minutes"
     * @param bool $shortLabels
     *  - true: use shortened labels (min, sec, hr, d);
     *  - false: user full lables (days, hours, minutes, seconds)
     * @return string
     */
    function formatSeconds(
        int $seconds,
        bool $displaySeconds = true,
        bool $shortLabels = true
    ): string {
        $ret = '';
        if ($seconds >= 86400) {
            $days = floor($seconds / 86400);
            $seconds -= 86400 * $days;
            $ret .= $shortLabels
                ? cmfTransGeneral('format_seconds.days_short', ['days' => $days])
                : transChoiceAlt(
                    cmfTransGeneral('format_seconds.days'),
                    (int)$days,
                    ['days' => $days]
                );
        }
        if ($seconds >= 3600 || !empty($days)) {
            $hours = floor($seconds / 3600);
            $seconds -= 3600 * $hours;
            $ret .= $shortLabels
                ? cmfTransGeneral('format_seconds.hours_short', ['hours' => $hours])
                : transChoiceAlt(
                    cmfTransGeneral('format_seconds.hours'),
                    (int)$hours,
                    ['hours' => $hours]
                );
        }
        if ($seconds >= 60 || !empty($days) || !empty($hours)) {
            $minutes = floor($seconds / 60);
            $seconds -= 60 * $minutes;
            $ret .= $shortLabels
                ? cmfTransGeneral('format_seconds.minutes_short', ['minutes' => $minutes])
                : transChoiceAlt(
                    cmfTransGeneral('format_seconds.minutes'),
                    (int)$minutes,
                    ['minutes' => $minutes]
                );
        }
        if ($displaySeconds) {
            $ret .= $shortLabels
                ? cmfTransGeneral('format_seconds.seconds_short', ['seconds' => $seconds])
                : transChoiceAlt(
                    cmfTransGeneral('format_seconds.seconds'),
                    $seconds,
                    ['seconds' => $seconds]
                );
        } elseif (empty($days) && empty($hours) && empty($minutes)) {
            $ret = cmfTransGeneral('format_seconds.less_then_a_minute');
        }
        return $ret;
    }
}

if (!function_exists('pickLocalization')) {
    /**
     * Pick correct localization strings from specially formatted array.
     * Useful for localizations stored in DB.
     * @param array       $translations
     *  - associative array format ($isAssociativeArray = true):
     *      ['lang1_code' => 'translation1', 'lang2_code' => 'translation2', ...]
     *  - indexed array format ($isAssociativeArray = false):
     *      [ ['key' => 'lang1_code', 'value' => 'translation1'], ...]
     * @param null|string $default - default value to return when there is no translation for app()->getLocale()
     *      language and for CmfConfig::getPrimary()->default_locale()
     * @param bool        $isAssociativeArray
     *      - true: $translations keys = language codes, values = translations;
     *      - false: $translations values = arrays with 2 keys: 'key' and 'value';
     * @return string|null
     */
    function pickLocalization(
        array $translations,
        ?string $default = null,
        bool $isAssociativeArray = true
    ): ?string {
        $langCodes = [app()->getLocale(), cmfConfig()->defaultLocale()];
        foreach ($langCodes as $langCode) {
            if ($isAssociativeArray) {
                if (
                    array_key_exists($langCode, $translations)
                    && is_string($translations[$langCode])
                    && trim($translations[$langCode]) !== ''
                ) {
                    return $translations[$langCode];
                }
            } else {
                foreach ($translations as $translation) {
                    if (
                        isset($translation['key'])
                        && $translation['key'] === $langCode
                        && !empty($translation['value'])
                        && trim($translation['value']) !== ''
                    ) {
                        return $translation['value'];
                    }
                }
            }
        }
        return $default;
    }
}

if (!function_exists('pickLocalizationFromJson')) {
    /**
     * Pick correct localization strings from specially formatted array.
     * Useful for localizations stored in DB.
     * @param array|string $translationsJson Format: '{"lang1_code": "translation1", "lang2_code": "translation2", ...}'
     * @param null|string  $default Default value to return when there is no translation for app()->getLocale()
     *      language and for CmfConfig::getPrimary()->default_locale()
     * @param bool         $isAssociativeArray
     *      - true: $translations keys = language codes, values = translations;
     *      - false: $translations values = arrays with 2 keys: 'key' and 'value';
     * @return string|null
     * @see pickLocalization()
     */
    function pickLocalizationFromJson(
        array|string $translationsJson,
        ?string $default = null,
        bool $isAssociativeArray = true
    ): ?string {
        $translations = is_array($translationsJson)
            ? $translationsJson
            : json_decode($translationsJson, true, 512, JSON_THROW_ON_ERROR);
        return is_array($translations)
            ? $default
            : pickLocalization($translations, $default, $isAssociativeArray);
    }
}

if (!function_exists('setting')) {
    /**
     * Get value for CmfSetting called $name (CmfSetting->key === $name)
     * @param string|null $name - setting name
     * @param mixed|null  $default - default value
     * @return mixed|PeskyCmfAppSettings
     */
    function setting(?string $name = null, mixed $default = null): mixed
    {
        $appSettings = cmfConfig()->getAppSettings();
        if ($name === null) {
            return $appSettings;
        }
        return $appSettings->getSetting($name, false, $default);
    }
}

if (!function_exists('hidePasswords')) {
    /**
     * @param array $data
     * @return array
     */
    function hidePasswords(array $data): array
    {
        foreach ($data as $key => &$value) {
            if (!empty($value) && preg_match('(pass(word|phrase|wd)?|pwd|secret)', $key)) {
                $value = '******';
            }
        }
        return $data;
    }
}
