<?php

namespace PeskyCMF\CMS;

use Illuminate\Routing\Route;
use PeskyCMF\CMS\Pages\CmsPage;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\CMS\Pages\CmsPageWrapper;
use PeskyCMF\CMS\Redirects\CmsRedirect;
use PeskyCMF\HttpCode;
use PeskyORM\ORM\RecordInterface;
use Ramsey\Uuid\Uuid;
use Swayok\Html\EmptyTag;
use Swayok\Html\Tag;

abstract class CmsFrontendUtils {

    /** @var CmsPageWrapper[] */
    static protected $loadedPages = [];

    /**
     * Declare route that will handle HTTP GET requests to CmsPagesTable
     * @param string|\Closure $routeAction - Closure, 'Controller@action' string, array.
     *      It is used as 2nd argument for \Route:get('url', $routeAction)
     * @param array $excludeUrlPrefixes - list of url prefixes used in application.
     * For example: 'admin' is default url prefix for administration area. It should be excluded in order to allow
     * access to administration area. Otherwise this route will intercept it.
     * @return Route
     */
    static public function addRouteForPages($routeAction, array $excludeUrlPrefixes = []) {
        $route = \Route::get('{url}', $routeAction);
        if (count($excludeUrlPrefixes) > 0) {
            $route->where('url', '/?(?!' . implode('|', $excludeUrlPrefixes) . ').*');
        }
        return $route;
    }

    /**
     * Declare route that will handle HTTP GET requests to CmsPagesTable using
     * CmsFrontendUtils::renderPage() as route action.
     * @param string $view - path to view that will render the page
     * @param \Closure $viewData - function (CmsPageWrapper $page) { return [] }. Returns array with data
     * to send to view in addition to 'texts' variable (CmsTextWrapper).
     * @param array $excludeUrlPrefixes - list of url prefixes used in application.
     * For example: 'admin' is default url prefix for administration area. It should be excluded in order to allow
     * access to administration area. Otherwise this route will intercept it.
     * @return Route
     */
    static public function addRouteForPagesWithDefaultAction($view, \Closure $viewData = null, array $excludeUrlPrefixes = []) {
        return static::addRouteForPages(function ($url) use ($view, $viewData) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return static::renderPage($url, $view, $viewData);
        }, $excludeUrlPrefixes);
    }

    /**
     * Render $view with $viewData for page with $url.
     * If $url is detected in CmsRedirectsTable - client will be redirected to page provided by CmsRedirect->page_id;
     * If page for $url was not found - 404 page will be shown;
     * 'texts' variable (CmsTextWrapper) will be additionally passed to a $view;
     * @param string $url - page's relative url
     * @param string|\Closure $view
     *      - string: path to view that will render the page;
     *      - \Closure - function (CmsPageWrapper $page) { return 'path.to.view'; }
     * @param \Closure $viewData - function (CmsPageWrapper $page) { return [] }. Returns array with data
     * to send to view in addition to 'texts' variable (CmsTextWrapper).
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    static public function renderPage($url, $view, \Closure $viewData = null) {
        $url = strtolower(rtrim($url, '/'));
        // search for url in redirects
        // todo: implement group redirect when redirect registered on page that has children
        /** @var CmsRedirect $redirectClass */
        $redirectClass = app(CmsRedirect::class);
        $redirect = $redirectClass::find([
            'relative_url ~*' => '^' . preg_quote($url, null) . '/*$'
        ]);
        if ($redirect->existsInDb()) {
            return redirect(
                rtrim($redirect->Page->relative_url, '/'),
                $redirect->is_permanent ? HttpCode::MOVED_PERMANENTLY : HttpCode::MOVED_TEMPORARILY
            );
        }
        $page = static::getPageByUrl($url);
        if (!$page->isValid()) {
            abort(404);
        }
        $data = [];
        if (!empty($viewData)) {
            $data = $viewData($page);
            if (!is_array($data)) {
                throw new \UnexpectedValueException('$viewData closure must return an array');
            }
        }
        $page->sendMetaTagsAndPageTitleSectionToLayout();
        if ($view instanceof \Closure) {
            $view = $view($page);
        }
        if (!is_string($view) || empty($view)) {
            throw new \InvalidArgumentException(
                '$view argument must be a not empty string or closure that returns not empty string'
            );
        }
        return view($view, array_merge($data, ['page' => $page]));
    }

    /**
     * @param int $pageIdOrPageCode
     * @param string $columnName
     * @return string
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public static function getPageDataForInsert($pageIdOrPageCode, $columnName = 'content') {
        $page = static::getPage($pageIdOrPageCode);
        if (!$page->isValid()) {
            return '';
        }
        return $page->$columnName;
    }

    /**
     * @param int $pageIdOrPageCode
     * @param null|string $linkText
     * @param bool $openInNewTab
     * @return Tag
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     * @throws \Swayok\Html\HtmlTagException
     */
    public static function makeHtmlLinkToPageForInsert($pageIdOrPageCode, $linkText = null, $openInNewTab = false) {
        $page = static::getPage($pageIdOrPageCode);
        if (!$page->isValid()) {
            return EmptyTag::create();
        }
        if (trim((string)$linkText) === '') {
            $linkText = $page->menu_title;
            /** @noinspection NotOptimalIfConditionsInspection */
            if (trim((string)$linkText) === '') {
                return EmptyTag::create();
            }
        }
        return Tag::a()
            ->setContent($linkText)
            ->setHref(rtrim($page->relative_url, '/'))
            ->setAttribute('target', $openInNewTab ? '_blank' : null);
    }

    /**
     * @param string $textWithInserts
     * @return string
     */
    public static function processDataInsertsForText($textWithInserts) {
        // todo: should I use \Blade::compileString($textWithInserts); ??
        return \View::yieldContent(Uuid::uuid4(), $textWithInserts);
    }

    /**
     * @param int $pageIdOrPageCode
     * @return CmsPageWrapper
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static protected function getPage($pageIdOrPageCode) {
        return static::getPageFromCache($pageIdOrPageCode, function ($pageIdOrPageCode) {
            /** @var CmsPage $pageClass */
            $pageClass = app(CmsPage::class);
            return $pageClass::find(
                [
                    'OR' => [
                        $pageClass::getPrimaryKeyColumnName() => (int)$pageIdOrPageCode,
                        'page_code' => $pageIdOrPageCode
                    ],
                ],
                [],
                ['Parent']
            );
        });
    }

    /**
     * @param string $url
     * @return CmsPageWrapper
     * @throws \PDOException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static protected function getPageByUrl($url) {
        return static::getPageFromCache($url, function ($url) {
            /** @var CmsPagesTable $pagesTable */
            $pagesTable = app(CmsPagesTable::class);
            $lastUrlSection = preg_quote(array_last(explode('/', trim($url, '/'))), null);
            $possiblePages = $pagesTable::select(['*', 'Parent' => ['*']], [
                'url_alias ~*' => (empty($url) ? '^' : $lastUrlSection) . '/*$',
                'ORDER' => ['parent_id' => 'DESC']
            ]);
            /** @var CmsPage $possiblePage */
            foreach ($possiblePages as $possiblePage) {
                static::savePageToCache($possiblePage);
            }
            return static::getPageFromCache($url, function () use ($pagesTable) {
                return $pagesTable::getInstance()->newRecord();
            });
        });
    }

    /**
     * @param string $cacheKey
     * @param \Closure $default
     * @return CmsPageWrapper
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static protected function getPageFromCache($cacheKey, \Closure $default) {
        $cacheKey = static::normalizePageUrl($cacheKey);
        if (!static::hasPageInCache($cacheKey)) {
            static::savePageToCache($default($cacheKey), $cacheKey);
        }
        return static::$loadedPages[$cacheKey];
    }

    /**
     * @param RecordInterface|CmsPage $page
     * @param string|null $cacheKeyForNotExistingPage - cache key to store not existing CmsPage
     */
    static protected function savePageToCache($page, $cacheKeyForNotExistingPage = null) {
        if ($page instanceof CmsPageWrapper) {
            return;
        }
        $wrapper = new CmsPageWrapper($page);
        if ($page->existsInDb()) {
            static::$loadedPages[$page->getPrimaryKeyValue()] = $wrapper;
            static::$loadedPages[$page->page_code] = $wrapper;
            static::$loadedPages[static::normalizePageUrl($page->relative_url)] = $wrapper;
        } else if (!empty($cacheKeyForNotExistingPage)) {
            static::$loadedPages[static::normalizePageUrl($cacheKeyForNotExistingPage)] = $wrapper;
        }
    }

    /**
     * @param string $cacheKey
     * @return bool
     */
    static protected function hasPageInCache($cacheKey) {
        return array_key_exists($cacheKey, static::$loadedPages);
    }

    /**
     * @param $url
     * @return string
     */
    static protected function normalizePageUrl($url) {
        return strtolower(rtrim((string)$url, '/'));
    }
}