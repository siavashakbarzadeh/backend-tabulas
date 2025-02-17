<?php

namespace App\Responses\Api;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiResponse
{
    /**
     * Response message.
     *
     * @var string
     */
    private string $message;

    /**
     * Response status code.
     *
     * @var int
     */
    private int $status_code = Response::HTTP_OK;

    /**
     * Array of response the data.
     *
     * @var array
     */
    private array $data = [];

    /**
     * Array of response the errors.
     *
     * @var array
     */
    private array $errors = [];

    /**
     * Response has error or not.
     *
     * @var bool
     */
    private bool $hasError = false;

    /**
     * Prepare a new message.
     *
     * @param string $message
     * @param int $status_code
     * @return ApiResponse
     */
    public function message(string $message, int $status_code = Response::HTTP_OK): ApiResponse
    {
        return $this->setMessage($message)
            ->setStatusCode($status_code);
    }

    /**
     * Generate a new success message.
     *
     * @param string $message
     * @param int $status_code
     * @return JsonResponse
     */
    public function success(string $message, int $status_code = Response::HTTP_OK): JsonResponse
    {
        return $this->setMessage($message)
            ->setStatusCode($status_code)
            ->send();
    }

    /**
     * Generate a new error message.
     *
     * @param string $message
     * @param int $status_code
     * @return JsonResponse
     */
    public function error(string $message, int $status_code = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return $this->setMessage($message)
            ->setStatusCode($status_code)
            ->hasError()
            ->send();
    }

    /**
     * Preparing json response.
     *
     * @return JsonResponse
     */
    public function send(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'status' => $this->getStatusCode(),
            'has_error' => $this->getHasError(),
            'uri' => request()->fullUrl(),
            'errors' => $this->getErrors(),
            'data' => $this->getData(),
        ], $this->getStatusCode());
    }

    /**
     * Get response status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->status_code;
    }

    /**
     * Set response status code.
     *
     * @param int $status_code
     * @return $this
     */
    public function setStatusCode(int $status_code): static
    {
        $this->status_code = $status_code;
        return $this;
    }

    /**
     * Get response message.
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set response message.
     *
     * @param string $message
     * @return $this
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Response has error or not.
     *
     * @return bool
     */
    public function getHasError(): bool
    {
        return $this->hasError;
    }

    /**
     * mark response has an error.
     *
     * @param bool $hasError
     * @return $this
     */
    public function hasError(bool $hasError = true): static
    {
        $this->hasError = $hasError;
        return $this;
    }

    /**
     * Set response errors.
     *
     * @param array $errors
     * @return $this
     */
    public function setErrors(array $errors): static
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Add a single item to errors array.
     *
     * @param $key
     * @param $data
     * @return $this
     */
    public function addError($key, $data): static
    {
        $this->errors = array_merge($this->errors, [$key => $data]);
        return $this;
    }

    /**
     * Get response errors.
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }


    /**
     * Set response data.
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Add a single item to data array.
     *
     * @param $key
     * @param $data
     * @return $this
     */
    public function addData($key, $data): static
    {
        $this->data = array_merge($this->data, [$key => $data]);
        return $this;
    }

    /**
     * Get response data.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

}
