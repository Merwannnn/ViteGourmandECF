<?php

namespace App\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
// permet de cibler la collection indiqué dans la base de données MongoDB associé
#[ODM\Document(collection: "stats_menu")]
class CommandeDocument
{
    #[ODM\Id]
    private string $id;

    #[ODM\Field(type: "int")]
    private int $idCommandeMysql;

    #[ODM\Field(type: "int")]
    private int $menuId;

    #[ODM\Field(type: "string")]
    private string $menuName;

    public function getId(): string
    {
        return $this->id;
    }

    public function getIdCommandeMysql(): int
    {
        return $this->idCommandeMysql;
    }

    public function setIdCommandeMysql(int $idCommandeMysql): void
    {
        $this->idCommandeMysql = $idCommandeMysql;
    }

    public function getMenuId(): int
    {
        return $this->menuId;
    }

    public function setMenuId(int $menuId): void
    {
        $this->menuId = $menuId;
    }

    public function getMenuName(): string
    {
        return $this->menuName;
    }

    public function setMenuName(string $menuName): void
    {
        $this->menuName = $menuName;
    }
}