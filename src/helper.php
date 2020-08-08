<?php

if (! function_exists('datatables')) {
    function datatables($query)
    {
        //initializing
        $this->query = $query;
        $this->auto_search = false;
        $this->auto_order = false;
        return $this;
    }
}

if (! function_exists('autoSearch')) {
    function autoSearch()
    {
        $this->auto_search = true;
        return $this;
    }
}
}

if (! function_exists('autoOrder')) {
    function autoOrder()
    {
        $this->auto_order = true;
        return $this;
    }
}

if (! function_exists('autoFilter')) {
    function autoFilter($query)
    {
        foreach ($this->getColumns($query) as $key => $column) {
            if(request()->filled($column)) {
                $query = $query->where($column, request()->$column);
            }
        }

        return $query;
    }
}

if (! function_exists('filter')) {
    function filter($column)
    {
        !is_array($column) ? $filters[] = $column : $filters = $column;
        foreach ($filters as $key => $filter) {
            if (!empty($filter) && request()->filled($filter)) {
                $this->query->where($filter, request()->$filter);
            }
        }
        return $this;
    }
}

if (! function_exists('order')) {
    function order($column, $sort = 'desc')
    {
        !is_array($column) ? $columns[] = $column : $columns = $column;
        foreach ($columns as $key => $column) {
            if (\Schema::hasColumn($this->query->getModel()->getTable(), $column)) {
                $this->query->orderBy($column, $sort);
            }
        }
        return $this;
    }
}

if (! function_exists('run')) {
    function run()
    {
        if ($this->auto_search) {
            $this->autoFilter($this->query);
        }

        //this script for global searching in datatables
        if (request()->filled('search') && !empty(request()->search['value'])) {
            $this->query->where(function($query) {
                foreach ($this->getColumns($this->query) as $key => $value) {
                    if ($key == 0) {
                        $this->query = $this->query->where($value, 'like', '%'.request()->search['value'].'%');
                    }
                    else {
                        $this->query = $this->query->orWhere($value, 'like', '%'.request()->search['value'].'%');
                    }
                }
            });
        }

        //get total data under mid before filtered
        $recordsTotal = $this->query->getModel()->count();

        //get total data under mid after filtered
        $recordsFiltered = $this->query->count();

        //offset and limit part
        $this->query = $this->query->offset(request()->input('start', 0));
        $this->query = $this->query->limit(request()->input('length', 10));

        //ordering part
        if ($this->auto_order && \Schema::hasColumn($this->query->getModel()->getTable(), 'created_at'))
        {
            $this->query = $this->query->orderBy('created_at', 'desc');
        }

        //getting rows
        $data = $this->query->get();

        $response = [
            'draw' => request()->draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ];

        return response()->json($response, 200);
    }
}
