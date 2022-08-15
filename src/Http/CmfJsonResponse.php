<?php

declare(strict_types=1);

namespace PeskyCMF\Http;

use Illuminate\Http\JsonResponse;

/**
 * @method CmfJsonResponse setData($data = [])
 */
class CmfJsonResponse extends JsonResponse
{
    
    public static $messageKey = '_message';
    public static $messageTypeKey = '_message_type';
    public static $redirectKey = 'redirect';
    public static $forcedRedirectKey = 'redirect_with_reload';
    public static $redirectFallbakKey = 'redirect_fallback';
    public static $errorsKey = 'errors';
    public static $modalKey = 'modal';
    public static $modalTitleKey = 'title';
    public static $modalContentKey = 'content';
    public static $modalFooterKey = 'footer';
    public static $modalUrlKey = 'url';
    public static $modalSizeKey = 'size';
    
    public const MODAL_SIZE_MEDIUM = 'medium';
    public const MODAL_SIZE_SMALL = 'small';
    public const MODAL_SIZE_LARGE = 'large';
    
    public const MESSAGE_TYPE_INFO = 'info';
    public const MESSAGE_TYPE_SUCCESS = 'success';
    public const MESSAGE_TYPE_WARNING = 'warning';
    public const MESSAGE_TYPE_ERROR = 'error';
    
    public static function create(int $status = 200, array $headers = [], int $options = JSON_UNESCAPED_UNICODE): CmfJsonResponse
    {
        return new static([], $status, $headers, $options);
    }
    
    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param int $options
     */
    public function __construct($data = null, $status = 200, $headers = [], $options = JSON_UNESCAPED_UNICODE)
    {
        parent::__construct($data ?? [], $status, $headers, $options, false);
    }
    
    public function addData(array $additionalData): CmfJsonResponse
    {
        $data = $this->getData(true);
        $data = array_merge($data, $additionalData);
        return $this->setData($data);
    }
    
    public function setMessage(string $message, ?string $messageType = null): CmfJsonResponse
    {
        if (!empty($message)) {
            $data = $this->getData(true);
            $data[static::$messageKey] = $message;
            if ($messageType) {
                $data[static::$messageTypeKey] = $messageType;
            }
            $this->setData($data);
        }
        return $this;
    }
    
    public function setRedirect($url, ?string $fallbackUrl = null): CmfJsonResponse
    {
        $data = $this->getData(true);
        $data[static::$redirectKey] = $url;
        if (!empty($fallbackUrl)) {
            $data[static::$redirectFallbakKey] = $fallbackUrl;
        }
        return $this->setData($data);
    }
    
    public function setForcedRedirect(string $url): CmfJsonResponse
    {
        $data = $this->getData(true);
        $data[static::$forcedRedirectKey] = $url;
        return $this->setData($data);
    }
    
    public function goBack(?string $fallbackUrl = null): CmfJsonResponse
    {
        return $this->setRedirect('back', $fallbackUrl);
    }
    
    public function reloadPage(): CmfJsonResponse
    {
        return $this->setRedirect('reload');
    }
    
    /**
     * @param array $errors
     * @param string|null $message
     * @return $this
     */
    public function setErrors(array $errors, ?string $message = null): CmfJsonResponse
    {
        $data = $this->getData(true);
        if (!empty($message)) {
            $data[static::$messageKey] = $message;
        }
        $data[static::$errorsKey] = $errors;
        return $this->setData($data);
    }
    
    /**
     * @param string $title
     * @param string $content
     * @param string|null $footer
     * @param string|null $url
     * @param string $modalSize
     * @return $this
     */
    public function setModalContent(
        string $title,
        string $content,
        string $footer = null,
        string $url = null,
        string $modalSize = self::MODAL_SIZE_MEDIUM
    ): CmfJsonResponse {
        $data = $this->getData(true);
        $data[static::$modalKey] = [
            static::$modalTitleKey => $title,
            static::$modalContentKey => $content,
        ];
        if (!empty($footer)) {
            $data[static::$modalKey][static::$modalFooterKey] = $footer;
        }
        if ($modalSize !== self::MODAL_SIZE_MEDIUM) {
            $data[static::$modalKey][static::$modalSizeKey] = $modalSize;
        }
        if (!empty($url)) {
            $data[static::$modalKey][static::$modalUrlKey] = $url;
        }
        return $this->setData($data);
    }
    
}