<?php

namespace AbdullahHafizh\LaravelDataTables;

class LaravelDataTables
{
	private $query;
	private $auto_search;
	private $auto_order;

    function datatables($query)
    {
    	$this->query = $query;
        $this->auto_search = false;
        $this->auto_order = false;
    	return $this;
    }

    function autoSearch()
    {
    	$this->auto_search = true;
        return $this;
    }

    function autoOrder()
    {
        $this->auto_order = true;
        return $this;
    }

    function autoFilter()
    {
        foreach ($this->getColumns($this->query) as $key => $column) {
            if(request()->filled($column)) {
                $this->query = $this->query->where($column, request()->$column);
            }
        }

        return $this;
    }

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

    function getColumns($model)
	{
		return \Schema::getColumnListing($model->getModel()->getTable());
	}
}
