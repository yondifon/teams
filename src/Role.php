<?php

namespace Malico\Teams;

use JsonSerializable;

class Role implements JsonSerializable
{
    /**
     * @param  string  $key  The key of the role.
     * @param  string  $name  The name of the role.
     * @param  array  $permissions  The permissions that are assigned to the role.
     * @param  string  $description  The description of the role.
     */
    public function __construct(
        public string $key,
        public string $name,
        public array $permissions,
        public string $description = ''
    ) {}

    /**
     * Describe the role.
     *
     * @return $this
     */
    public function description(string $description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the JSON serializable representation of the object.
     *
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize()
    {
        return [
            'key' => $this->key,
            'name' => __($this->name),
            'description' => __($this->description),
            'permissions' => $this->permissions,
        ];
    }
}
