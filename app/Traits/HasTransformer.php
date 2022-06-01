<?php


namespace App\Traits;


use Flugg\Responder\Http\MakesResponses;
use Flugg\Responder\Http\Responses\SuccessResponseBuilder;
use Flugg\Responder\Serializers\SuccessSerializer;
use Flugg\Responder\Transformers\Transformer;
use Illuminate\Http\JsonResponse;

trait HasTransformer
{
    use MakesResponses;

    /**
     * @var mixed
     */
    protected $serializer = SuccessSerializer::class;

    /**
     * Build a HTTP_OK response.
     *
     * @param  mixed  $data
     * @param  callable|string|Transformer|null  $transformer
     * @param  string|null  $resourceKey
     * @return SuccessResponseBuilder|JsonResponse
     */
    public function httpOK($data = null, $transformer = null, string $resourceKey = null)
    {
        return $this->success($data, $transformer, $resourceKey)
            ->serializer($this->getSerializer())
            ->respond(JsonResponse::HTTP_OK);
    }

    protected function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param  mixed  $serializer
     * @return $this
     */
    protected function setSerializer($serializer)
    {
        $this->serializer = $serializer;
        return $this;
    }

    /**
     * Build a HTTP_CREATED response.
     *
     * @param  mixed  $data
     * @param  callable|string|Transformer|null  $transformer
     * @param  string|null  $resourceKey
     * @return SuccessResponseBuilder|JsonResponse
     */
    public function httpCreated($data = null, $transformer = null, string $resourceKey = null)
    {
        return $this->success($data, $transformer, $resourceKey)
            ->serializer($this->getSerializer())
            ->respond(JsonResponse::HTTP_CREATED);
    }

    /**
     * Build a HTTP_NO_CONTENT response.
     *
     * @return SuccessResponseBuilder|JsonResponse
     */
    public function httpNoContent()
    {
        return $this->success()
            ->serializer($this->getSerializer())
            ->respond(JsonResponse::HTTP_NO_CONTENT);
    }

    /**
     * Build a HTTP_BAD_REQUEST response.
     *
     * @param  array  $errors
     * @return JsonResponse
     */
    public function httpBadRequest(array $errors = [])
    {
        return $this->error()
            ->data($errors)
            ->respond(JsonResponse::HTTP_BAD_REQUEST);
    }
}
