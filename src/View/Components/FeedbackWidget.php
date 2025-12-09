<?php

namespace Haevol\OpenProjectFeedback\View\Components;

use Illuminate\View\Component;

class FeedbackWidget extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render()
    {
        return view('openproject-feedback::components.feedback-widget');
    }
}

