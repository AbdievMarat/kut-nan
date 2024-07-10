<?php

namespace App\View\Components\Admin\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Input extends Component
{
    /**
     * @param string $type
     * @param string $name
     * @param string|null $id
     * @param string|null $value
     * @param string|null $placeholder
     * @param string|null $label
     * @param string|null $class
     * @param string|null $form
     * @param bool|null $required
     */
    public function __construct(
        public string $type,
        public string $name,
        public ?string $id = null,
        public ?string $value = null,
        public ?string $placeholder = null,
        public ?string $label = null,
        public ?string $class = '',
        public ?string $form = '',
        public ?bool $required = false,
    ) {
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.admin.forms.input');
    }
}
