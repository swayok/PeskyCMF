<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Message;
use PeskyCMF\ApiDocs\CmfApiMethodDocumentation;
use PeskyCMF\Db\Admins\CmfAdmin;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Record;
use PeskyORM\ORM\RecordInterface;
use Ramsey\Uuid\Uuid;
use Swayok\Utils\Folder;
use Swayok\Utils\Set;
use Swayok\Utils\ValidateValue;

class CmfGeneralController extends CmfController {

    use DataValidationHelper,
        AuthorizesRequests;

    protected $originalUserFromLoginAsActionSessionKey = '__original_user';

    public function __construct() {

    }

    public function loadJsApp(Request $request) {
        if ($request->ajax()) {
            return response()->json([], 404);
        }

        return view(static::getCmfConfig()->layout_view());
    }

    public function getPage(Request $request, $name) {
        if ($request->ajax()) {
            $this->authorize('cmf_page', [$name]);
            if (
                !\View::exists(static::getCmfConfig()->custom_views_prefix() . 'page.' . $name)
                && \View::exists('cmf::page.' . $name)
            ) {
                return view('cmf::page.' . $name)->render();
            }
            return view(static::getCmfConfig()->custom_views_prefix() . 'page.' . $name)->render();
        } else {
            return view(static::getCmfConfig()->layout_view())->render();
        }
    }

    public function getUiView($viewName) {
        $configName = $viewName . '_view';
        $configs = static::getCmfConfig();
        if (!method_exists($configs, $configName)) {
            abort(HttpCode::NOT_FOUND, "Config [$configName] not defined");
        }

        return view(static::getCmfConfig()->$configName)->render();
    }

    public function redirectToUserProfile() {
        return redirect()->route(static::getCmfConfig()->getRouteName('cmf_profile'));
    }

    public function getAdminProfile() {
        $admin = static::getCmfConfig()->getUser();
        $this->authorize('resource.details', ['cmf_profile', $admin]);
        return view(static::getCmfConfig()->user_profile_view(), [
            'admin' => $admin,
            'canSubmit' => \Gate::allows('resource.update', ['cmf_profile', $admin])
        ]);
    }

    public function updateAdminProfile(Request $request) {
        $admin = static::getCmfConfig()->getUser();
        $this->authorize('resource.update', ['cmf_profile', $admin]);
        $updates = $this->validateAndGetAdminProfileUpdates($request, $admin);
        if (!is_array($updates)) {
            return $updates;
        } else {
            $admin
                ->begin()
                ->updateValues($updates);
            if (!empty(trim($request->input('new_password')))) {
                $admin->setPassword($request->input('new_password'));
            }
            if ($admin->commit()) {
                return cmfJsonResponse()
                    ->setMessage(cmfTransCustom('.page.profile.saved'))
                    ->reloadPage();
            } else {
                return cmfJsonResponse(HttpCode::SERVER_ERROR)
                    ->setMessage(cmfTransGeneral('.form.failed_to_save_resource_data'))
                    ->reloadPage();
            }
        }
    }

    /**
     * @param Request $request
     * @param Record|Authenticatable $admin
     * @return array|\Illuminate\Http\JsonResponse
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function validateAndGetAdminProfileUpdates(Request $request, Record $admin) {
        $validationRules = [
            'old_password' => 'required',
            'new_password' => 'nullable|min:6',
        ];
        $columnsToUpdate = [];
        if ($admin::hasColumn('language')) {
            $validationRules['language'] = 'required|in:' . implode(',', static::getCmfConfig()->locales());
            $columnsToUpdate[] = 'language';
        }
        if ($admin::hasColumn('name')) {
            $validationRules['name'] = 'nullable|max:200';
            $columnsToUpdate[] = 'name';
        }
        if ($admin::hasColumn('timezone')) {
            $validationRules['timezone'] = 'nullable|exists:pg_timezone_names,name';
            $columnsToUpdate[] = 'timezone';
        }
        $usersTable = static::getCmfConfig()->users_table()->getName();
        $userLoginCol = static::getCmfConfig()->user_login_column();
        if ($admin::hasColumn('email')) {
            if ($userLoginCol === 'email') {
                $validationRules['email'] = "required|email|unique:$usersTable,email,{$admin->getAuthIdentifier()},id";
            } else {
                $validationRules['email'] = 'nullable|email';
            }
            $columnsToUpdate[] = 'email';
        }
        if ($userLoginCol !== 'email') {
            $validationRules[$userLoginCol] = "required|regex:%^[a-zA-Z0-9_@.-]+$%is|min:4|unique:$usersTable,$userLoginCol,{$admin->getAuthIdentifier()},id";
            $columnsToUpdate[] = $userLoginCol;
        }
        foreach (static::getCmfConfig()->additional_user_profile_fields() as $columnName => $rules) {
            if (is_int($columnName)) {
                $columnName = $rules;
            } else {
                $validationRules[$columnName] = $rules;
            }
            $columnsToUpdate[] = $columnName;
        }
        $validator = \Validator::make(
            $request->all(),
            $validationRules,
            Set::flatten(cmfTransCustom('.page.profile.errors'))
        );
        $errors = [];
        if ($validator->fails()) {
            $errors = $validator->getMessageBag()->toArray();
        } else if (method_exists($admin, 'checkPassword')) {
            if (!$admin->checkPassword($request->input('old_password'))) {
                $errors['old_password'] = cmfTransCustom('.page.profile.errors.old_password.match');
            }
        } else if (!\Hash::check($request->input('old_password'), $admin->getAuthPassword())) {
            $errors['old_password'] = cmfTransCustom('.page.profile.errors.old_password.match');
        }
        if (count($errors) > 0) {
            return $this->makeValidationErrorsJsonResponse($errors);
        }

        return $request->only($columnsToUpdate);
    }

    protected function getDataForBasicUiView() {
        return [
            'urlPrefix' => '/' . static::getCmfConfig()->url_prefix(),
        ];
    }

    public function getBasicUiView() {
        $viewData = $this->getDataForBasicUiView();

        return view(static::getCmfConfig()->ui_view(), $viewData)->render();
    }

    /**
     * @param null|string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLocale($locale = null) {
        static::getCmfConfig()->setLocale($locale);

        return \Redirect::back();
    }

    public function getLogin(Request $request) {
        if ($request->ajax()) {
            return response()->json([], 404);
        } else if (!static::getCmfConfig()->getAuth()->check()) {
            return view(static::getCmfConfig()->layout_view())->render();
        } else {
            return \Redirect::to($this->getIntendedUrl());
        }
    }

    public function getReplacePassword(Request $request, $accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (empty($user)) {
            return cmfRedirectResponseWithMessage(
                static::getCmfConfig()->login_page_url(),
                cmfTransCustom('.replace_password.invalid_access_key'),
                'error'
            );
        }

        return $this->loadJsApp($request);
    }

    /**
     * @param $accessKey
     * @return bool|CmfDbRecord
     * @throws \UnexpectedValueException
     * @throws \PeskyORM\Exception\OrmException
     * @throws \PeskyORM\Exception\InvalidDataException
     * @throws \PDOException
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    protected function getUserFromPasswordRecoveryAccessKey($accessKey) {
        /** @var ResetsPasswordsViaAccessKey $userClass */
        $userClass = static::getCmfConfig()->user_record_class();

        return $userClass::loadFromPasswordRecoveryAccessKey($accessKey);
    }

    public function getLoginTpl() {
        return view(static::getCmfConfig()->login_view())->render();
    }

    public function getForgotPasswordTpl() {
        return view(static::getCmfConfig()->forgot_password_view())->render();
    }

    public function getReplacePasswordTpl($accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user)) {
            return view(static::getCmfConfig()->replace_password_view(), [
                'accessKey' => $accessKey,
                'userId' => $user->getPrimaryKeyValue(),
                'userLogin' => $user->getValue(static::getCmfConfig()->user_login_column())
            ])->render();
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setRedirect(static::getCmfConfig()->login_page_url());
        }
    }

    private function getIntendedUrl() {
        $intendedUrl = session()->pull(static::getCmfConfig()->session_redirect_key(), false);
        if (!$intendedUrl) {
            return static::getCmfConfig()->home_page_url();
        } else {
            if (preg_match('%/api/([^/]+?)/list/?$%i', $intendedUrl, $matches)) {
                return routeToCmfItemsTable($matches[1]);
            } else if (preg_match('%/api/([^/]+?)/service/%i', $intendedUrl, $matches)) {
                return routeToCmfItemsTable($matches[1]);
            } else if (preg_match('%/api/([^/]+?)/([^/?]+?)/?(?:\?details=(\d)|$)%i', $intendedUrl, $matches)) {
                if (!empty($matches[3]) && $matches[3] === '1') {
                    return routeToCmfItemDetails($matches[1], $matches[2]);
                } else {
                    return routeToCmfItemEditForm($matches[1], $matches[2]);
                }
            } else if (preg_match('%/api/([^/]+?)%i', $intendedUrl, $matches)) {
                return routeToCmfItemsTable($matches[1]);
            } else if (preg_match('%/page/([^/]+)\.html$%i', $intendedUrl, $matches)) {
                return routeToCmfPage($matches[1]);
            } else {
                return $intendedUrl;
            }
        }
    }

    public function doLogin(Request $request) {
        $userLoginColumn = static::getCmfConfig()->user_login_column();
        $data = $this->validate($request, [
            $userLoginColumn => 'required' . ($userLoginColumn === 'email' ? '|email' : ''),
            'password' => 'required',
        ]);
        $credentials = [
            DbExpr::create("LOWER(`{$userLoginColumn}`) = LOWER(``" . trim($data[$userLoginColumn]) . '``)'),
            'password' => $data['password'],
        ];
        if (!static::getCmfConfig()->getAuth()->attempt($credentials)) {
            return cmfJsonResponse(HttpCode::INVALID)
                ->setMessage(cmfTransCustom('.login_form.login_failed'));
        } else {
            return cmfJsonResponse()->setRedirect($this->getIntendedUrl());
        }
    }

    public function sendPasswordReplacingInstructions(Request $request) {
        $data = $this->validate($request, [
            'email' => 'required|email',
        ]);
        $email = strtolower(trim($data['email']));
        if (static::getCmfConfig()->getAuth()->once(['email' => $email])) {
            /** @var CmfDbRecord|ResetsPasswordsViaAccessKey $user */
            $user = static::getCmfConfig()->getAuth()->getLastAttempted();
            if (!method_exists($user, 'getPasswordRecoveryAccessKey')) {
                throw new \BadMethodCallException(
                    'Class ' . get_class($user) . ' does not support password recovery. You should add ' .
                    ResetsPasswordsViaAccessKey::class . ' trait to specified class to enable this functionality'
                );
            }
            $data = [
                'url' => cmfRoute('cmf_replace_password', [$user->getPasswordRecoveryAccessKey()]),
                'user' => $user->toArrayWithoutFiles(),
            ];
            $subject = cmfTransCustom('.forgot_password.email_subject');
            $from = static::getCmfConfig()->system_email_address();
            $view = static::getCmfConfig()->password_recovery_email_view();
            \Mail::send($view, $data, function (Message $message) use ($from, $email, $subject) {
                $message
                    ->from($from)
                    ->to($email)
                    ->subject($subject);
            });
        }

        return cmfJsonResponse()
            ->setMessage(cmfTransCustom('.forgot_password.instructions_sent'))
            ->setRedirect(static::getCmfConfig()->login_page_url());
    }

    public function replacePassword(Request $request, $accessKey) {
        $data = $this->validate($request, [
            'id' => 'required|integer|min:1',
            'password' => 'required|min:6',
            'password_confirm' => 'required|min:6|same:password',
        ]);
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user) && $user->getPrimaryKeyValue() !== $data['id']) {
            /** @var CmfDbRecord $user */
            $user
                ->begin()
                ->updateValue('password', $data['password'], false)
                ->commit();
            return cmfJsonResponse()
                ->setMessage(cmfTransCustom('.replace_password.password_replaced'))
                ->setRedirect(static::getCmfConfig()->login_page_url());
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setRedirect(static::getCmfConfig()->login_page_url());
        }
    }

    public function loginAs($otherUserId) {
        $this->authorize('cmf_page', ['login_as']);
        $currentUser = static::getUser();
        $currentUserId = $currentUser->getAuthIdentifier();
        if ($currentUserId === $otherUserId || $currentUserId === (int)$otherUserId) {
            return cmfJsonResponse(HttpCode::CANNOT_PROCESS)
                ->setMessage(cmfTransCustom('admins.login_as.same_user'));
        }
        $token = $currentUser->getRememberToken();
        if (!$token) {
            return cmfJsonResponse(HttpCode::CANNOT_PROCESS)
                ->setMessage(cmfTransCustom('admins.login_as.no_auth_token'));
        }
        /** @var \PeskyCMF\Db\Admins\CmfAdmin|RecordInterface $otherUser */
        $otherUser = static::getAuthGuard()->loginUsingId($otherUserId);
        if (!is_object($otherUser)) {
            // Warning: do not use Auth->login($currentUser) - it might fail
            static::getAuthGuard()->loginUsingId($currentUserId, false);
            return cmfJsonResponse(HttpCode::CANNOT_PROCESS)
                ->setMessage(cmfTransCustom('admins.login_as.fail', ['id' => $otherUserId]));
        }
        $cmfConfig = static::getCmfConfig();
        \Session::put([
            $this->originalUserFromLoginAsActionSessionKey => [
                'id' => $currentUserId,
                'token' => $token,
                'url' => \URL::previous($cmfConfig::home_page_url(true)),
            ],
            $cmfConfig::session_message_key() => cmfTransCustom(
                'admins.login_as.success',
                ['user' => $otherUser->getValue($cmfConfig::user_login_column())]
            )
        ]);
        return cmfJsonResponse()
            ->setRedirect($cmfConfig::home_page_url());
    }

    public function logout() {
        $cmfConfig = static::getCmfConfig();
        if (\Session::has($this->originalUserFromLoginAsActionSessionKey)) {
            // logout to original account after 'login_as'
            $userInfo = \Session::pull($this->originalUserFromLoginAsActionSessionKey);
            $user = static::getAuthGuard()->getProvider()->retrieveByToken(
                array_get($userInfo, 'id', -1),
                array_get($userInfo, 'token', -1)
            );
            if ($user) {
                // Warning: do not use Auth->login($user) - it will fail to login previous user
                static::getAuthGuard()->loginUsingId($user->getAuthIdentifier(), false);
                return \Redirect::to(array_get($userInfo, 'url') ?: $cmfConfig::login_page_url(true));
            }
        }
        static::getAuthGuard()->logout();
        \Session::invalidate();
        $cmfConfig::resetLocale();
        return \Redirect::to($cmfConfig::login_page_url(true));
    }

    public function getAdminInfo() {
        /** @var CmfAdmin $admin */
        $admin = static::getCmfConfig()->getUser();
        $this->authorize('resource.details', ['cmf_profile', $admin]);
        $adminData = $admin->toArray();
        if (!empty($adminData['role'])) {
            $adminData['_role'] = $admin->role;
            $role = $admin->role;
            if ($admin::hasColumn('is_superadmin') && $admin->is_superadmin) {
                $role = 'superadmin';
            }
            $adminData['role'] = cmfTransCustom('.admins.role.' . $role);
        }

        return cmfJsonResponse()->setData($adminData);
    }

    public function getMenuCounters() {
        $admin = static::getCmfConfig()->getUser();
        $this->authorize('resource.details', ['cmf_profile', $admin]);
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
            list(, $tableName, $columnName) = $matches;
        } else if (preg_match('%^t-(.+?)-c-(.+?)-input$%', $matches)) {
            list(, $tableName, $columnName) = $matches;
        } else {
            return cmfTransGeneral('.ckeditor.fileupload.cannot_detect_table_and_field', ['editor_name' => $editorId]);
        }
        $scaffoldConfig = static::getCmfConfig()->getScaffoldConfig($tableName);
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
