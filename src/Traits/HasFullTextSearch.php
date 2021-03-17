<?php

namespace Exosfera\Traits;

trait HasFullTextSearch
{
    protected $model_property_aux = 'searchable';
    protected $reserved_symbols = ['-', '+', '<', '>', '@', '(', ')', '~'];

    function scopeOrderByScore($query, $term){
        return $query
                ->selectRaw("(MATCH ({$this->getSearchableColumns()}) AGAINST (?)) + IF({$this->getColumnsBeginingWith($term)}, 100, 0) as score", [$this->fullTextWildcards($term)])
                ->orderByDesc('score');
    }

    function scopeFullTextSearch($query, $term){
        return $query->whereRaw($this->getFullTextSqlCode(), $this->fullTextWildcards($term));
    }

    protected function fullTextWildcards($term){
        $words = explode(' ', str_replace($this->reserved_symbols, '', $term));

        return collect($words)
                ->filter(function($item){ return strlen($item) > 3; })
                    ->map(function($item){ return '+' . $item . '*'; })
                        ->join(' ');
    }

    protected function getSearchableColumns(){
        if (!property_exists(self::class, $this->model_property_aux))
            throw new \Exception("$this->model_property_aux property is required", 1);
            
        return collect($this->{$this->model_property_aux})->join(',');
    }

    protected function getColumnsBeginingWith($term){
        if (!property_exists(self::class, $this->model_property_aux))
            throw new \Exception("$this->model_property_aux property is required", 1);
            
        return collect($this->{$this->model_property_aux})->map(function($column) use ($term){
            return "{$column} LIKE '{$term}%'";
        })->join(' OR ');
    }

    protected function getFullTextSqlCode(){
        return "MATCH ({$this->getSearchableColumns()}) AGAINST (? IN BOOLEAN MODE)";
    }
}
