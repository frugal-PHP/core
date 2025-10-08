<?php

namespace Frugal\Core\Controllers;

use Frugal\Core\Helpers\PayloadHelper;
use Frugal\Core\Helpers\RoutingHelper;
use Frugal\Core\Interfaces\PayloadInterface;
use Frugal\Core\Mappers\CRUDEnum;
use Frugal\Core\Services\ResponseService;
use FrugalPhpPlugin\Orm\Exceptions\EntityNotFoundException;
use FrugalPhpPlugin\Orm\Helpers\RepositoryHelper;
use FrugalPhpPlugin\Orm\Interfaces\EntityInterface;
use FrugalPhpPlugin\Orm\Interfaces\RepositoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Message\Response;
use React\Promise\PromiseInterface;
use RuntimeException;

use function React\Promise\resolve;

class CrudController
{
    protected ServerRequestInterface $request;
    protected PayloadInterface $payload;

    public function __invoke(ServerRequestInterface $request, ?string $id = null) : PromiseInterface
    {
        $this->request = $request;

        if(RoutingHelper::getRouteDetails($request, 'payloadClassName') !== null) {
            $this->payload = PayloadHelper::getPayload($request);
        }

        switch(RoutingHelper::getRouteDetails($this->request, 'action')) {
            case CRUDEnum::CREATE : return $this->create();
            case CRUDEnum::UPDATE : return $this->update($id);
            case CRUDEnum::DELETE : return $this->delete($id);
            case CRUDEnum::RETRIEVE: return $this->retrieve($id);
            default: throw new RuntimeException("Crud action is invalid for route ".$request->getUri());
        }
    }
    
    protected function create() : PromiseInterface
    {
        $repository = RepositoryHelper::getRepository(RoutingHelper::getRouteDetails($this->request, 'entityClassName'));
        $entity = $this->getCreatedEntity($this->request);

        return $this->beforeCreate($entity)
                ->then(function(EntityInterface $entity) use ($repository) {
                    return $repository->create($entity)
                        ->then(function($result) use($entity, $repository) {
                            if($entity->id === null) {
                                $entity->id = $repository->getDatabaseManager()->getLastInsertId($result);
                            }
                            
                            return $entity;
                        });
                })
                ->then(fn(EntityInterface $entity) => $this->afterCreate($entity))
                ->then(fn($data) => ResponseService::sendJsonResponse(statusCode: Response::STATUS_CREATED, message: $data));
    }

    /**
     * Delete is idempotent. So if no data is found it will always return the same 204 response.
     * @param ServerRequestInterface $request 
     * @param string $id 
     * @return PromiseInterface 
     * @throws RuntimeException 
     */
    protected function delete(string $id) : PromiseInterface
    {
        $repository = RepositoryHelper::getRepository(RoutingHelper::getRouteDetails($this->request, 'entityClassName'));
        $entityClassName = RoutingHelper::getRouteDetails($this->request, 'entityClassName');

        return $repository->findBy(['id' => $id])
            ->then(fn($rows) => empty($rows) ? throw new EntityNotFoundException() : $entityClassName::createFromArray($rows[0]))
            ->then(fn(EntityInterface $entity) => $this->beforeDelete($entity)->then(fn() => $entity))
            ->then(fn(EntityInterface $entity) => $repository->delete($id)->then(fn() => $entity)) 
            ->then(fn(EntityInterface $entity) => $this->afterDelete($entity))            
            ->then(fn($data) => ResponseService::sendJsonResponse(statusCode: Response::STATUS_NO_CONTENT, message: $data));
    }

    protected function retrieve(?string $id = null) : PromiseInterface
    {
        $repository = RepositoryHelper::getRepository(RoutingHelper::getRouteDetails($this->request, 'entityClassName'));
        $entityClassName = RoutingHelper::getRouteDetails($this->request, 'entityClassName');

        return $this->beforeRetrieve($id)
            ->then(fn() => $id === null ? $this->getRetrieveAllQuery($repository) : $this->getSingleRetrieveQuery($repository, $id))
            ->then(fn($rows) => array_map(fn($row) => $entityClassName::createFromArray($row), $rows))
            ->then(function($entities) {
                return $this->afterRetrieve($entities)
                    ->then(fn() => $entities);
            })
            ->then(fn($data) => ResponseService::sendJsonResponse(Response::STATUS_OK, $data));
    }

    protected function update(string $id) : PromiseInterface
    {
        $entityClassName = RoutingHelper::getRouteDetails($this->request, 'entityClassName');
        $repository = RepositoryHelper::getRepository($entityClassName);
        
        return $repository->findBy(['id' => $id])
            ->then(function(array $rows) use ($repository, $entityClassName) {
                if(empty($rows)) {
                    throw new EntityNotFoundException();
                }

                return
                    resolve($this->getUpdatedEntity($entityClassName::createFromArray($rows[0])))
                    ->then(fn(EntityInterface $entity) => $this->beforeUpdate($entity))
                    ->then(function(EntityInterface $entity) use ($repository) {
                        return $repository->update($entity)
                            ->then(fn() => $entity);
                    })
                    ->then(fn(EntityInterface $entity) => $this->afterUpdate($entity))
                    ->then(fn($data) => ResponseService::sendJsonResponse(Response::STATUS_OK, $data));
            });
    }

    protected function getCreatedEntity() : EntityInterface
    {
        throw new RuntimeException('getCreatedEntity() not implemented in generic controller');
    }

    protected function getUpdatedEntity(EntityInterface $entity) : EntityInterface
    {
        throw new RuntimeException('getUpdatedEntity() not implemented in generic controller');
    }

    /**
     * @return PromiseInterface<EntityInterface> 
     */
    protected function beforeCreate(EntityInterface $entity): PromiseInterface
    {
        return resolve($entity);
    }

    protected function afterCreate(EntityInterface $entity): PromiseInterface
    {
        return resolve(null);
    }

    /**
     * @return PromiseInterface<EntityInterface> 
     */
    protected function beforeUpdate(EntityInterface $entity): PromiseInterface
    {
        return resolve($entity);
    }

    /**
     * @param EntityInterface $entity 
     * @return PromiseInterface<void>
     */
    protected function afterUpdate(EntityInterface $entity): PromiseInterface
    {
        return resolve(null);
    }

    protected function beforeDelete(EntityInterface $entity): PromiseInterface
    {
        return resolve(null);
    }

    protected function afterDelete(EntityInterface $entity): PromiseInterface
    {
        return resolve(null);
    }

    protected function getSingleRetrieveQuery(RepositoryInterface $repository, string $id) : PromiseInterface
    {
        return $repository->findBy(['id' => $id]);
    }

    protected function getRetrieveAllQuery(RepositoryInterface $repository) : PromiseInterface
    {
        return $repository->findAll();
    }

    protected function beforeRetrieve(?string $id): PromiseInterface
    {
        return resolve(null);
    }

    /**
     * @param EntityInterface[] $entities 
     * @return PromiseInterface 
     */
    protected function afterRetrieve(array $entities): PromiseInterface
    {
        return resolve(null);
    }
}