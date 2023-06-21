<?php

declare(strict_types=1);

namespace PeskyCMF\Utils;

use Illuminate\Http\Request;

/**
 * Locale detector.
 * Can detect locale from:
 * - URL search query argument.
 * - URL parameter.
 * - Special HTTP header.
 * - Cookie parameter.
 * - Accept-Language HTTP header.
 */
class LocaleDetector
{
    private string|null $specialHttpHeaderName = null;
    private string|null $cookieName = null;
    private string|null $urlParameterName = 'locale';
    private string|null $urlQueryArgumentName = 'lang';

    /**
     * Use provided HTTP header to detect locale.
     */
    public function useCookie(string|null $cookieName = 'locale'): void
    {
        $this->cookieName = $cookieName;
    }

    /**
     * Use provided HTTP header to detect locale.
     */
    public function useSpecialHttpHeader(string|null $headerName = 'site-locale'): void
    {
        $this->specialHttpHeaderName = strtolower($headerName);
    }

    /**
     * Use provided URL parameter name to detect locale.
     * URL example: /admin/{locale}/other
     */
    public function useUrlParameter(string|null $parameterName = 'locale'): void
    {
        $this->urlParameterName = $parameterName;
    }

    /**
     * Use provided URL search query argument name to detect locale.
     * URL example: /admin/other?lang={locale}
     */
    public function useUrlQueryArgument(string|null $argumentName = 'lang'): void
    {
        $this->urlQueryArgumentName = $argumentName;
    }

    /**
     * Detect locale from HTTP request according to priority.
     * $localesMap = [
     *      'en' => ['en-us', 'en-gb', 'uk'],
     *      'es' => ['es-es', 'es-ar'],
     *      'ru',
     *      'ru' => 'ru-ru'
     *      ...
     * ]
     * Key: locale, value: variations of locale for matcher (in lower case).
     * Priority:
     * 1. URL search query argument.
     * 2. URL parameter.
     * 3. Special HTTP header.
     * 4. Cookie parameter.
     * 5. Accept-Language HTTP header.
     */
    public function detectLocale(
        Request $request,
        array $localesMap,
        string $default
    ): string {
        $locale = null;
        // 1. URL search query argument.
        if ($this->urlQueryArgumentName) {
            $locale = $request->query($this->urlQueryArgumentName);
        }
        // 2. URL parameter.
        if (empty($locale) && $this->urlParameterName) {
            $locale = $request->route($this->urlParameterName);
        }
        // 3. Special HTTP header.
        if (empty($locale) && $this->specialHttpHeaderName) {
            $locale = $request->header($this->specialHttpHeaderName);
        }
        // 4. Cookie parameter.
        if (empty($locale) && $this->cookieName) {
            $locale = $request->cookies->get($this->cookieName);
        }
        // 5. Accept-Language HTTP header.
        if (empty($locale)) {
            // There are many variations, so we split the string.
            $locale = preg_split(
                '%\s*[,;]\s*%',
                mb_strtolower($request->header('accept-language') ?? '')
            );
        }
        return $this->normalizeLocale($locale, $localesMap, $default);
    }

    /**
     * Search for matching locale in list of supported locales.
     * If not found: returns default locale.
     */
    public function normalizeLocale(
        null|string|array $locale,
        array $localesMap,
        string $default
    ): string {
        if (!$locale) {
            return $default;
        }
        if (!is_array($locale)) {
            $locale = [mb_strtolower($locale)];
        }
        foreach ($localesMap as $localeCode => $aliases) {
            if (is_int($localeCode)) {
                /* ['en', ['en', 'en-us']] */
                if (is_string($aliases)) {
                    $localeCode = $aliases;
                    $aliases = [mb_strtolower($localeCode)];
                } elseif (is_array($aliases)) {
                    $localeCode = $aliases[0];
                } else {
                    continue;
                }
            }
            if (count(array_intersect($aliases, $locale)) > 0) {
                return $localeCode;
            }
        }
        return $default;
    }
}
