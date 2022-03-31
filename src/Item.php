<?php
namespace ToDoApp;
class Item
{
    public function __construct(
        protected string $id,
        protected string $content,
        protected string $status,
        protected null|string|\DateTime $createdAt = null,
        protected ?string $dueDate = null
    ){
        $this->createdAt = $createdAt ?? date('Y-m-d H:i:s');
    }
    public function setStatus($status):void{
        $this->status = $status;
    }
    public function setContent($content):void{
        $this->content = $content;
    }
    public function setDueDate($dueDate):void{
        $this->dueDate = $dueDate;
    }
    public function getDueDate(): string
    {
       return $this->dueDate;
    }
    public function getId(): string
    {
        return $this->id;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }
    public function isDone():bool{
        return $this -> status === "done";
    }
    public function toArray(): array
    {

        return [
            'id' => $this->getId(),
            'content' => $this->getContent(),
            'status' => $this->getStatus(),
            'createdAt' => $this->getCreatedAt(),
            'dueDate' => $this->getDueDate()
        ];
    }
    public static function fromArray(array $items): Item
    {
        return new Item(
            $items['id'],
            $items['content'],
            $items['status'],
            $items['createdAt'],
            $items['dueDate']
        );
    }
}