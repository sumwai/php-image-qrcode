<?php


class Json
{
    private $data;

    public function __construct($data = '')
    {
        $this->data = json_decode($data, true);
    }

    public function get($name): string|null
    {
        $name = explode(".", str_replace("[", ".", str_replace("]", "", $name)));
        $result = $this->data;
        foreach ($name as $k) {
            $result = $result[$k] ?? [];
        }
        return !!$result ? $result : null;
    }
}
