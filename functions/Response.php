<?php

class Response
{
    public ?string $status = "";
    public $data;

    function __construct($status = "success", $data = null)
    {
        if (isset($status)) $this->status = $status;
        if (!isset($data)) $data = (object)[];
        $this->data = $data; //can be array or object
    }

    function __toString()
    {
        if (is_object($this->data)) {
            $this->data->status = $this->status ? $this->status : "";
        }
        return json_encode($this->data);
    }
}
