<?php
declare(strict_types=1);

namespace Eos\Bundle\ComView\Server\Controller;

use Eos\ComView\Server\ComViewServer;
use Eos\ComView\Server\Model\Value\HttpResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philipp Marien <marien@eosnewmedia.de>
 */
class ComViewController
{
    /**
     * @var ComViewServer
     */
    private $comViewServer;

    /**
     * @var array
     */
    private $schema;

    /**
     * @var string
     */
    private $allowOrigin;

    /**
     * @param ComViewServer $comViewServer
     * @param array $schema
     * @param string $allowOrigin
     */
    public function __construct(ComViewServer $comViewServer, array $schema, string $allowOrigin)
    {
        $this->comViewServer = $comViewServer;
        $this->schema = $schema;
        $this->allowOrigin = $allowOrigin;
    }

    /**
     * @param Request $request
     * @param string $name
     * @return Response
     * @throws \Throwable
     */
    public function view(Request $request, string $name): Response
    {
        return $this->createJsonResponse(
            $request,
            $this->comViewServer->view(
                $name,
                $this->extractHeaders($request),
                $request->query->all()
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function execute(Request $request): Response
    {
        return $this->createJsonResponse(
            $request,
            $this->comViewServer->execute(
                $this->extractHeaders($request),
                \json_decode($request->getContent(), true)
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function schema(Request $request): Response
    {
        return $this->createJsonResponse(
            $request,
            new HttpResponse(200, $this->schema)
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function health(Request $request): Response
    {
        $response = $this->comViewServer->health();

        return $this->createJsonResponse($request, $response);
    }

    /**
     * @param Request $request
     * @param HttpResponse $response
     * @return JsonResponse
     */
    private function createJsonResponse(
        Request $request,
        HttpResponse $response
    ): JsonResponse {
        $headers = array_merge($response->getHeaders(), ['Access-Control-Allow-Origin' => $this->allowOrigin]);

        if ($request->query->has('pretty')) {
            return new JsonResponse(
                \json_encode($response->getBody(), JSON_PRETTY_PRINT),
                $response->getStatus(),
                $headers,
                true
            );
        }

        return new JsonResponse(
            $response->getBody(),
            $response->getStatus(),
            $headers
        );
    }

    /**
     * @param Request $request
     * @return string[][]
     */
    private function extractHeaders(Request $request): array
    {
        $headers = [];
        foreach ($request->headers->all() as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            $headers[strtolower($header)] = $value;
        }

        return $headers;
    }
}
