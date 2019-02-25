<?php
declare(strict_types=1);

namespace Eos\Bundle\ComView\Server\Controller;

use Eos\ComView\Server\ComViewServer;
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
     * @param ComViewServer $comViewServer
     * @param array $schema
     */
    public function __construct(ComViewServer $comViewServer, array $schema)
    {
        $this->comViewServer = $comViewServer;
        $this->schema = $schema;
    }

    /**
     * @param Request $request
     * @param string $name
     * @return Response
     * @throws \Throwable
     */
    public function view(Request $request, string $name): Response
    {
        $response = $this->comViewServer->view($name, $request->query->all());

        return new JsonResponse($response->getBody(), $response->getStatus());
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Throwable
     */
    public function execute(Request $request): Response
    {
        $response = $this->comViewServer->execute(\json_decode($request->getContent(), true));

        return new JsonResponse($response->getBody(), $response->getStatus());
    }

    /**
     * @return Response
     */
    public function schema(): Response
    {
        return new JsonResponse($this->schema);
    }

    /**
     * @return Response
     */
    public function health(): Response
    {
        $response = $this->comViewServer->health();

        return new JsonResponse($response->getBody(), $response->getStatus());
    }
}
