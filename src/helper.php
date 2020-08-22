<?php

$var = new stdClass;

if (! function_exists('datatables')) {
    function datatables($query)
    {
        $model = new \AbdullahHafizh\LaravelDataTables\LaravelDataTables;
        $model->datatables($query);
        return $model;
    }
}

if (! function_exists('autoSearch')) {
    function autoSearch()
    {
        $model = new \AbdullahHafizh\LaravelDataTables\LaravelDataTables;
        $model->autoSearch();
        return $model;
    }
}

if (! function_exists('autoOrder')) {
    function autoOrder()
    {
        $model = new \AbdullahHafizh\LaravelDataTables\LaravelDataTables;
        $model->autoOrder();
        return $model;
    }
}

if (! function_exists('autoFilter')) {
    function autoFilter()
    {
        $model = new \AbdullahHafizh\LaravelDataTables\LaravelDataTables;
        $model->autoFilter();
        return $model;
    }
}

if (! function_exists('filter')) {
    function filter($column)
    {
        $model = new \AbdullahHafizh\LaravelDataTables\LaravelDataTables;
        $model->filter($column);
        return $model;
    }
}

if (! function_exists('order')) {
    function order($column, $sort = 'desc')
    {
        $model = new \AbdullahHafizh\LaravelDataTables\LaravelDataTables;
        $model->order($column, $sort);
        return $model;
    }
}

if (! function_exists('run')) {
    function run()
    {
        $model = new \AbdullahHafizh\LaravelDataTables\LaravelDataTables;
        $model->run();
        return $model;
    }
}
