<?php

namespace Model;
class Cat
{
    private string $uid;
    private string $name;
    private ?int $age;
    private ?string $description;
    private ?string $whenLastSeen;
    private ?string $whereLastSeen;
    private ?string $furColor;
    private ?int $weight;
    private ?bool $isStray;
    private ?string $image;
    private ?int $price;
    private ?string $ownerUID;

    /**
     * @param string $uid
     * @param string $name
     * @param int $age
     * @param string $description
     * @param string $whenLastSeen
     * @param string $whereLastSeen
     * @param string $furColor
     * @param int $weight
     * @param bool $isStray
     * @param string $image
     * @param int $price
     * @param string $ownerUID
     */
    public function __construct(string $uid, string $name, int $age, string $description, string $whenLastSeen, string $whereLastSeen, string $furColor, int $weight, bool $isStray, string $image, int $price, string $ownerUID)
    {
        $this->uid = $uid;
        $this->name = $name;
        $this->age = $age;
        $this->description = $description;
        $this->whenLastSeen = $whenLastSeen;
        $this->whereLastSeen = $whereLastSeen;
        $this->furColor = $furColor;
        $this->weight = $weight;
        $this->isStray = $isStray;
        $this->image = $image;
        $this->price = $price;
        $this->ownerUID = $ownerUID;
    }

    public function getUid(): string
    {
        return $this->uid;
    }

    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function setAge(int $age): void
    {
        $this->age = $age;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getWhenLastSeen(): string
    {
        return $this->whenLastSeen;
    }

    public function setWhenLastSeen(string $whenLastSeen): void
    {
        $this->whenLastSeen = $whenLastSeen;
    }

    public function getWhereLastSeen(): string
    {
        return $this->whereLastSeen;
    }

    public function setWhereLastSeen(string $whereLastSeen): void
    {
        $this->whereLastSeen = $whereLastSeen;
    }

    public function getFurColor(): string
    {
        return $this->furColor;
    }

    public function setFurColor(string $furColor): void
    {
        $this->furColor = $furColor;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): void
    {
        $this->weight = $weight;
    }

    public function isStray(): bool
    {
        return $this->isStray;
    }

    public function setIsStray(bool $isStray): void
    {
        $this->isStray = $isStray;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function setImage(string $image): void
    {
        $this->image = $image;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): void
    {
        $this->price = $price;
    }

    public function getOwnerUID(): ?string
    {
        return $this->ownerUID;
    }

    public function setOwnerUID(?string $ownerUID): void
    {
        $this->ownerUID = $ownerUID;
    }

}