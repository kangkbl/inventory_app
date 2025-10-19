<?php

namespace App\Livewire\Superadmin\Barang;

use App\Models\Barang;
use Livewire\Component;


class Index extends Component
{
    use \Livewire\WithPagination;
    protected $paginateTheme = 'tailwind';
    public $paginate='10';
    public $search='';
    public $nama='';

    public function render()
    {   
        $data = array(
            'title' => 'Item Management',
            'addbarang' => 'Tambahkan Barang',
            'barang' => Barang::where('nama_barang','like','%'.$this->search.'%')
                ->orderBy('nama_barang','ASC')->paginate($this->paginate),
        );
        return view('livewire.superadmin.barang.index', $data);
    }

    public function store()
    {
        $this-> validate([
            'nama_barang' => 'required'
        ]);
    }
}
