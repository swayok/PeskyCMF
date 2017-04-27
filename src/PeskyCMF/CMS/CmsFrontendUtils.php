<?php

namespace PeskyCMF\CMS;

use Illuminate\Routing\Route;
use PeskyCMF\CMS\Pages\CmsPage;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\CMS\Pages\CmsPageWrapper;
use PeskyCMF\CMS\Redirects\CmsRedirect;
use PeskyCMF\HttpCode;
use PeskyORM\ORM\RecordInterface;
use Swayok\Html\EmptyTag;
use Swayok\Html\Tag;

abstract class CmsFrontendUtils {

    /** @var CmsPageWrapper[] */
    static protected $loadedPages = [];

    static public function registerBladeDirectiveForStringTemplateRendering() {
        \Blade::directive('wysiwygInsert', function ($param) {
            return '<?php echo ' . html_entity_decode($param, ENT_QUOTES | ENT_HTML401) . '; ?>';
        });
    }

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
        $route = \Route::get('{page_url}', $routeAction);
        if (count($excludeUrlPrefixes) > 0) {
            $route->where('page_url', '/?(?!' . implode('|', $excludeUrlPrefixes) . ').*');
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
        return static::addRouteForPages(function ($pageUrl = '/') use ($view, $viewData) {
            /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
            return static::renderPage('/' . ltrim($pageUrl, '/'), $view, $viewData);
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
        return view($view, array_merge($data, ['page' => $page]))->render();
    }

    /**
     * Get value of $columnName from page identified by $pageIdOrPageCode
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
    static public function getPageData($pageIdOrPageCode, $columnName = 'content') {
        $page = static::getPageWrapper($pageIdOrPageCode);
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
    static public function makeHtmlLinkToPageForInsert($pageIdOrPageCode, $linkText = null, $openInNewTab = false) {
        $page = static::getPageWrapper($pageIdOrPageCode);
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
            ->setContent(trim(stripslashes($linkText)))
            ->setHref(rtrim($page->relative_url, '/'))
            ->setAttribute('target', $openInNewTab ? '_blank' : null);
    }

    /**
     * @param string $textWithInserts
     * @param string $cacheKey
     * @return string
     */
    static public function processDataInsertsForText($textWithInserts, $cacheKey) {
        static $compiled;
        if ($compiled === null) {
            $compiled = [];
        }
        $compiledViewsPath = config('view.compiled', function () {
            return realpath(storage_path('framework/views'));
        });
        $filePath = $compiledViewsPath . DIRECTORY_SEPARATOR . strtolower(preg_replace('%[^a-zA-Z0-9]+%', '_', $cacheKey)) . '.php';
        if (
            !in_array($filePath, $compiled, true)
            && (
                config('app.debug')
                || !\File::exists($filePath)
                || filemtime($filePath) + 3600 < time()
            )
        ) {
            \File::put($filePath, \Blade::compileString(preg_replace(
                ['%<span[^>]+class="wysiwyg-data-insert"[^>]*>(.*?)</span>%is', '%<div[^>]+class="wysiwyg-data-insert"[^>]*>(.*?)</div>%is'],
                ['$1', '<div>$1</div>'],
                $textWithInserts
            )));
            $compiled[] = $filePath;
        }
        \View::startSection($filePath);
        include $filePath;
        return \View::yieldSection();
    }

    /**
     * Get rendered text block contents
     * @param string $pageCode
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getTextBlock($pageCode) {
        return static::getPageData($pageCode, 'content');
    }

    /**
     * Get rendered menu contents
     * @param string $pageCode
     * @param null|string $language - 2-letter code of language
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getMenu($pageCode) {
        return static::getPageData($pageCode, 'content');
    }

    /**
     * @param string $pageCode
     * @return string
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getMenuHeader($pageCode) {
        return static::getPageData($pageCode, 'menu_title');
    }

    /**
     * Extract links from menu's content
     * @param string $pageCode
     * @param bool $parseLinks
     *  - false: return list of strings like '<a href="/link/url">link content</a>'
     *  - true: return list of arrays in format: ['url' => '/link/url', 'text' => 'link content']
     * @return array
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static public function getLinksForMenu($pageCode, $parseLinks = false) {
        $links = [];
        if (preg_match_all('%<a[^>]+href=([\'"])(.*?)\1[^>]+>(.*?)</a>%is', static::getMenu($pageCode), $matches)) {
            if ($parseLinks) {
                foreach ($matches[2] as $index => $url) {
                    $links[] = [
                        'url' => $url,
                        'text' => $matches[3][$index]
                    ];
                }
            } else {
                $links = $matches[0];
            }
        }
        return $links;
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
    static public function getPageWrapper($pageIdOrPageCode) {
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
            if (!empty($page->page_code)) {
                static::$loadedPages[$page->page_code] = $wrapper;
            }
            if (!empty($page->url_alias)) {
                static::$loadedPages[static::normalizePageUrl($page->relative_url)] = $wrapper;
            }
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

    /**
     * @param CmsPage|CmsPageWrapper $page
     * @param $language
     * @return string
     */
    static public function makeCacheKeyForPageContentView($page, $language) {
        return 'page-' . $page->id . '-lang-' . $language . '-updated-at-' . $page->updated_at_as_unix_ts;
    }

}