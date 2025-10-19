<?php

namespace App\Livewire\Superadmin\User;

use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    use \Livewire\WithPagination;
    protected $paginateTheme = 'tailwind';
    public $paginate='10';
    public $search='';
    public string $nama = '';
    public string $email = '';
    public string $role = '';

    public array $roleOptions = [
        'super_admin' => 'Super Admin',
        'admin'       => 'Admin',
    ];

    public function render()
    {   
        $data = array(
            'title' => 'User Management',
            'adduser' => 'Add User',
            'user' => User::where('name','like','%'.$this->search.'%')
                ->orWhere('email','like','%'.$this->search.'%')
                ->orWhere('role','like','%'.$this->search.'%')
                ->orderBy('role','ASC')->paginate($this->paginate),
        );
        return view('livewire.superadmin.user.index', $data);
    }

    protected function rules(): array
    {
        return [
            'nama'  => ['required','string','min:2'],
            'email' => ['required','email','unique:users,email'],
            'role'  => ['required','in:super_admin,admin'],
        ];
    }

    protected function messages(): array
    {
        return [
            'nama.required'  => 'Nama wajib diisi.',
            'nama.min'       => 'Nama minimal 2 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah terdaftar.',
            'role.required'  => 'Role wajib dipilih.',
            'role.in'        => 'Role harus Super Admin atau Admin.',
        ];
    }
    
    public function updated($property) { $this->validateOnly($property); }

    public function getCanSaveProperty(): bool
    {
        return trim($this->nama) !== ''
            && trim($this->email) !== ''
            && trim($this->role) !== ''
            && $this->getErrorBag()->isEmpty();
    }

    public function store()
    {
        $validated = $this->validate();

        User::create([
            'name'     => $validated['nama'],
            'email'    => $validated['email'],
            'role'     => $validated['role'], // 'super_admin' atau 'admin'
            'password' => bcrypt('default12345'),
        ]);

        $this->resetForm();
        $this->dispatch('close-dialog');
        $this->dispatch('notify', body: 'User berhasil ditambahkan.');
        $this->dispatch('refresh-table');
    }

    public function resetForm()
    {
        $this->reset(['nama','email','role']);
        $this->resetValidation();
    }


    

}
