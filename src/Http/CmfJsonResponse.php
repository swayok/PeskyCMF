<?php

namespace PeskyCMF\Http;

use Illuminate\Http\JsonResponse;

/**
 * @method CmfJsonResponse setData($data = [])
 */
class CmfJsonResponse extends JsonResponse {

    static protected $messageKey = '_message';
    static protected $redirectKey = 'redirect';
    static protected $forcedRedirectKey = 'redirect_with_reload';
    static protected $redirectFallbakKey = 'redirect_fallback';
    static protected $errorsKey = 'errors';
    static protected $modalKey = 'modal';
    static protected $modalTitleKey = 'title';
    static protected $modalContentKey = 'content';
    static protected $modalFooterKey = 'footer';
    static protected $modalUrlKey = 'url';
    static protected $modalSizeKey = 'size';

    const MODAL_SIZE_MEDIUM = 'medium';
    const MODAL_SIZE_SMALL = 'small';
    const MODAL_SIZE_LARGE = 'large';

    /**
     * CmfJsonResponse constructor.
     * @param array $data
     * @param int $status
     * @param array $headers
     * @param int $options
     */
    public function __construct(array $data = null, $status = 200, array $headers = [], $options = 0) {
        parent::__construct($data === null ? [] : $data, $status, $headers, $options);
    }

    /**
     * @param array $additionalData
     * @return CmfJsonResponse
     */
    public function addData(array $additionalData) {
        $data = $this->getData(true);
        $data = array_merge($data, $additionalData);
        return $this->setData($data);
    }

    /**
     * @param string $message
     * @return $this
     */
    public function setMessage($message) {
        if (!empty($message)) {
            $data = $this->getData(true);
            $data[static::$messageKey] = $message;
            $this->setData($data);
        }
        return $this;
    }

    /**
     * @param string $url
     * @param string|null $fallbackUrl
     * @return $this
     */
    public function setRedirect($url, $fallbackUrl = null) {
        $data = $this->getData(true);
        $data[static::$redirectKey] = $url;
        if (!empty($fallbackUrl)) {
            $data[static::$redirectFallbakKey] = $fallbackUrl;
        }
        return $this->setData($data);
    }

    /**
     * Redirect using document.location instead of JS router
     * @param string $url
     * @return $this
     */
    public function setForcedRedirect($url) {
        $data = $this->getData(true);
        $data[static::$forcedRedirectKey] = $url;
        return $this->setData($data);
    }

    /**
     * @param string|null $fallbakUrl
     * @return $this
     */
    public function goBack($fallbakUrl = null) {
        return $this->setRedirect('back', $fallbakUrl);
    }

    /**
     * @return $this
     */
    public function reloadPage() {
        return $this->setRedirect('reload');
    }

    /**
     * @param array $errors
     * @param null|string $message
     * @return $this
     */
    public function setErrors(array $errors, $message = null) {
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
     * @param string $footer
     * @param null|string $url
     * @param string $modalSize
     * @return $this
     */
    public function setModalContent($title, $content, $footer = null, $url = null, $modalSize = self::MODAL_SIZE_MEDIUM) {
        $data = $this->getData(true);
        $data[static::$modalKey] = [
            static::$modalTitleKey => (string)$title,
            static::$modalContentKey => (string)$content,
        ];
        if (!empty($footer)) {
            $data[static::$modalKey][static::$modalFooterKey] = (string)$footer;
        }
        if ($modalSize !== self::MODAL_SIZE_MEDIUM) {
            $data[static::$modalKey][static::$modalSizeKey] = $modalSize;
        }
        if (!empty($url)) {
            $data[static::$modalKey][static::$modalUrlKey] = (string)$url;
        }
        return $this->setData($data);
    }

}