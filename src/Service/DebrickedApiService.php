<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Service class for interacting with the Debricked API.
 */
class DebrickedApiService
{
    /**
     * @var HttpClientInterface The HTTP client for making API requests.
     */
    private $httpClient;
    private string $authorizationToken;
    private string $apiUrl;
    private string $debrickedUserName;
    private string $debrickedPassword;

    /**
     * Constructor to initialize the DebrickedApiService.
     *
     * @param HttpClientInterface $httpClientInterface The HTTP client interface.
     * @param ParameterBagInterface $params Parameters for the service, including API credentials and URL.
     */
    public function __construct(HttpClientInterface $httpClientInterface, ParameterBagInterface $params)
    {
        $this->httpClient = $httpClientInterface;
        $this->apiUrl = $params->get('api_base_url');
        $this->debrickedUserName = $params->get('user_name_debricked');
        $this->debrickedPassword = $params->get('password_debricked');
        $this->generateApiToken();
    }

    /**
     * Generates and sets the API token required for authentication.
     *
     * @return void
     */
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

    /**
     * Uploads a dependency file to the Debricked API.
     *
     * @param string $filePath The path to the file to be uploaded.
     * @param string $fileName The name of the file to be uploaded.
     * @param string $repositoryName The name of the repository associated with the file.
     * @param string $commitName The commit identifier associated with the file.
     * @return array An array containing the status and data from the API response.
     *
     * @throws \Exception If the file does not exist or is not readable.
     */
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

    /**
     * Marks a file upload as finished in the Debricked API.
     *
     * @param string $ciUploadId The ID of the CI upload to be marked as finished.
     * @return array An array containing the status and message from the API response.
     */
    public function finishFileUpload($ciUploadId){
        try {
            $headers = [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer ' . $this->authorizationToken
            ];
            $body = [
                'ciUploadId' => $ciUploadId,
            ];
            $response = $this->httpClient->request(
                'POST',
                $this->apiUrl."/1.0/open/finishes/dependencies/files/uploads",
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

    public function getUploadStatus(int $ciUploadId): array
    {
        $queryParameters = [
            'ciUploadId' => $ciUploadId
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->authorizationToken,
            'Accept' => '*/*',
        ];

        try {
            $response = $this->httpClient->request('GET', $this->apiUrl."/1.0/open/ci/upload/status", [
                'headers' => $headers,
                'query' => $queryParameters,
            ]);

            return [
                'status' => $response->getStatusCode(),
                'data' => $response->toArray(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 500,
                'message' => $e->getMessage(),
            ];
        }
    }

}