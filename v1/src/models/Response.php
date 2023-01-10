<?php

namespace v1\Models;

class Response
{
    private $_success;
    private int $_httpStatusCode;
    private $_messages = array();
    private $_data;
    private $_toCache = false;
    private $_responseData = array();

    public function setSuccess($success)
    {
        $this->_success = $success;
    }

    public function setHttpStatusCode($httpStatusCode)
    {
        $this->_httpStatusCode = $httpStatusCode;
    }

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function addMessage($message)
    {
        $this->_messages[] = $message;
    }

    public function toCache($toCache)
    {
        $this->_toCache = $toCache;
    }

    public function notAcceptable($messages = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-cache, no-store');

        http_response_code(HttpStatusCode::NOT_ACCEPTABLE->value);

        $this->setHttpStatusCode(HttpStatusCode::NOT_ACCEPTABLE->value);
        $this->setSuccess(false);
        if (is_array($messages) && !empty($messages)) {
            $this->_messages = [...$this->_messages, ...$messages];
        } else {
            $this->addMessage('Not acceptable');
        }
        $this->send();
    }

    public function conflict($messages = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-cache, no-store');

        http_response_code(HttpStatusCode::CONFLICT->value);

        $this->setHttpStatusCode(HttpStatusCode::CONFLICT->value);
        $this->setSuccess(false);
        if (is_array($messages) && !empty($messages)) {
            $this->_messages = [...$this->_messages, ...$messages];
        } else {
            $this->addMessage('Conflict');
        }
        $this->send();
    }

    public function methodNotAllowed($messages = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-cache, no-store');

        http_response_code(HttpStatusCode::METHOD_NOT_ALLOWED->value);

        $this->setHttpStatusCode(HttpStatusCode::METHOD_NOT_ALLOWED->value);
        $this->setSuccess(false);
        if (is_array($messages) && !empty($messages)) {
            $this->_messages = [...$this->_messages, ...$messages];
        } else {
            $this->addMessage('Method not allowed');
        }
        $this->send();
    }

    public function notFound($messages = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-cache, no-store');

        http_response_code(HttpStatusCode::NOT_FOUND->value);

        $this->setHttpStatusCode(HttpStatusCode::NOT_FOUND->value);
        $this->setSuccess(false);
        if (is_array($messages) && !empty($messages)) {
            $this->_messages = [...$this->_messages, ...$messages];
        } else {
            $this->addMessage('Not found');
        }
        $this->send();
    }

    public function badRequest($messages = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-cache, no-store');

        http_response_code(HttpStatusCode::BAD_REQUEST->value);

        $this->setHttpStatusCode(HttpStatusCode::BAD_REQUEST->value);
        $this->setSuccess(false);

        if (is_array($messages) && !empty($messages)) {
            $this->_messages = [...$this->_messages, ...$messages];
        } else {
            $this->addMessage('Bad request');
        }

        $this->send();
    }

    public function internalServerError($messages = [])
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-cache, no-store');

        http_response_code(HttpStatusCode::INTERNAL_SERVER_ERROR->value);

        $this->setHttpStatusCode(HttpStatusCode::INTERNAL_SERVER_ERROR->value);
        $this->setSuccess(false);
        if (is_array($messages) && !empty($messages)) {
            $this->_messages = [...$this->_messages, ...$messages];
        } else {
            $this->addMessage('Internal server error');
        }
        $this->send();
    }

    public function ok($data = null)
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($this->_toCache == true) {
            header('Cache-control: max-age=60');
        } else {
            header('Cache-control: no-cache, no-store');
        }

        http_response_code(HttpStatusCode::OK->value);

        $this->setHttpStatusCode(HttpStatusCode::OK->value);
        $this->setSuccess(true);
        $this->setData($data);
        $this->send();
    }

    public function created($data = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-control: no-cache, no-store');

        http_response_code(HttpStatusCode::CREATED->value);

        $this->setHttpStatusCode(HttpStatusCode::CREATED->value);
        $this->setSuccess(true);
        $this->setData($data);
        $this->send();
    }

    private function send()
    {
        header('Content-Type: application/json; charset=utf-8');

        if ($this->_toCache == true) {
            header('Cache-control: max-age=60');
        } else {
            header('Cache-control: no-cache, no-store');
        }

        if (($this->_success !== true && $this->_success !== false) || !is_numeric($this->_httpStatusCode)) {
            http_response_code(HttpStatusCode::INTERNAL_SERVER_ERROR->value);

            $this->_responseData['statusCode'] = HttpStatusCode::INTERNAL_SERVER_ERROR->value;
            $this->_responseData['success'] = false;
            $this->addMessage('Response creation error');
            $this->_responseData['messages'] = $this->_messages;
        } else {
            http_response_code($this->_httpStatusCode);

            $this->_responseData['statusCode'] = $this->_httpStatusCode;
            $this->_responseData['success'] = $this->_success;
            $this->_responseData['messages'] = $this->_messages;
            $this->_responseData['data'] = $this->_data;
        }

        echo json_encode($this->_responseData);
    }
}
