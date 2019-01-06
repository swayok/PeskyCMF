<?php
/** @noinspection ExceptionsAnnotatingAndHandlingInspection */

namespace PeskyCMF\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PeskyCMF\ApiDocs\CmfApiMethodDocumentation;
use PeskyCMF\Auth\Middleware\CmfAuth;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Traits\DataValidationHelper;
use Ramsey\Uuid\Uuid;
use Swayok\Utils\Folder;
use Swayok\Utils\ValidateValue;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CmfGeneralController extends CmfController {

    use DataValidationHelper,
        AuthorizesRequests;

    public function __construct() {

    }

    public function loadJsApp(Request $request) {
        if ($request->ajax()) {
            return response()->json([], 404);
        }
        return static::getCmfConfig()->getUiModule()->renderLayoutView();
    }

    public function getPage(Request $request, $name) {
        if (!$request->ajax()) {
            return static::getCmfConfig()->getUiModule()->renderLayoutView();
        }
        $this->authorize('cmf_page', [$name]);
        $cmfConfig = static::getCmfConfig();
        $altName = str_replace('-', '_', $name);
        $viewsPrefix = $cmfConfig::getUiModule()->getCustomViewsPrefix();
        $isModal = (bool)$request->query('modal', false);
        $primaryView = $viewsPrefix . 'page.' . $name;
        $dataForView = [
            'isModal' => $isModal,
        ];
        if (\View::exists($primaryView)) {
            return view($primaryView, $dataForView)->render();
        } else if ($altName !== $name && \View::exists($viewsPrefix . 'page.' . $altName)) {
            return view($viewsPrefix . 'page.' . $altName, $dataForView)->render();
        } else if (\View::exists('cmf::page.' . $name)) {
            return view('cmf::page.' . $name, $dataForView)->render();
        }
        return $this->pageViewNotFoundResponse($request, $name, $primaryView);
    }

    protected function pageViewNotFoundResponse(Request $request, $pageName, $primaryView) {
        return cmfJsonResponseForHttp404(
            static::getCmfConfig()->home_page_url(),
            'View file not found at ' . $primaryView
        );
    }

    public function getCustomUiView($viewName) {
        return static::getCmfConfig()->getUiModule()->renderUIView($viewName);
    }

    public function redirectToUserProfile() {
        return redirect()->route(static::getCmfConfig()->getRouteName('cmf_profile'));
    }

    public function renderUserProfileView() {
        return static::getCmfConfig()->getAuthModule()->renderUserProfilePageView();
    }

    public function updateUserProfile(Request $request) {
        return static::getCmfConfig()->getAuthModule()->processUserProfileUpdateRequest($request);
    }

    public function getBasicUiView() {
        return static::getCmfConfig()->getUiModule()->renderBasicUIView();
    }

    /**
     * @param null|string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLocale($locale = null) {
        static::getCmfConfig()->setLocale($locale);

        return \Redirect::back();
    }

    public function ping(Request $request) {
        $url = $request->input('url');
        $request = Request::create($url, 'GET');
        try {
            $route = \Route::getRoutes()->match($request);
            $authClasses = [
                preg_quote(\Illuminate\Auth\Middleware\Authenticate::class, '%'),
                preg_quote(CmfAuth::class, '%'),
            ];
            $regexp = '%^(auth(:|$)|' . implode('|', $authClasses) . ')%';
            foreach (\Route::gatherRouteMiddleware($route) as $middleware) {
                if (preg_match($regexp, $middleware)) {
                    // route requires auth
                    if (!static::getCmfConfig()->getAuthGuard()->check()) {
                        return cmfJsonResponse(HttpCode::UNAUTHORISED);
                    }
                    break;
                }
            }
        } catch (NotFoundHttpException $exc) {

        }
        return cmfJsonResponse();
    }

    public function getLoginTpl() {
        return static::getCmfConfig()->getAuthModule()->renderUserLoginPageView();
    }

    public function doLogin(Request $request) {
        return static::getCmfConfig()->getAuthModule()->processUserLoginRequest($request);
    }

    public function getRegistrationTpl() {
        return static::getCmfConfig()->getAuthModule()->renderUserRegistrationPageView();
    }

    public function doRegister(Request $request) {
        return static::getCmfConfig()->getAuthModule()->processUserRegistrationRequest($request);
    }

    public function getForgotPasswordTpl() {
        return static::getCmfConfig()->getAuthModule()->renderForgotPasswordPageView();
    }

    public function sendPasswordReplacingInstructions(Request $request) {
        return static::getCmfConfig()->getAuthModule()->startPasswordRecoveryProcess($request);
    }

    public function getReplacePasswordTpl($accessKey) {
        return static::getCmfConfig()->getAuthModule()->renderReplaceUserPasswordPageView($accessKey);
    }

    public function replacePassword(Request $request, $accessKey) {
        return static::getCmfConfig()->getAuthModule()->finishPasswordRecoveryProcess($request, $accessKey);
    }

    public function loginAsOtherUser($otherUserId) {
        return static::getCmfConfig()->getAuthModule()->processLoginAsOtherUserRequest($otherUserId);
    }

    public function logout() {
        return static::getCmfConfig()->getAuthModule()->processUserLogoutRequest();
    }

    public function getUserProfileData() {
        return cmfJsonResponse()->setData(static::getCmfConfig()->getAuthModule()->getDataForUserProfileForm());
    }

    public function getMenuCounters() {
        $user = static::getCmfConfig()->getUser();
        $this->authorize('resource.details', ['cmf_profile', $user]);
        return cmfJsonResponse()->setData(static::getCmfConfig()->getValuesForMenuItemsCounters());
    }

    public function cleanCache() {
        \Cache::flush();
    }

    public function getCkeditorConfigJs() {
        return view(
            'cmf::ui.ckeditor_config',
            ['configs' => static::getCmfConfig()->ckeditor_config()]
        )->render();
    }

    public function ckeditorUploadImage(Request $request) {
        $column = $this->validateImageUpload($request);
        $url = $message = '';
        if (!is_object($column)) {
            $message = (string)$column;
        } else {
            list($url, $message) = $this->saveUploadedImage($column, $request->file('upload'));
        }
        $editorNum = (int)$request->input('CKEditorFuncNum');
        $message = addslashes($message);

        return "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$editorNum}, '{$url}', '{$message}');</script>";
    }

    protected function validateImageUpload(Request $request) {
        $errors = $this->validateAndReturnErrors($request->all(), [
            'CKEditorFuncNum' => 'required|int',
            'CKEditor' => 'required|string',
            'upload' => 'required|image|mimes:jpeg,png,gif,svg|between:1,5064',
        ]);
        if (!empty($errors)) {
            $ret = [];
            /** @var array $errors */
            foreach ($errors as $param => $errorsForParam) {
                $ret[] = $param . ': ' . (is_array($errorsForParam) ? implode(', ', $errorsForParam) : (string)$errorsForParam);
            }

            return implode('<br>', $ret);
        }

        $editorId = $request->input('CKEditor');

        if (preg_match('%^([^:]+):(.+)$%', $editorId, $matches)) {
            list(, $resourceName, $columnName) = $matches;
        } else if (preg_match('%^t-(.+?)-c-(.+?)-input$%', $matches)) {
            list(, $resourceName, $columnName) = $matches;
        } else {
            return cmfTransGeneral('.ckeditor.fileupload.cannot_detect_resource_and_field', ['editor_name' => $editorId]);
        }
        $scaffoldConfig = static::getCmfConfig()->getScaffoldConfig($resourceName);
        $columns = $scaffoldConfig->getFormConfig()->getValueViewers();
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
        if (empty($column)) {
            return cmfTransGeneral(
                '.ckeditor.fileupload.cannot_find_field_in_scaffold',
                [
                    'editor_name' => $editorId,
                    'field_name' => $columnName,
                    'scaffold_class' => get_class($scaffoldConfig),
                ]
            );
        } else if (!($column instanceof WysiwygFormInput)) {
            return cmfTransGeneral(
                '.ckeditor.fileupload.is_not_wysiwyg_field_config',
                [
                    'wysywig_class' => WysiwygFormInput::class,
                    'field_name' => $columnName,
                    'scaffold_class' => get_class($scaffoldConfig),
                ]
            );
        }
        /** @var WysiwygFormInput $column */
        if (!$column->hasImageUploadsFolder()) {
            return cmfTransGeneral(
                '.ckeditor.fileupload.image_uploading_folder_not_set',
                [
                    'field_name' => $columnName,
                    'scaffold_class' => get_class($scaffoldConfig),
                ]
            );
        }

        return $column;
    }

    /**
     * @param WysiwygFormInput $formInput
     * @param UploadedFile $uploadedFile
     * @return array - 0: url to file; 1: message
     * @throws \Symfony\Component\HttpFoundation\File\Exception\FileException
     */
    protected function saveUploadedImage(WysiwygFormInput $formInput, UploadedFile $uploadedFile) {
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
            return ['', cmfTransGeneral('.ckeditor.fileupload.invalid_or_corrupted_image')];
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
                return ['', cmfTransGeneral('.ckeditor.fileupload.failed_to_resize_image')];
            }
        }
        if (!$imageProcessor->writeImage($file->getRealPath())) {
            return ['', cmfTransGeneral('.ckeditor.fileupload.failed_to_save_image_to_fs')];
        }
        $url = $formInput->getRelativeImageUploadsUrl() . $newFileName;

        return [$url, ''];
    }

    public function downloadApiRequestsCollectionForPostman() {
        $host = \request()->getHttpHost();
        $fileName = cmfTransCustom('.api_docs.postman_collection_file_name', [
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
        foreach (static::getCmfConfig()->getApiDocumentationClasses() as $methodsList) {
            /** @var CmfApiMethodDocumentation $apiMethodDocs */
            foreach ($methodsList as $apiMethodDocs) {
                $docsObject = $apiMethodDocs::create();
                if (trim($docsObject->getUrl()) === '') {
                    continue;
                }
                $data['item'][] = $docsObject->getConfigForPostman();
            }
        }
        return response(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), HttpCode::OK, [
            'Content-type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$fileName}.json\""
        ]);
    }

    public function getCachedUiTemplatesJs() {
        return view(
            'cmf::ui.cached_templates',
            [
                'pages' => static::getCmfConfig()->getCachedPagesTemplates(),
                'resources' => static::getCmfConfig()->getCachedResourcesTemplates()
            ]
        )->render();
    }

}
