<?php

declare(strict_types=1);

namespace PeskyCMF\Http\Controllers;

use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Cache\Store as CacheStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\Router;
use Illuminate\View\Factory as ViewFactory;
use PeskyCMF\ApiDocs\CmfApiMethodDocumentation;
use PeskyCMF\Auth\Middleware\CmfAuth;
use PeskyCMF\Http\CmfJsonResponse;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use Ramsey\Uuid\Uuid;
use Swayok\Html\Tag;
use Swayok\Utils\Folder;
use Swayok\Utils\ValidateValue;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CmfGeneralController extends CmfController
{
    
    public function loadJsApp(Request $request): string
    {
        if ($request->ajax()) {
            abort(new JsonResponse([], 404));
        }
        return $this->getCmfConfig()->getUiModule()->renderLayoutView();
    }
    
    public function getPage(Request $request, ViewFactory $views, string $name): string
    {
        if (!$request->ajax()) {
            return $this->getCmfConfig()->getUiModule()->renderLayoutView();
        }
        $this->authorize('cmf_page', [$name]);
        $altName = str_replace('-', '_', $name);
        $viewsPrefix = $this->getCmfConfig()->getUiModule()->getCustomViewsPrefix();
        $isModal = (bool)$request->query('modal', false);
        $primaryView = $viewsPrefix . 'page.' . $name;
        $dataForView = [
            'isModal' => $isModal,
        ];
        if ($views->exists($primaryView)) {
            return $views->make($primaryView, $dataForView)->render();
        } elseif ($altName !== $name && $views->exists($viewsPrefix . 'page.' . $altName)) {
            return $views->make($viewsPrefix . 'page.' . $altName, $dataForView)->render();
        } elseif ($views->exists('cmf::page.' . $name)) {
            return $views->make('cmf::page.' . $name, $dataForView)->render();
        }
        abort($this->getViewNotFoundResponse($request, $name, $primaryView));
    }
    
    protected function getViewNotFoundResponse(Request $request, string $pageName, $primaryView): JsonResponse
    {
        return CmfJsonResponse::create(HttpCode::NOT_FOUND)
            ->setMessage("View file for page {$pageName} not found at {$primaryView}")
            ->goBack($this->getCmfConfig()->home_page_url());
    }
    
    public function getCustomUiView($viewName): string
    {
        return $this->getCmfConfig()->getUiModule()->renderUIView($viewName);
    }
    
    public function redirectToUserProfile(): RedirectResponse
    {
        return new RedirectResponse($this->getCmfConfig()->route('cmf_profile'));
    }
    
    public function renderUserProfileView(): string
    {
        return $this->getCmfConfig()->getAuthModule()->renderUserProfilePageView();
    }
    
    public function updateUserProfile(Request $request): JsonResponse
    {
        return $this->getCmfConfig()->getAuthModule()->processUserProfileUpdateRequest($request);
    }
    
    public function getBasicUiView(): string
    {
        return $this->getCmfConfig()->getUiModule()->renderBasicUIView();
    }
    
    /**
     * @param string|null $locale
     * @return RedirectResponse
     */
    public function switchLocale(?string $locale = null): RedirectResponse
    {
        $this->getCmfConfig()->setLocale($locale);
        
        return $this->app->make(Redirector::class)->back();
    }
    
    public function ping(Request $request, Router $router): JsonResponse
    {
        $url = $request->input('url');
        $otherRequest = Request::create($url, 'GET');
        try {
            $route = $router->getRoutes()->match($otherRequest);
            $authClasses = [
                preg_quote(Authenticate::class, '%'),
                preg_quote(CmfAuth::class, '%'),
            ];
            $regexp = '%^(auth(:|$)|' . implode('|', $authClasses) . ')%';
            foreach ($router->gatherRouteMiddleware($route) as $middleware) {
                if (preg_match($regexp, $middleware)) {
                    // route requires auth
                    if (!$this->getCmfConfig()->getAuthGuard()->check()) {
                        abort(new JsonResponse([], HttpCode::UNAUTHORISED));
                    }
                    break;
                }
            }
        } catch (NotFoundHttpException $exc) {
        }
        return new JsonResponse([]);
    }
    
    public function getLoginTpl(): string
    {
        return $this->getCmfConfig()->getAuthModule()->renderUserLoginPageView();
    }
    
    public function doLogin(Request $request): JsonResponse
    {
        return $this->getCmfConfig()->getAuthModule()->processUserLoginRequest($request);
    }
    
    public function getRegistrationTpl(): string
    {
        return $this->getCmfConfig()->getAuthModule()->renderUserRegistrationPageView();
    }
    
    public function doRegister(Request $request): JsonResponse
    {
        return $this->getCmfConfig()->getAuthModule()->processUserRegistrationRequest($request);
    }
    
    public function getForgotPasswordTpl(): string
    {
        return $this->getCmfConfig()->getAuthModule()->renderForgotPasswordPageView();
    }
    
    public function sendPasswordReplacingInstructions(Request $request): JsonResponse
    {
        return $this->getCmfConfig()->getAuthModule()->startPasswordRecoveryProcess($request);
    }
    
    public function getReplacePasswordTpl($accessKey): string
    {
        return $this->getCmfConfig()->getAuthModule()->renderReplaceUserPasswordPageView($accessKey);
    }
    
    public function replacePassword(Request $request, $accessKey): JsonResponse
    {
        return $this->getCmfConfig()->getAuthModule()->finishPasswordRecoveryProcess($request, $accessKey);
    }
    
    public function loginAsOtherUser($otherUserId): JsonResponse
    {
        return $this->getCmfConfig()->getAuthModule()->processLoginAsOtherUserRequest($otherUserId);
    }
    
    public function logout(Request $request): Response
    {
        return $this->getCmfConfig()->getAuthModule()->processUserLogoutRequest($request);
    }
    
    public function getUserProfileData(): JsonResponse
    {
        return new CmfJsonResponse($this->getCmfConfig()->getAuthModule()->getDataForUserProfileForm());
    }
    
    public function getMenuCounters(): JsonResponse
    {
        $user = $this->getCmfConfig()->getUser();
        $this->authorize('resource.details', ['cmf_profile', $user]);
        return new CmfJsonResponse($this->getCmfConfig()->getValuesForMenuItemsCounters());
    }
    
    public function cleanCache(CacheStore $cache): string
    {
        $cache->flush();
        return 'done';
    }
    
    public function getCkeditorConfigJs(): string
    {
        return view(
            'cmf::ui.ckeditor_config',
            ['configs' => $this->getCmfConfig()->ckeditor_config()]
        )->render();
    }
    
    public function ckeditorUploadImage(Request $request): string
    {
        $column = $this->validateImageUpload($request);
        $url = '';
        if (is_string($column)) {
            $message = $column;
            $column = null;
        } else {
            [$url, $message] = $this->saveUploadedImage($column, $request->file('upload'));
        }
        $editorNum = (int)$request->input('CKEditorFuncNum');
        $message = addslashes($message);
        
        return Tag::script()
            ->setType('text/javascript')
            ->setContent("window.parent.CKEDITOR.tools.callFunction({$editorNum}, '{$url}', '{$message}');")
            ->build();
    }
    
    /**
     * @param Request $request
     * @return WysiwygFormInput|string
     */
    protected function validateImageUpload(Request $request)
    {
        $errors = $this->validateAndReturnErrors($request->all(), [
            'CKEditorFuncNum' => 'required|int',
            'CKEditor' => 'required|string',
            'upload' => 'required|image|mimes:jpeg,png,gif,svg|between:1,5064',
        ]);
        if (!empty($errors)) {
            $ret = [];
            foreach ($errors as $param => $errorsForParam) {
                $ret[] = $param . ': ' . (is_array($errorsForParam) ? implode(', ', $errorsForParam) : (string)$errorsForParam);
            }
            
            return implode('<br>', $ret);
        }
        
        $editorId = $request->input('CKEditor');
        
        if (preg_match('%^([^:]+):(.+)$%', $editorId, $matches)) {
            [, $resourceName, $columnName] = $matches;
        } elseif (preg_match('%^t-(.+?)-c-(.+?)-input$%', $matches)) {
            [, $resourceName, $columnName] = $matches;
        } else {
            return $this->getCmfConfig()->transGeneral('.ckeditor.fileupload.cannot_detect_resource_and_field', ['editor_name' => $editorId]);
        }
        $scaffoldConfig = $this->getCmfConfig()->getScaffoldConfig($resourceName);
        $columns = $scaffoldConfig->getFormConfig()->getValueViewers();
        $column = null;
        if (array_key_exists($columnName, $columns)) {
            $column = $columns[$columnName];
        } else {
            foreach ($columns as $name => $columnInfo) {
                if (preg_replace('%[^a-zA-Z0-9-]+%', '_', $name) === $columnName) {
                    $column = $columnInfo;
                    break;
                }
            }
        }
        if (!$column) {
            return $this->getCmfConfig()->transGeneral(
                '.ckeditor.fileupload.cannot_find_field_in_scaffold',
                [
                    'editor_name' => $editorId,
                    'field_name' => $columnName,
                    'scaffold_class' => get_class($scaffoldConfig),
                ]
            );
        }
        if ($column instanceof WysiwygFormInput) {
            if (!$column->hasImageUploadsFolder()) {
                return $this->getCmfConfig()->transGeneral(
                    '.ckeditor.fileupload.image_uploading_folder_not_set',
                    [
                        'field_name' => $columnName,
                        'scaffold_class' => get_class($scaffoldConfig),
                    ]
                );
            }
            return $column;
        } else {
            return $this->getCmfConfig()->transGeneral(
                '.ckeditor.fileupload.is_not_wysiwyg_field_config',
                [
                    'wysywig_class' => WysiwygFormInput::class,
                    'field_name' => $columnName,
                    'scaffold_class' => get_class($scaffoldConfig),
                ]
            );
        }
    }
    
    /**
     * @param WysiwygFormInput $formInput
     * @param UploadedFile $uploadedFile
     * @return array - 0: url to file; 1: message
     */
    protected function saveUploadedImage(WysiwygFormInput $formInput, UploadedFile $uploadedFile): array
    {
        /** @var UploadedFile $uploadedFile */
        Folder::load($formInput->getAbsoluteImageUploadsFolder(), true, 0755);
        $newFileName = Uuid::uuid4()->toString() . ($uploadedFile->getExtension() ?: $uploadedFile->getClientOriginalExtension());
        $file = $uploadedFile->move($formInput->getAbsoluteImageUploadsFolder(), $newFileName);
        $imageProcessor = new \Imagick($file->getRealPath());
        // resize image
        if (
            !$imageProcessor->valid()
            || (
                $imageProcessor->getImageMimeType() === 'image/jpeg'
                && ValidateValue::isCorruptedJpeg($file->getRealPath())
            )
        ) {
            return ['', $this->getCmfConfig()->transGeneral('.ckeditor.fileupload.invalid_or_corrupted_image')];
        }
        if (
            ($formInput->getMaxImageWidth() > 0 && $imageProcessor->getImageWidth() > $formInput->getMaxImageWidth())
            || ($formInput->getMaxImageHeight() > 0 && $imageProcessor->getImageHeight() > $formInput->getMaxImageHeight())
        ) {
            $success = $imageProcessor->resizeImage(
                $formInput->getMaxImageWidth(),
                $formInput->getMaxImageHeight(),
                \Imagick::FILTER_LANCZOS,
                -1,
                true
            );
            if (!$success) {
                return ['', $this->getCmfConfig()->transGeneral('.ckeditor.fileupload.failed_to_resize_image')];
            }
        }
        if (!$imageProcessor->writeImage($file->getRealPath())) {
            return ['', $this->getCmfConfig()->transGeneral('.ckeditor.fileupload.failed_to_save_image_to_fs')];
        }
        $url = $formInput->getRelativeImageUploadsUrl() . $newFileName;
        
        return [$url, ''];
    }
    
    public function downloadApiRequestsCollectionForPostman(Request $request): Response
    {
        $host = $request->getHttpHost();
        $fileName = $this->getCmfConfig()->transCustom('.api_docs.postman_collection_file_name', [
            'http_host' => $host,
        ]);
        $data = [
            'variables' => [],
            'info' => [
                'name' => $host . ' (' . config('app.env') . ')',
                '_postman_id' => sha1($host),
                'description' => '',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.0.0/collection.json',
            ],
            'item' => [],
        ];
        $cmfConfig = $this->getCmfConfig();
        foreach ($cmfConfig->getApiDocumentationModule()->getDocumentationClassesList() as $methodsList) {
            /** @var CmfApiMethodDocumentation $apiMethodDocs */
            foreach ($methodsList as $apiMethodDocs) {
                $docsObject = $apiMethodDocs::create($cmfConfig);
                if (trim($docsObject->getUrl()) === '') {
                    continue;
                }
                $data['item'][] = $docsObject->getConfigForPostman();
            }
        }
        return new Response(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), HttpCode::OK, [
            'Content-type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$fileName}.json\"",
        ]);
    }
    
    public function getCachedUiTemplatesJs(): string
    {
        return view(
            'cmf::ui.cached_templates',
            [
                'pages' => $this->getCmfConfig()->getCachedPagesTemplates(),
                'resources' => $this->getCmfConfig()->getCachedResourcesTemplates(),
            ]
        )->render();
    }
    
}
