<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DebrickedApiService
{
    private $httpClient;
    private string $authorizationToken;
    private string $apiUrl;
    private string $debrickedUserName;
    private string $debrickedPassword;

    public function __construct(HttpClientInterface $httpClientInterface, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClientInterface;
        $this->apiUrl = $params->get('api_base_url');
        $this->debrickedUserName = $params->get('user_name_debricked');
        $this->debrickedPassword = $params->get('password_debricked');
        $this->generateApiToken();
    }

    public function generateApiToken(){
        try {
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ];
            $body = [
                '_username' => $this->debrickedUserName,
                '_password' => $this->debrickedPassword,
            ];
            $response = $this->httpClient->request(
                'POST',
                $this->apiUrl."/login_check",
                [
                    'headers' => $headers,
                    'body' => $body,
                ]
            );
            if($response->toArray()['token']){
                $this->authorizationToken = $response->toArray()['token'];
            }
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function uploadFile($filePath, string $fileName, string $repositoryName, string $commitName): array
    {
        try {     

            if (!file_exists($filePath) || !is_readable($filePath)) {
                throw new \Exception("File not found or not readable: " . $filePath);
            }
            $body = [
                'repositoryName' => $repositoryName,
                'commitName' => $commitName,
                'fileData' => DataPart::fromPath($filePath, $fileName),
            ];
            $formData = new FormDataPart($body);
            $body = $formData->bodyToIterable();
            $multipartHeaders = $formData->getPreparedHeaders()->toArray(); // This will include correct 'Content-Type'
            $headers = array_merge($multipartHeaders, [
                'Authorization' => 'Bearer ' . $this->authorizationToken
            ]);
            $response = $this->httpClient->request(
                'POST',
                $this->apiUrl."/1.0/open/uploads/dependencies/files",
                [
                    'headers' => $headers,
                    'body' => $body,
                ]
            );
            // print_r(dump($response->getContent(false)));
            return [
                'status' => $response->getStatusCode(),
                'data' => $response->toArray(),
            ];
        }catch (\Exception $e) {
            return [
                'status' => 503,
                'message' => $e->getMessage(),
            ];
        }
    }
}