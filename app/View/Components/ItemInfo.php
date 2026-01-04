<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ItemInfo extends Component
{
    public $name;
    public $image;
    public $code;
    public $barcode;
    public $initials;

    public function __construct($item)
    {
        $this->name = $item->name ?? $item->title ?? ucwords(str_replace('_', ' ', $item->key ?? '')) ?? 'N/A';
        $this->image = $item->image ?? null;
        $this->code = $item->code ?? null;
        $this->barcode = $item->barcode ?? null;

        if ($this->image) {
            if (file_exists(public_path($this->image))) {
                $this->initials = imageShow($this->image);
            }
        }
        if (empty($this->initials)) {
            $words = preg_split('/\s+/', trim($this->name)); // split by spaces
            $initials = '';

            foreach ($words as $word) {
                $initials .= strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $word), 0, 1));
            }

            $this->initials = substr($initials, 0, 2);
            $this->image = null;
        }
    }

    public function render(): View|Closure|string
    {
        return view('components.item-info', [
            'name' => $this->name,
            'image' => $this->image,
            'code' => $this->code,
            'barcode' => $this->barcode,
            'initials' => $this->initials,
        ]);
    }
}
