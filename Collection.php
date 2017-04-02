<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace classes;

/**
 * Description of collection2
 *
 * @author papa
 */
   class Collection  {
        
        
        public $numItems=0;

        //The array
        //public $array = array('12','5','60', '98', '8');       
        public $items=[];
       
       function add($key,$obj)  {
            
             
            $this->items[$key] = $obj;
                         
            $this->count();		
            return $this ;
        }
  
        function set($key,$item) {
	    if(isset($key)) {
		$this->items[$key] = $item;
            } else {
		$this->items[] = $item ;
	    }
            $this->count();
            return $this->get($key);
        }         
  
       
        function sort($flags=null) {
            sort($this->items,$flags);
            return $this ;
        }
        
       
        function count() { 
            $this->numItems = count($this->items);
            return $this->numItems ;
        }                
       
        function remove($key) {
            if (array_key_exists($key,$this->items)) {
                unset($this->items[$key]);
                $this->count();
                return $this;
            }
        }      
       
        function next() {
            return next($this->items) ;
        }
       
        function hasNext() {
            $this->next() ;
            $status = $this->valid() ;
            $this->back() ;
            return $status ;
        }           
        
        function back() {
            return prev($this->items);
        }
        
        function rewind() {
            return reset($this->items);
        }        
        function forward() {
            return end($this->items);
        }        
        function current() {
            return current($this->items);
        }
    
      //Getting the current cursor of the key
      function currentKey() {
            return key($this->items) ; 
        }
    
        
        function key() {
            return $this->currentKey();
        }

        
        function valid() {
            if(!is_null($this->key())) {
                return true;
            } else {
                return false ;
            }
        }
                
        function get($key) {
            return $this->items[$key];
        }
     	
        function offsetExists($offset) {
            return $this->exists($offset);
        }
        
        function offsetGet($offset) {
            return $this->get($offset);
        }

        function offsetSet($offset,$value) {
            return $this->set($offset, $value);
        } 
    
                function offsetUnset($offset) {
            return $this->remove($offset);
        }
	
        //Checking if the collection is empty or not
        function isEmpty() {
 	    if($this->count() < 1)
        	return true ;
	    else
            return false;
        }

        
       /* function contains($obj) {
            foreach($this->items as $element) {
                if($element === $obj) {
                    $this->rewind();
                    return true ;
                }
            }
            $this->rewind();
            return false ;
        }*/
    
        
        function indexOf($obj) {
            foreach($this->items as $k=>$element) {
                if($element === $obj) {    			
                    $this->rewind();
                    return $k ;
                }
            }
            $this->rewind();
            return null ;
        }   
        
       
    }

?> 
}
