<?php
namespace ToDoApp;
use LogicException;


class Application
{
    private array $items = [];

    public function __construct(
        protected string $path,
        protected string $prefix
    )
    {
        $this->items = $this->getItems();
    }

    public function run()
    {
        while ($cmd = readline("todo>")) {

            try {
                match ($cmd) {
                    "list" => $this->listItems(),
                    'help' => $this->help(),
                    "add" => $this->addItem(readline("new item> "), readline("due-date> ")),
                    "delete" => $this->deleteItem(readline("item to delete> ")),
                    "update" => $this->updateItem(readline("item ID> "), readline("item new content> "), readline("item new due date> ")),
                    "set-status" => $this->setStatus(readline("item ID> "), readline("item new status> ")),
                    "find" => $this->findItem(readline("content to search> ")),
                    default => print "Command $cmd not supported" . PHP_EOL
                };
            } catch (\Throwable $e) {
                print PHP_EOL . "Invalid data" . PHP_EOL;
                print $e->getMessage() . PHP_EOL . PHP_EOL;
                $this->saveItems();
            }
        }
        $this->saveItems();
    }

    public function help(): void
    {
        print "Available commands: list add delete update set-status";
    }

    public function setStatus(string $idToEdit, string $newStatus): void
    {
        $index = array_search($idToEdit, array_column($this->items, "id"));
        $this->items[$index]->setStatus($newStatus);
        print "Te status of $idToEdit was updated." . PHP_EOL . PHP_EOL;
    }

    public function updateItem(string $idToUpdate, string $newContent, string $newDueDate): void
    {
        if (!$idToUpdate) {
            throw new LogicExeption("You didn't provide id to update");
        }
        $index = array_search($idToUpdate, array_column($this->items, "id"));
        $this->items[$index]->setContent($newContent);
        $this->items[$index]->setStatus("updated");
        $this->items[$index]->setDueDate($newDueDate);
        print "Item $idToUpdate was updated." . PHP_EOL . PHP_EOL;
    }

    public function findItem(string $contentToFind): void
    {
        if (empty($contentToFind)) {
            throw new LogicExeptions("You didn't provide any content");
        }

        $index = array_search($contentToFind, array_column($this->items, "content"));
        print " [] {$this->items[$index]->getId()} from {$this->items[$index]->getCreatedAt()}" . PHP_EOL;
        print "   Content  : {$this->items[$index]->getContent()}" . PHP_EOL;
        print "   Due-date : {$this->items[$index]->getDueDate()}" . PHP_EOL;
        print "   Status   : {$this->items[$index]->getStatus()}" . PHP_EOL . PHP_EOL;
    }

    public function deleteItem(string $idToDelete): void
    {
        if (empty($idToDelete)) {
            throw new LogicException("You didn't provide item ID to delete.");
        }

        $filteredItems = array_filter($this->items, fn(Item $item) => $item->getId() !== $idToDelete);
        if (count($this->items) > count($filteredItems)) {
            $this->saveItems($filteredItems);
            print "Item $idToDelete was deleted" . PHP_EOL . PHP_EOL;
        } else {
            print "Nothing to delete" . PHP_EOL . PHP_EOL;
        }
        $this->items = $filteredItems;
    }

    public function dueDate(): void
    {
        foreach ($this->items as $item) {
            if (strtotime($item->getDueDate()) < time()) {
                $item->setStatus("outdated");
            }
        }
    }

    public function addItem(string $content, string $dueDate): Item
    {
        if (empty($content)) {
            throw new LogicExeption("You didn't provide item content.");
        }
            $lastId = 0;
        if (count($this->items) > 0) {
            $lastItems = $this->items[count($this->items) - 1];
            $lastId = (int)str_replace($this->prefix, "", $lastItems->getId());
        }
        $item = new Item(
            $this->prefix . ($lastId + 1),
            $content,
            'new',
            null,
            $dueDate
        );

        $this->items[] = $item;
        $this->saveItems();
        print "Item {$item->getId()} was added." . PHP_EOL . PHP_EOL;
        return $item;
    }

    public function listItems(): void
    {
        print "## Todo items ##" . PHP_EOL;
        $this->dueDate();
        if (empty($this->items)) {
            print "Nothing here yet..." . PHP_EOL . PHP_EOL;
            return;
        }
        foreach ($this->items as $item) {
            $this->printItem($item);
        }
    }
    public function printItem(Item $item):void{
        $state = $item->getStatus()=== 'done' ? 'X' : ' '; # ctr + w
        print " - [$state] {$item->getId()} from {$item->getCreatedAt()}" . PHP_EOL;
        print "   Content  : {$item->getContent()}" . PHP_EOL;
        print "   Due-date : {$item->getDueDate()}" . PHP_EOL;
        print "   Status   : {$item->getStatus()}" . PHP_EOL . PHP_EOL;
    }

    public function getItems(): array
    {
        if (!file_exists($this->path)) {
            $this->saveItems();
        }
        $arrayOfItems = json_decode(file_get_contents($this->path), true);

        return array_map(fn ($item) => Item::fromArray($item), $arrayOfItems);

    }

    public function saveItems(): void
    {
        $itemsArray = array_map(fn (Item $item) => $item->toArray(), $this->items);
        file_put_contents($this->path, json_encode(array_values($itemsArray), JSON_PRETTY_PRINT));
    }
}

