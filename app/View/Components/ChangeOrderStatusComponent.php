<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ChangeOrderStatusComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public $deliveryAgents;
    public function __construct($deliveryAgents)
    {
        $this->deliveryAgents = $deliveryAgents;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.change-order-status-component');
    }
}
