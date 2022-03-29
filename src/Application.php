<?php

class Application
{
    public function __construct(protected string $path){


}
    public function run(){
        $items = $this->get_items();
        while($cmd = readline("todo>")){
            match($cmd){
                "list" => $this->list_items(),
                "add" => $this -> add_items($items = readline()),
                "delete" => $this -> delete_item($items, readline("item to delete>")),
                "update" => $this -> update_item($items, readline("Item to delete>")),
                "set-status" => $this -> set_statua($items, readline("Item to set status>"), readline("Status>")),
                default => print "Command $cmd not suported" . PHP_EOL
            };
        }
        $this -> save_to_file($items);
    }

    function help(){
        print "Available commands: list add delete update set-status";
    }
    function set_status(array $arguments):void
    {
        $items = get_items();
        if (empty($items)) {
            print "No items to update" . PHP_EOL . PHP_EOL;
            return;
        }
        $idToSetStatus = array_shift($arguments);
        $index = array_search($idToSetStatus, array_column($items, "id"));
        $items[$index]["created at"] = date('Y-m-d H:i');
        $items[$index]["status"] = array_shift($arguments);
        save_to_file($items);
        print "Te status of $idToSetStatus was updated." . PHP_EOL . PHP_EOL;
    }

    function update_item(array $arguments):void
    {
        $items = get_items();
        if (empty($items)) {
            print "No items to update" . PHP_EOL . PHP_EOL;
            return;
        }
        $idToUpdate = array_shift($arguments);
        $index = array_search($idToUpdate, array_column($items, "id"));
        $items[$index]["content"] = array_shift($arguments);
        $items[$index]["created at"] = date('Y-m-d H:i');
        $items[$index]["status"] = "updated";
        save_to_file($items);
        print "Item $idToUpdate was updated." . PHP_EOL . PHP_EOL;
    }
    function find_item(array $arguments):void
    {
        $items = get_items();
        if (empty($items)) {
            print "No items to update" . PHP_EOL . PHP_EOL;
            return;
        }
        $itemToFind = array_shift($arguments);
        $index = array_search($itemToFind, array_column($items, "content"));
        print_r($items[$index]);
    }
    function delete_item(array $arguments):void
    {
        if (count($arguments) < 1) {
            print "You didn't provide item ID to delete." . PHP_EOL . PHP_EOL;
            return;
        }
        $idToDelete = array_shift($arguments);
        $items = get_items();
        $filteredItems = array_filter($items, fn ($item) => $item['id'] !== $idToDelete);
        if (count($items) > count($filteredItems)) {
            save_to_file($filteredItems);
            print "Item $idToDelete was deleted" . PHP_EOL . PHP_EOL;
        } else {
            print "Nothing to delete" . PHP_EOL . PHP_EOL;
        }
    }
    function add_item(array $data):void
    {
        if (count($data) < 1) {
            print "You didn't provide item content." . PHP_EOL . PHP_EOL;
            return;
        }
        $items = get_items();
        $lastItems = $items[count($items) - 1];
        $lastId = (int)str_replace(PREFIX, "", $lastItems['id']);
        $item = [
            'id' => PREFIX . ($lastId + 1),
            'created_at' => date('Y-m-d H:i'),
            'content' => array_shift($data),
            'status' => 'new',
        ];
        $items[] = $item;
        save_to_file($items);
        print "Item $item[id] was added." . PHP_EOL . PHP_EOL;
    }
    function list_items():void
    {
        print "## Todo items ##" . PHP_EOL;
        $items = get_items();
        if (empty($items)) {
            print "Nothing here yet..." . PHP_EOL . PHP_EOL;
            return;
        }
        foreach ($items as $item) {
            $this -> print($item);
        }
    }
     function print_item(Item $item){
         $state = $item-> getStatus() === 'done' ? 'X' : ' '; # ctr + w

         print " - [$state] {$item-> getId()} from {$item->getCreatedAt()}" . PHP_EOL;
         print "   Content  : {$item->getContent()}" . PHP_EOL;
         print "   Status   : {$item-> getStatus()}" . PHP_EOL . PHP_EOL;
     }
    function get_items():mixed
    {
        if (!file_exists($this ->path)) {
            save_to_file([]);
        }
        $arrayOfItems = json_decode(file_get_contents($this -> $path), true);
        $items = [];
        foreach($arrayOfItems as $itemArray){
            $items[] = new Item(
                $itemArray['id'],
                $itemArray['content'],
                $itemArray['status'],
                $itemArray['createdAt']
            );
        }
        return $arrayOfItems;
    }
    function save_to_file(array $items):void
    {
        file_put_contents($this -> $path, json_encode(array_values($items), JSON_PRETTY_PRINT));
    }
}