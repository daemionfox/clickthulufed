<?php

namespace App\Actions;

class APObject
{

    private string $context = "https://www.w3.org/ns/activitystreams";
    private string $id;
    private string $type;
    private string $actor;
    private APObject|string $object;

    /**
     * @return string
     */
    public function getContext(): string
    {
        return $this->context;
    }

    /**
     * @param string $context
     */
    public function setContext(string $context): void
    {
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getActor(): string
    {
        return $this->actor;
    }

    /**
     * @param string $actor
     */
    public function setActor(string $actor): void
    {
        $this->actor = $actor;
    }

    /**
     * @return APObject|string
     */
    public function getObject(): APObject|string
    {
        return $this->object;
    }

    /**
     * @param APObject|string $object
     */
    public function setObject(APObject|string $object): void
    {
        $this->object = $object;
    }


    public function toArray()
    {
        return [
            '@context' => $this->context,
            'id' => $this->id,
            'type' => $this->type,
            'actor' => $this->actor,
            'object' => is_a($this->object, APObject::class) ? $this->object->toArray() : $this->object
        ];
    }


}