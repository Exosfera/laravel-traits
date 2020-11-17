<?php

namespace Nabbio\Traits;

trait HasFullTextSearch
{
    protected $model_property_aux = 'searchable';
    protected $reserved_symbols = ['-', '+', '<', '>', '@', '(', ')', '~'];

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
            
        return implode(',', $this->{$this->model_property_aux});
    }

    protected function getFullTextSqlCode(){
        return "MATCH ({$this->getSearchableColumns()}) AGAINST (? IN BOOLEAN MODE)";
    }
}