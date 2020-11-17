<?php

namespace App\Models\Traits;

trait HasActiveColumn
{
	function scopeActive($query){
		return $query->where($this->getActiveColumn(), 1);
	}

    function getActiveColumn(){
    	if (!property_exists(self::class, $this->active_column))
        	return $this->getTable() . '.active';
        
        return $this->getTable() . '.' . $this->active_column;
    }
}