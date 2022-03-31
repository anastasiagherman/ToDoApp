<?php

#define('DATABASE_PATH', getcwd() . '/todo.json');
DEFINE('PREFIX', "TODO-");

function main(string $command, array $arguments):void
{
    match ( $command) {
        "list" => list_items(),
        "add" => add_item($arguments),
        "delete" => delete_item($arguments),
        "update" => update_item($arguments),
        "find" => find_item($arguments),
        "set-status" => set_status($arguments),
        default => print 'Command not supported' . PHP_EOL
    };
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

    $ContentToUpdate = array_shift($arguments);
    $index = array_search($ContentToUpdate, array_column($items, "content"));
    print " - {$items[$index]["id"]}from {$items[$index]["created at"]}" . PHP_EOL;
    print "   Content  : {$items[$index]["content"]}" . PHP_EOL;
    print "   Status   : {$items[$index]["status"]}" . PHP_EOL . PHP_EOL;
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
function due_date(){
    $items = get_items();
    foreach($items as &$item){
        if(strtotime($item["due_date"]) < time()){
            $item["status"] = "outdated";
        }
    }
    save_to_file($items);
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
        'due_date' => array_shift($data),
        'status' => 'new',
    ];
    $items[] = $item;
    save_to_file($items);
    print "Item $item[id] was added." . PHP_EOL . PHP_EOL;
}
function list_items()
{
    print "## Todo items ##" . PHP_EOL;
    due_date();
    $items = get_items();
    if (empty($items)) {
        print "Nothing here yet..." . PHP_EOL . PHP_EOL;
        return;
    }
    foreach ($items as $item) {
        $state = $item['status'] === 'done' ? 'X' : ' '; # ctr + w
        print " - [$state] $item[id] from $item[created_at]" . PHP_EOL;
        print "   Content  : $item[content]" . PHP_EOL;
        print "   Due-date : $item[due_date]" . PHP_EOL;
        print "   Status   : $item[status]" . PHP_EOL . PHP_EOL;
    }
}
function get_items():mixed
{
    if (!file_exists(DATABASE_PATH)) {
        save_to_file([]);
    }
    return json_decode(file_get_contents(DATABASE_PATH), true);
}
function save_to_file(array $items):void
{
    file_put_contents(DATABASE_PATH, json_encode(array_values($items), JSON_PRETTY_PRINT));
}
