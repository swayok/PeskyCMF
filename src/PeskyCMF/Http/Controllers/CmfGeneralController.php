<?php

namespace PeskyCMF\Http\Controllers;

use App\Db\Admins\Admin;
use Auth;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Message;
use Illuminate\Routing\Controller;
use PeskyCMF\Config\CmfConfig;
use PeskyCMF\Db\CmfDbRecord;
use PeskyCMF\Db\Traits\ResetsPasswordsViaAccessKey;
use PeskyCMF\HttpCode;
use PeskyCMF\Scaffold\Form\WysiwygFormInput;
use PeskyCMF\Traits\DataValidationHelper;
use PeskyORM\Core\DbExpr;
use PeskyORM\ORM\Record;
use Ramsey\Uuid\Uuid;
use Redirect;
use Swayok\Utils\Folder;
use Swayok\Utils\Set;
use Swayok\Utils\ValidateValue;

class CmfGeneralController extends Controller {

    use DataValidationHelper;

    public function __construct() {

    }

    public function loadJsApp(Request $request) {
        if ($request->ajax()) {
            return response()->json([], 404);
        }
        return view(CmfConfig::getInstance()->layout_view());
    }

    public function getPage(Request $request, $name) {
        if ($request->ajax()) {
            return view(CmfConfig::getInstance()->custom_views_prefix() . 'page/' . $name)->render();
        } else {
            return view(CmfConfig::getInstance()->layout_view())->render();
        }
    }

    public function getUiView($viewName) {
        $configName = $viewName . '_view';
        $configs = CmfConfig::getInstance();
        if (!method_exists($configs, $configName)) {
            abort(HttpCode::NOT_FOUND, "Config [$configName] not defined");
        }
        return view(CmfConfig::getInstance()->$configName)->render();
    }

    public function getAdminProfile() {
        return view(CmfConfig::getInstance()->user_profile_view(), ['admin' => $this->getAdmin()]);
    }

    public function updateAdminProfile(Request $request) {
        $admin = $this->getAdmin();
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
            'new_password' => 'min:6',
        ];
        $columnsToUpdate = [];
        if ($admin::hasColumn('language')) {
            $validationRules['language'] = 'required|in:' . implode(CmfConfig::getInstance()->locales());
            $columnsToUpdate[] = 'language';
        }
        if ($admin::hasColumn('name')) {
            $validationRules['name'] = 'max:200';
            $columnsToUpdate[] = 'name';
        }
        if ($admin::hasColumn('timezone')) {
            $validationRules['timezone'] = 'required|exists2:pg_timezone_names,name';
            $columnsToUpdate[] = 'timezone';
        }
        $usersTable = CmfConfig::getInstance()->users_table_name();
        $userLoginCol = CmfConfig::getInstance()->user_login_column();
        if ($admin::hasColumn('email')) {
            if ($userLoginCol === 'email') {
                $validationRules['email'] = "required|email|unique:$usersTable,email,{$admin->getAuthIdentifier()},id";
            } else {
                $validationRules['email'] = 'email';
            }
            $columnsToUpdate[] = 'email';
        }
        if ($userLoginCol !== 'email') {
            $validationRules[$userLoginCol] = "required|regex:%^[a-zA-Z0-9_@.-]+$%is|min:4|unique:$usersTable,$userLoginCol,{$admin->getAuthIdentifier()},id";
            $columnsToUpdate[] = $userLoginCol;
        }
        foreach (CmfConfig::getInstance()->additional_user_profile_fields() as $columnName => $rules) {
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
            'urlPrefix' => '/' . CmfConfig::getInstance()->url_prefix(),
        ];
    }

    public function getBasicUiView() {
        $viewData = $this->getDataForBasicUiView();
        return view(CmfConfig::getInstance()->ui_view(), $viewData)->render();
    }

    /**
     * @param null|string $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchLocale($locale = null) {
        if (is_string($locale) && $locale !== '' && in_array($locale, CmfConfig::getInstance()->locales(), true)) {
            \Session::set(CmfConfig::getInstance()->locale_session_key(), strtolower($locale));
        }
        return \Redirect::back();
    }

    static public function getLocale() {
        return \Session::get(
            CmfConfig::getInstance()->locale_session_key(),
            CmfConfig::getInstance()->default_locale()
        );
    }

    public function getLogin(Request $request) {
        if ($request->ajax()) {
            return response()->json([], 404);
        } else if (!Auth::guard()->check()) {
            return view(CmfConfig::getInstance()->layout_view())->render();
        } else {
            return Redirect::to($this->getIntendedUrl());
        }
    }

    public function getReplacePassword(Request $request, $accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (empty($user)) {
            return cmfRedirectResponseWithMessage(
                route(CmfConfig::getInstance()->login_route()),
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
        $userClass = CmfConfig::getInstance()->user_object_class();
        return $userClass::loadFromPasswordRecoveryAccessKey($accessKey);
    }

    public function getLoginTpl() {
        return view(CmfConfig::getInstance()->login_view())->render();
    }

    public function getForgotPasswordTpl() {
        return view(CmfConfig::getInstance()->forgot_password_view())->render();
    }

    public function getReplacePasswordTpl($accessKey) {
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user)) {
            return view(CmfConfig::getInstance()->replace_password_view(), [
                'accessKey' => $accessKey,
                'userId' => $user->getPrimaryKeyValue()
            ])->render();
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setRedirect(route(CmfConfig::getInstance()->login_route()));
        }
    }

    private function getIntendedUrl() {
        $intendedUrl = session()->pull(CmfConfig::getInstance()->session_redirect_key(), false);
        if (!$intendedUrl) {
            return CmfConfig::getInstance()->home_page_url();
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
        $userLoginColumn = CmfConfig::getInstance()->user_login_column();
        $this->validate($request->input(), [
            $userLoginColumn => 'required' . ($userLoginColumn === 'email' ? '|email' : ''),
            'password' => 'required'
        ]);
        $credentials = [
            DbExpr::create("LOWER(`{$userLoginColumn}`) = LOWER(``" . trim($request->input($userLoginColumn)) . '``)'),
            'password' => $request->input('password')
        ];
        if (!Auth::guard()->attempt($credentials)) {
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
        if (Auth::guard()->attempt(['email' => $email], false, false)) {
            /** @var CmfDbRecord|ResetsPasswordsViaAccessKey $user */
            $user = Auth::guard()->getLastAttempted();
            $data = [
                'url' => route('cmf_replace_password', [$user->getPasswordRecoveryAccessKey()]),
                'user' => $user->toArrayWithoutFiles()
            ];
            $subject = cmfTransCustom('.forgot_password.email_subject');
            $from = CmfConfig::getInstance()->system_email_address();
            $view = CmfConfig::getInstance()->password_recovery_email_view();
            \Mail::send($view, $data, function (Message $message) use ($from, $email, $subject) {
                $message
                    ->from($from)
                    ->to($email)
                    ->subject($subject);
            });
        }
        return cmfJsonResponse()
            ->setMessage(cmfTransCustom('.forgot_password.instructions_sent'))
            ->setRedirect(route(CmfConfig::getInstance()->login_route()));
    }

    public function replacePassword(Request $request, $accessKey) {
        $this->validate($request->input(), [
            'id' => 'required|integer|min:1',
            'password' => 'required|min:6',
            'password_confirm' => 'required|min:6|same:password'
        ]);
        $user = $this->getUserFromPasswordRecoveryAccessKey($accessKey);
        if (!empty($user) && $user->getPrimaryKeyValue() !== $request->input('id')) {
            /** @var CmfDbRecord $user */
            $user->begin()->updateValue('password', $request->input('password'), false);
            if ($user->commit()) {
                return cmfJsonResponse()
                    ->setMessage(cmfTransCustom('.replace_password.password_replaced'))
                    ->setRedirect(route(CmfConfig::getInstance()->login_route()));
            } else {
                return cmfJsonResponse(HttpCode::SERVER_ERROR)
                    ->setMessage(cmfTransCustom('.replace_password.failed_to_save'));
            }
        } else {
            return cmfJsonResponse(HttpCode::FORBIDDEN)
                ->setMessage(cmfTransCustom('.replace_password.invalid_access_key'))
                ->setRedirect(route(CmfConfig::getInstance()->login_route()));
        }
    }

    public function logout() {
        Auth::guard()->logout();
        \Session::flush();
        \Session::regenerate(true);
        return Redirect::route(CmfConfig::getInstance()->login_route());
    }

    public function getAdminInfo() {
        $admin = $this->getAdmin();
        $adminData = $admin->toArray();
        if (!empty($adminData['role'])) {
            $adminData['_role'] = $admin->role;
            $role = ($admin->is_superadmin ? 'superadmin' : $admin->role);
            $adminData['role'] = cmfTransCustom('.admins.role.' . $role);
        }
        return response()->json($adminData);
    }

    /**
     * @return Admin
     */
    protected function getAdmin() {
        return Auth::guard()->user();
    }

    public function cleanCache() {
        \Cache::flush();
    }

    public function getCkeditorConfigJs() {
        return view(
            'cmf::ui.ckeditor_config',
            ['configs' => CmfConfig::getInstance()->ckeditor_config()]
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
        } elseif (preg_match('%^t-(.+?)-c-(.+?)-input$%', $matches)) {
            list(, $tableName, $columnName) = $matches;
        } else {
            return cmfTransGeneral('.ckeditor.fileupload.cannot_detect_table_and_field', ['editor_name' => $editorId]);
        }
        $scaffoldConfig = CmfConfig::getInstance()->getScaffoldConfigByTableName($tableName);
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
                    'scaffold_class' => get_class($scaffoldConfig)
                ]
            );
        } else if (!($column instanceof WysiwygFormInput)) {
            return cmfTransGeneral(
                '.ckeditor.fileupload.is_not_wysiwyg_field_config',
                [
                    'wysywig_class' => WysiwygFormInput::class,
                    'field_name' => $columnName,
                    'scaffold_class' => get_class($scaffoldConfig)
                ]
            );
        }
        /** @var WysiwygFormInput $column */
        if (!$column->hasImageUploadsFolder()) {
            return cmfTransGeneral(
                '.ckeditor.fileupload.image_uploading_folder_not_set',
                [
                    'field_name' => $columnName,
                    'scaffold_class' => get_class($scaffoldConfig)
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
        $success = $imageProcessor->writeImage($file->getRealPath());
        if (!$success) {
            return ['', cmfTransGeneral('.ckeditor.fileupload.failed_to_save_image_to_fs')];
        }
        $url = $formInput->getRelativeImageUploadsUrl() . $newFileName;
        return [$url, ''];
    }

}
