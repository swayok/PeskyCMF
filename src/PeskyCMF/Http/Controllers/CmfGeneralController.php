<?php

namespace PeskyCMF\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Message;
use Illuminate\Routing\Controller;
use PeskyCMF\ApiDocs\CmfApiDocsSection;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Record;
use Ramsey\Uuid\Uuid;
use Swayok\Utils\Folder;
use Swayok\Utils\Set;
use Swayok\Utils\ValidateValue;

class CmfGeneralController extends Controller {

    use DataValidationHelper,
        AuthorizesRequests;

    public function __construct() {

    }

    public function loadJsApp(Request $request) {
        if ($request->ajax()) {
            return response()->json([], 404);
        }

        return view(CmfConfig::getPrimary()->layout_view());
    }

    public function getPage(Request $request, $name) {
        if ($request->ajax()) {
            $this->authorize('cmf_page', [$name]);
            if (
                !\View::exists(CmfConfig::getPrimary()->custom_views_prefix() . 'page.' . $name)
                && \View::exists('cmf::page.' . $name)
            ) {
                return view('cmf::page.' . $name)->render();
            }
            return view(CmfConfig::getPrimary()->custom_views_prefix() . 'page.' . $name)->render();
        } else {
            return view(CmfConfig::getPrimary()->layout_view())->render();
        }
    }

    public function getUiView($viewName) {
        $configName = $viewName . '_view';
        $configs = CmfConfig::getPrimary();
        if (!method_exists($configs, $configName)) {
            abort(HttpCode::NOT_FOUND, "Config [$configName] not defined");
        }

        return view(CmfConfig::getPrimary()->$configName)->render();
    }

    public function getAdminProfile() {
        $admin = CmfConfig::getPrimary()->getUser();
        $this->authorize('resource.details', ['cmf_profile', $admin]);
        return view(CmfConfig::getPrimary()->user_profile_view(), ['admin' => $admin]);
    }

    public function updateAdminProfile(Request $request) {
        $admin = CmfConfig::getPrimary()->getUser();
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
            $validationRules['language'] = 'required|in:' . implode(',', CmfConfig::getPrimary()->locales());
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
        $usersTable = CmfConfig::getPrimary()->users_table_name();
        $userLoginCol = CmfConfig::getPrimary()->user_login_column();
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
        foreach (CmfConfig::getPrimary()->additional_user_profile_fields() as $columnName => $rules) {
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
            return cmfJsonResponseForValidationErrors($errors);
        }

        return $request->only($columnsToUpdate);
    }

    protected function getDataForBasicUiView() {
        return [
            'urlPrefix' => '/' . CmfConfig::getPrimary()->url_prefix(),
        ];
    }

    public function getBasicUiView() {
        $viewData = $this->getDataForBasicUiView();

        return view(CmfConfig::getPrimary()->ui_view(), $viewData)->render();
    }

    /**
     * @param null|string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLocale($locale = null) {
        CmfConfig::getPrimary()->setLocale($locale);

        return \Redirect::back();
    }

    public function getLogin(Request $request) {
        if ($request->ajax()) {
            return response()->json([], 404);
        } else if (!CmfConfig::getPrimary()->getAuth()->check()) {
            return view(CmfConfig::getPrimary()->layout_view())->render();
        } else {
            return \Redirect::to($this->getIntendedUrl());
        }
    }

    public function getReplacePassword(Request $request, $accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (empty($user)) {
            return cmfRedirectResponseWithMessage(
                CmfConfig::getPrimary()->login_page_url(),
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
        $userClass = CmfConfig::getPrimary()->user_record_class();

        return $userClass::loadFromPasswordRecoveryAccessKey($accessKey);
    }

    public function getLoginTpl() {
        return view(CmfConfig::getPrimary()->login_view())->render();
    }

    public function getForgotPasswordTpl() {
        return view(CmfConfig::getPrimary()->forgot_password_view())->render();
    }

    public function getReplacePasswordTpl($accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user)) {
            return view(CmfConfig::getPrimary()->replace_password_view(), [
                'accessKey' => $accessKey,
                'userId' => $user->getPrimaryKeyValue(),
            ])->render();
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setRedirect(CmfConfig::getPrimary()->login_page_url());
        }
    }

    private function getIntendedUrl() {
        $intendedUrl = session()->pull(CmfConfig::getPrimary()->session_redirect_key(), false);
        if (!$intendedUrl) {
            return CmfConfig::getPrimary()->home_page_url();
        } else {
            if (preg_match('%/api/([^/]+?)/list/?$%i', $intendedUrl, $matches)) {
                return routeToCmfItemsTable($matches[1]);
            } else if (preg_match('%/api/([^/]+?)/service/%i', $intendedUrl, $matches)) {
                return routeToCmfItemsTable($matches[1]);
            } else if (preg_match('%/api/([^/]+?)/([^/]+?)/?(?:details=(\d)|$)%i', $intendedUrl, $matches)) {
                if ($matches[3] === '1') {
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
        $userLoginColumn = CmfConfig::getPrimary()->user_login_column();
        $this->validate($request->input(), [
            $userLoginColumn => 'required' . ($userLoginColumn === 'email' ? '|email' : ''),
            'password' => 'required',
        ]);
        $credentials = [
            DbExpr::create("LOWER(`{$userLoginColumn}`) = LOWER(``" . trim($request->input($userLoginColumn)) . '``)'),
            'password' => $request->input('password'),
        ];
        if (!CmfConfig::getPrimary()->getAuth()->attempt($credentials)) {
            return cmfJsonResponse(HttpCode::INVALID)
                ->setMessage(cmfTransCustom('.login_form.login_failed'));
        } else {
            return cmfJsonResponse()->setRedirect($this->getIntendedUrl());
        }
    }

    public function sendPasswordReplacingInstructions(Request $request) {
        $this->validate($request->input(), [
            'email' => 'required|email',
        ]);
        $email = strtolower(trim($request->input('email')));
        if (CmfConfig::getPrimary()->getAuth()->once(['email' => $email])) {
            /** @var CmfDbRecord|ResetsPasswordsViaAccessKey $user */
            $user = CmfConfig::getPrimary()->getAuth()->getLastAttempted();
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
            $from = CmfConfig::getPrimary()->system_email_address();
            $view = CmfConfig::getPrimary()->password_recovery_email_view();
            \Mail::send($view, $data, function (Message $message) use ($from, $email, $subject) {
                $message
                    ->from($from)
                    ->to($email)
                    ->subject($subject);
            });
        }

        return cmfJsonResponse()
            ->setMessage(cmfTransCustom('.forgot_password.instructions_sent'))
            ->setRedirect(CmfConfig::getPrimary()->login_page_url());
    }

    public function replacePassword(Request $request, $accessKey) {
        $this->validate($request->input(), [
            'id' => 'required|integer|min:1',
            'password' => 'required|min:6',
            'password_confirm' => 'required|min:6|same:password',
        ]);
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user) && $user->getPrimaryKeyValue() !== $request->input('id')) {
            /** @var CmfDbRecord $user */
            $user->begin()->updateValue('password', $request->input('password'), false);
            if ($user->commit()) {
                return cmfJsonResponse()
                    ->setMessage(cmfTransCustom('.replace_password.password_replaced'))
                    ->setRedirect(CmfConfig::getPrimary()->login_page_url());
            } else {
                return cmfJsonResponse(HttpCode::SERVER_ERROR)
                    ->setMessage(cmfTransCustom('.replace_password.failed_to_save'));
            }
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setRedirect(CmfConfig::getPrimary()->login_page_url());
        }
    }

    public function logout() {
        CmfConfig::getPrimary()->getAuth()->logout();
        \Session::invalidate();
        CmfConfig::getPrimary()->resetLocale();

        return \Redirect::to(CmfConfig::getPrimary()->login_page_url(true));
    }

    public function getAdminInfo() {
        $admin = CmfConfig::getPrimary()->getUser();
        $this->authorize('resource.details', ['cmf_profile', $admin]);
        $adminData = $admin->toArray();
        if (!empty($adminData['role'])) {
            $adminData['_role'] = $admin->role;
            $role = ($admin->is_superadmin ? 'superadmin' : $admin->role);
            $adminData['role'] = cmfTransCustom('.admins.role.' . $role);
        }

        return cmfJsonResponse()->setData($adminData);
    }

    public function getMenuCounters() {
        $admin = CmfConfig::getPrimary()->getUser();
        $this->authorize('resource.details', ['cmf_profile', $admin]);
        return cmfJsonResponse()->setData(CmfConfig::getPrimary()->getValuesForMenuItemsCounters());
    }

    public function cleanCache() {
        \Cache::flush();
    }

    public function getCkeditorConfigJs() {
        return view(
            'cmf::ui.ckeditor_config',
            ['configs' => CmfConfig::getPrimary()->ckeditor_config()]
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
        $errors = $this->validateWithoutHalt($request->all(), [
            'CKEditorFuncNum' => 'required|int',
            'CKEditor' => 'required|string',
            'upload' => 'required|image|mimes:jpeg,png,gif,svg|between:1,5064',
        ]);
        if (is_array($errors)) {
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
        $scaffoldConfig = CmfConfig::getPrimary()->getScaffoldConfig($tableName);
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
        foreach (CmfConfig::getPrimary()->getApiDocsSections() as $methodsList) {
            /** @var CmfApiDocsSection $apiMethodDocs */
            foreach ($methodsList as $apiMethodDocs) {
                $data['item'][] = $apiMethodDocs::create()->getConfigForPostman();
            }
        }
        return response(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), HttpCode::OK, [
            'Content-type' => 'application/json',
            'Content-Disposition' => "attachment; filename=\"{$fileName}.json\""
        ]);
    }

}
