<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Input extends Component
{
    public $title;

    public $name;

    public $id;

    public $type;

    public $attr;

    public $isRequired;

    public $placeholder;

    public $class;

    public $value;

    public $options;

    public $accept;

    public $showPreview;

    public $maxSize;

    public function __construct(
        string $name,
        string $type = 'text',
        string $attr = '',
        bool $isRequired = false,
        ?string $placeholder = null,
        ?string $class = null,
        ?string $title = '',
        ?string $value = '',
        ?array $options = [],
        ?string $accept = null,
        bool $showPreview = false,
        int $maxSize = 2048,
        ?string $id = null
    ) {
        $this->title = $title ?? $placeholder;
        $this->name = $name;
        $this->id = $id ?? $name;
        $this->type = $type;
        $this->attr = $attr;
        $this->isRequired = $isRequired;
        $this->placeholder = $placeholder;
        $this->class = $class;
        $this->value = $value;
        $this->options = $options;
        $this->accept = $accept;
        $this->showPreview = $showPreview;
        $this->maxSize = $maxSize;
    }

    public function render()
    {
        return view('components.input');
    }
}
