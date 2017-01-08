<?php

namespace PeskyCMF\Http;

use Illuminate\Http\JsonResponse;

class CmfJsonResponse extends JsonResponse {

    static protected $messageKey = '_message';
    static protected $redirectKey = 'redirect';
    static protected $redirectFallbakKey = 'redirect_fallback';
    static protected $errorsKey = 'errors';

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
     * @param string $message
     * @return $this
     */
    public function setMessage($message) {
        $data = $this->getData(true);
        $data[static::$messageKey] = $message;
        return $this->setData($data);
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

}