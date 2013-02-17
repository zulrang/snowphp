<?php
class HTMLControls
{   
    protected $control;
    protected $name;
    
    public function __construct($name, $label)
    {
        // set control name
        $this->name = $name;
        $this->control = '';

        if (isset($label))
        {
            $this->control .= '<label for="'.$name.'">'.$label.'</label><br/>';
        }
    }
}