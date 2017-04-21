<?php

namespace PeskyCMF\CMS;

use PeskyCMF\CMS\Pages\CmsPage;
use PeskyCMF\CMS\Pages\CmsPagesTable;
use PeskyCMF\CMS\Redirects\CmsRedirect;
use PeskyCMF\HttpCode;
use Swayok\Html\Tag;

abstract class CmsFrontendUtils {

    /** @var CmsPage[] */
    static protected $loadedPages = [];

    /**
     * Render $view with $viewData for page with $url.
     * If $url is detected in CmsRedirectsTable - client will be redirected to page provided by CmsRedirect->page_id;
     * If page for $url was not found - 404 page will be shown;
     * 'texts' valiable (CmsTextWrapper) will be additionally passed to a $view;
     * @param string $url - page's relative url
     * @param string $view - view to render
     * @param array $viewData - data to add to view
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    static public function renderPage($url, $view, array $viewData = []) {
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
        if (!$page->existsInDb() || !$page->is_published) {
            abort(404);
        }
        return view($view, array_merge($viewData, ['texts' => $page->getLocalizedText()]));
    }

    /**
     * @param int $pageId
     * @param string $columnName
     * @return string
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \BadMethodCallException
     * @throws \InvalidArgumentException
     */
    public static function getPageDataForInsert($pageId, $columnName = 'content') {
        $page = static::getPage($pageId);
        if (!$page->existsInDb() || !$page->is_published) {
            return '';
        }
        return $page->getLocalizedText()->$columnName();
    }

    /**
     * @param int $pageId
     * @param null|string $linkText
     * @param bool $openInNewTab
     * @return string
     */
    public static function makeHtmlLinkToPageForInsert($pageId, $linkText = null, $openInNewTab = false) {
        $page = static::getPage($pageId);
        if (!$page->existsInDb() || !$page->is_published) {
            return '';
        }
        if (trim((string)$linkText) === '') {
            $linkText = $page->getLocalizedText()->getMenuTitle();
            if (trim((string)$linkText) === '') {
                return '';
            }
        }
        return Tag::a()
            ->setContent($linkText)
            ->setHref(rtrim($page->relative_url, '/'))
            ->setAttribute('target', $openInNewTab ? '_blank' : null)
            ->build();
    }

    /**
     * @param string $textWithInserts
     * @return string
     */
    public static function processDataInsertsForText($textWithInserts) {
        // todo: should I use \Blade::compileString($textWithInserts); ??
        return \View::make($textWithInserts)->render();
    }

    /**
     * @param int $pageId
     * @return CmsPage
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static protected function getPage($pageId) {
        if (!array_key_exists($pageId, static::$loadedPages)) {
            /** @var CmsPage $pageClass */
            $pageClass = app(CmsPage::class);
            $page = $pageClass::read($pageId, [], ['Parent']);
            static::$loadedPages[$pageId] = $page;
            if ($page->existsInDb()) {
                static::$loadedPages[strtolower(rtrim($page->relative_url, '/'))] = $page;
            }
        }
        return static::$loadedPages[$pageId];
    }

    /**
     * @param string $url
     * @return CmsPage
     * @throws \PDOException
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    static protected function getPageByUrl($url) {
        $url = strtolower(rtrim($url, '/'));
        if (!array_key_exists($url, static::$loadedPages)) {
            /** @var CmsPagesTable $pagesTable */
            $pagesTable = app(CmsPagesTable::class);
            $lastUrlSection = preg_quote(array_last(explode('/', trim($url, '/'))), null);
            $possiblePages = $pagesTable::select(['*', 'Parent' => ['*']], [
                'url_alias ~*' => (empty($url) ? '^' : $lastUrlSection) . '/*$'
            ]);
            /** @var CmsPage $possiblePage */
            foreach ($possiblePages as $possiblePage) {
                static::$loadedPages[$possiblePage->id] = $possiblePage;
                static::$loadedPages[strtolower(rtrim($possiblePage->relative_url, '/'))] = $possiblePage;
            }
            if (!array_key_exists($url, static::$loadedPages)) {
                static::$loadedPages[$url] = $pagesTable::getInstance()->newRecord();
            }
        }
        return static::$loadedPages[$url];
    }
}