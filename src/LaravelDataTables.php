<?php

namespace AbdullahHafizh\LaravelDataTables;

class LaravelDataTables
{
    function datatables($query)
    {
        $this->query = $query;
        $this->auto_search = false;
        $this->auto_order = false;
        $this->model = $this->query->getModel();
        $this->table = $this->model->getTable();
        $this->draw = request('draw', 0);
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
        foreach ($this->getColumns() as $key => $column) {
            if(request()->filled($column)) {
                $this->query = $this->query->whereRaw('CAST(' . $column . ' as CHAR)' . ' = ' . "'" . request($column) . "'");
            }
        }

        return $this;
    }

    function filter($column)
    {
        !is_array($column) ? $filters[] = $column : $filters = $column;
        foreach ($filters as $key => $filter) {
            if (!empty($filter) && request()->filled($filter)) {
                $this->query->whereRaw('CAST(' . $filter . ' as CHAR)' . ' = ' . "'" . request($filter) . "'");
            }
        }
        return $this;
    }

    function order($column, $sort = 'desc')
    {
        !is_array($column) ? $columns[] = $column : $columns = $column;
        foreach ($columns as $key => $column) {
            if ($this->hasColumn($column)) {
                $this->query->orderBy($column, $sort);
            }
        }
        return $this;
    }

    function run()
    {
        try {
            if ($this->auto_search) {
                $this->autoFilter($this->query);
            }

            // check searchable for any column
            if (request()->filled('columns')) {
                $columns = request('columns');
                foreach ($columns as $key => $column) {
                    $value = $column['search']['value'];
                    if (!empty($value)) {
                        if($this->hasColumn($column['data'])) {
                            $this->query = $this->query->whereRaw('CAST(' . $column['data'] . ' as CHAR)' . " LIKE '" . $value . "%'");
                        }
                    }
                }
            }
            // this script for global searching in datatables
            if (request()->filled('search') && !empty(request('search')['value'])) {
                $search = request('search')['value'];
                $this->query->where(function($query) use($search) {
                    foreach ($this->getColumns() as $key => $value) {
                        if ($key == 0) {
                            $this->query = $this->query->whereRaw('CAST(' . $value . ' as CHAR)' . ' LIKE ' . "'%" . $search . "%'");
                        }
                        else {
                            $this->query = $this->query->orWhereRaw('CAST(' . $value . ' as CHAR)' . ' LIKE ' . "'%" . $search . "%'");
                        }
                    }
                });
            }

            // get total data under mid before filtered
            $this->recordsTotal = $this->model->count();

            // get total data under mid after filtered
            $this->recordsFiltered = $this->query->count();

            // offset and limit part
            if (request('length') != -1) {
                $this->query = $this->query->offset(request()->input('start', 0));
                $this->query = $this->query->limit(request()->input('length', 10));
            }

            // ordering part
            if (request()->filled('order')) {
                $orders = request('order');
                foreach ($orders as $key => $order) {
                    $column = request('columns')[$order['column']]['data'];
                    $sort = $order['dir'];
                    if($this->hasColumn($column)) {
                        $this->query = $this->query->orderBy($column, $sort);
                    }
                }
            }

            if ($this->auto_order && $this->hasColumn('created_at')) {
                $this->query = $this->query->orderBy('created_at', 'desc');
            }

            // getting rows
            $this->data = $this->query->get();

        } catch(\Illuminate\Database\QueryException $exception) {
            $this->error = $exception->errorInfo[2];
        }
        return $this->response();
    }

    function getColumns()
    {
        return \Schema::getColumnListing($this->table);
    }

    function hasColumn($column)
    {
        return \Schema::hasColumn($this->table, $column);
    }

    function response($error = false)
    {
        if (!empty($this->error)) {
            $response = [
                'draw' => $this->draw,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $this->error
            ];
            return response()->json($response, 200);
        }

        $response = [
            'draw' => $this->draw,
            'recordsTotal' => $this->recordsTotal,
            'recordsFiltered' => $this->recordsFiltered,
            'data' => $this->data
        ];
        return response()->json($response, 200);
    }
}
